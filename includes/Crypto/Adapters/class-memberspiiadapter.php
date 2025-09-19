<?php
/**
 * Members table encryption adapter.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Crypto\Adapters;

use FoodBankManager\Core\Install;
use FoodBankManager\Crypto\Crypto;
use FoodBankManager\Crypto\EncryptionAdapter;
use FoodBankManager\Crypto\EncryptionSettings;
use RuntimeException;
use wpdb;
use function __;
use function array_fill;
use function array_key_exists;
use function array_keys;
use function count;
use function implode;
use function is_array;
use function is_numeric;
use function is_string;
use function max;
use const ARRAY_A;

/**
 * Handles envelope encryption for member PII fields.
 */
final class MembersPiiAdapter extends EncryptionAdapter {
	private const MODE_MIGRATE  = 'migrate';
	private const MODE_ROTATE   = 'rotate';
	private const ENVELOPE_LIKE = '{\"v\":\"1\",\"alg\":\"AES-256-GCM\"%';
	private const COLUMNS       = array( 'first_name', 'last_initial' );

	/**
	 * WordPress database abstraction.
	 *
	 * @var wpdb
	 */
	private wpdb $wpdb;

	/**
	 * Fully qualified members table name.
	 *
	 * @var string
	 */
	private string $table;

	/**
	 * Constructor.
	 *
	 * @param wpdb $wpdb WordPress database abstraction.
	 */
	public function __construct( wpdb $wpdb ) {
		$this->wpdb  = $wpdb;
		$this->table = Install::members_table_name( $wpdb );
	}

	/**
	 * Retrieve the adapter identifier.
	 *
	 * @return string
	 */
	public function id(): string {
		return 'members_pii';
	}

	/**
	 * Retrieve the adapter label.
	 *
	 * @return string
	 */
	public function label(): string {
		return __( 'Members personal data', 'foodbank-manager' );
	}

	/**
	 * Retrieve adapter status metrics.
	 *
	 * @return array<string,mixed>
	 */
	public function status(): array {
		$pattern = self::ENVELOPE_LIKE;
		$sql     = $this->wpdb->prepare(
// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is trusted internal metadata.
			"SELECT COUNT(*) AS total, SUM(CASE WHEN first_name LIKE %s AND last_initial LIKE %s THEN 1 ELSE 0 END) AS encrypted FROM `{$this->table}`",
			$pattern,
			$pattern
		);

		$total     = 0;
		$encrypted = 0;

		if ( is_string( $sql ) ) {
			/**
			 * Result row data.
			 *
			 * @var array<string,int|string>|null $row
			 */
			$row = $this->wpdb->get_row( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Prepared above.

			if ( is_array( $row ) ) {
				$total     = isset( $row['total'] ) ? (int) $row['total'] : 0;
				$encrypted = isset( $row['encrypted'] ) ? (int) $row['encrypted'] : 0;
			}
		}

		$progress = $this->load_progress();
		$cursor   = ( isset( $progress['cursor'] ) && is_numeric( $progress['cursor'] ) ) ? (int) $progress['cursor'] : null;
		$mode     = isset( $progress['mode'] ) ? (string) $progress['mode'] : '';

		return array(
			'id'                 => $this->id(),
			'label'              => $this->label(),
			'total'              => $total,
			'encrypted'          => $encrypted,
			'remaining'          => max( 0, $total - $encrypted ),
			'progress'           => null !== $cursor ? array(
				'cursor' => $cursor,
				'mode'   => $mode,
			) : null,
			'encrypt_new_writes' => EncryptionSettings::encrypt_new_writes_enabled(),
		);
	}

	/**
	 * Migrate plaintext member records to envelopes.
	 *
	 * @param int  $limit   Maximum rows to evaluate.
	 * @param bool $dry_run Whether to simulate without persisting changes.
	 * @return array<string,mixed>
	 */
	public function migrate( int $limit, bool $dry_run ): array {
		$limit  = max( 1, $limit );
		$cursor = $this->progress_cursor( self::MODE_MIGRATE );
		$rows   = $this->fetch_plaintext_rows( $cursor, $limit );

		$processed = 0;
		$changed   = 0;
		$failures  = array();
		$last_id   = $cursor;

		foreach ( $rows as $row ) {
			if ( ! isset( $row['id'] ) || ! is_numeric( $row['id'] ) ) {
				continue;
			}

			++$processed;
			$member_id = (int) $row['id'];
			$updates   = array();

			foreach ( self::COLUMNS as $column ) {
				if ( ! isset( $row[ $column ] ) || ! is_string( $row[ $column ] ) ) {
					continue;
				}

				$original = $row[ $column ];

				if ( $this->is_envelope( $original ) ) {
					continue;
				}

				try {
					$updates[ $column ] = Crypto::encrypt( $original, $this->table, $column, (string) $member_id );
				} catch ( RuntimeException $exception ) {
					$failures[] = array(
						'id'     => $member_id,
						'column' => $column,
						'error'  => $exception->getMessage(),
					);
				}
			}

			if ( empty( $updates ) ) {
				$last_id = $member_id;
				continue;
			}

			if ( $dry_run ) {
				++$changed;
				$last_id = $member_id;
				continue;
			}

			if ( $this->update_member( $member_id, $updates ) ) {
				++$changed;
			} else {
				$failures[] = array(
					'id'     => $member_id,
					'column' => implode( ',', array_keys( $updates ) ),
					'error'  => 'update-failed',
				);
			}

			$last_id = $member_id;
		}

		$complete = $this->determine_completion( self::MODE_MIGRATE, $limit, $rows, $last_id );

		if ( ! $dry_run ) {
			if ( $complete ) {
				$this->reset_progress();
			} elseif ( $processed > 0 ) {
				$this->save_progress(
					array(
						'mode'   => self::MODE_MIGRATE,
						'cursor' => $last_id,
					),
				);
			}
		}

		return array(
			'processed' => $processed,
			'changed'   => $changed,
			'failures'  => $failures,
			'complete'  => $complete,
			'cursor'    => $complete ? null : $last_id,
		);
	}

	/**
	 * Rotate stored envelopes by re-encrypting their contents.
	 *
	 * @param int  $limit   Maximum rows to evaluate.
	 * @param bool $dry_run Whether to simulate without persisting changes.
	 * @return array<string,mixed>
	 */
	public function rotate( int $limit, bool $dry_run ): array {
		$limit  = max( 1, $limit );
		$cursor = $this->progress_cursor( self::MODE_ROTATE );
		$rows   = $this->fetch_encrypted_rows( $cursor, $limit );

		$processed = 0;
		$changed   = 0;
		$failures  = array();
		$last_id   = $cursor;

		foreach ( $rows as $row ) {
			if ( ! isset( $row['id'] ) || ! is_numeric( $row['id'] ) ) {
				continue;
			}

			++$processed;
			$member_id = (int) $row['id'];
			$updates   = array();

			foreach ( self::COLUMNS as $column ) {
				if ( ! isset( $row[ $column ] ) || ! is_string( $row[ $column ] ) ) {
					continue;
				}

				$value = $row[ $column ];

				if ( ! $this->is_envelope( $value ) ) {
					continue;
				}

				$plaintext = Crypto::decrypt( $value, $this->table, $column, (string) $member_id );

				if ( null === $plaintext ) {
					$failures[] = array(
						'id'     => $member_id,
						'column' => $column,
						'error'  => 'decrypt-failed',
					);
					continue;
				}

				try {
					$updates[ $column ] = Crypto::encrypt( $plaintext, $this->table, $column, (string) $member_id );
				} catch ( RuntimeException $exception ) {
					$failures[] = array(
						'id'     => $member_id,
						'column' => $column,
						'error'  => $exception->getMessage(),
					);
				}
			}

			if ( empty( $updates ) ) {
				$last_id = $member_id;
				continue;
			}

			if ( $dry_run ) {
				++$changed;
				$last_id = $member_id;
				continue;
			}

			if ( $this->update_member( $member_id, $updates ) ) {
				++$changed;
			} else {
				$failures[] = array(
					'id'     => $member_id,
					'column' => implode( ',', array_keys( $updates ) ),
					'error'  => 'update-failed',
				);
			}

			$last_id = $member_id;
		}

		$complete = $this->determine_completion( self::MODE_ROTATE, $limit, $rows, $last_id );

		if ( ! $dry_run ) {
			if ( $complete ) {
				$this->reset_progress();
			} elseif ( $processed > 0 ) {
				$this->save_progress(
					array(
						'mode'   => self::MODE_ROTATE,
						'cursor' => $last_id,
					),
				);
			}
		}

		return array(
			'processed' => $processed,
			'changed'   => $changed,
			'failures'  => $failures,
			'complete'  => $complete,
			'cursor'    => $complete ? null : $last_id,
		);
	}

	/**
	 * Verify the integrity of stored envelopes.
	 *
	 * @return array<string,mixed>
	 */
	public function verify(): array {
		$pattern = self::ENVELOPE_LIKE;
		$sql     = $this->wpdb->prepare(
// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is trusted internal metadata.
			"SELECT id, first_name, last_initial FROM `{$this->table}` WHERE first_name LIKE %s OR last_initial LIKE %s ORDER BY id ASC",
			$pattern,
			$pattern
		);

		$checked  = 0;
		$failures = array();

		if ( is_string( $sql ) ) {
			$rows = $this->wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Prepared above.

			if ( is_array( $rows ) ) {
				foreach ( $rows as $row ) {
					if ( ! isset( $row['id'] ) || ! is_numeric( $row['id'] ) ) {
						continue;
					}

					$member_id = (int) $row['id'];

					foreach ( self::COLUMNS as $column ) {
						if ( ! isset( $row[ $column ] ) || ! is_string( $row[ $column ] ) ) {
							continue;
						}

						$value = $row[ $column ];

						if ( ! $this->is_envelope( $value ) ) {
							continue;
						}

						++$checked;

						if ( null === Crypto::decrypt( $value, $this->table, $column, (string) $member_id ) ) {
							$failures[] = array(
								'id'     => $member_id,
								'column' => $column,
							);
						}
					}
				}
			}
		}

		return array(
			'checked'  => $checked,
			'failures' => $failures,
		);
	}

	/**
	 * Encrypt specified columns for a recently inserted member.
	 *
	 * @param int                  $member_id Member identifier.
	 * @param array<string,string> $values    Plaintext values keyed by column name.
	 * @return bool
	 */
	public function encrypt_new_values( int $member_id, array $values ): bool {
		if ( ! EncryptionSettings::encrypt_new_writes_enabled() ) {
			return false;
		}

		$updates = array();

		foreach ( self::COLUMNS as $column ) {
			if ( ! array_key_exists( $column, $values ) ) {
				continue;
			}

			$value = (string) $values[ $column ];

			try {
				$updates[ $column ] = Crypto::encrypt( $value, $this->table, $column, (string) $member_id );
			} catch ( RuntimeException $exception ) {
				return false;
			}
		}

		if ( empty( $updates ) ) {
			return false;
		}

		return $this->update_member( $member_id, $updates );
	}

	/**
	 * Decrypt managed columns for consumption by repositories.
	 *
	 * @param array<string,mixed> $row Database row.
	 * @return array<string,mixed>
	 */
	public function decrypt_row( array $row ): array {
		if ( ! isset( $row['id'] ) || ! is_numeric( $row['id'] ) ) {
			return $row;
		}

		$member_id = (int) $row['id'];

		foreach ( self::COLUMNS as $column ) {
			if ( ! isset( $row[ $column ] ) || ! is_string( $row[ $column ] ) ) {
				continue;
			}

			$value = $row[ $column ];

			if ( ! $this->is_envelope( $value ) ) {
				continue;
			}

			$decrypted = Crypto::decrypt( $value, $this->table, $column, (string) $member_id );

			$row[ $column ] = null !== $decrypted ? $decrypted : '';
		}

		return $row;
	}

	/**
	 * Retrieve the current progress cursor for the provided mode.
	 *
	 * @param string $mode Processing mode identifier.
	 * @return int
	 */
	private function progress_cursor( string $mode ): int {
		$progress = $this->load_progress();

		if ( isset( $progress['mode'] ) && $mode === $progress['mode'] && isset( $progress['cursor'] ) && is_numeric( $progress['cursor'] ) ) {
			return (int) $progress['cursor'];
		}

		return 0;
	}

	/**
	 * Fetch plaintext rows needing migration.
	 *
	 * @param int $cursor Last processed identifier.
	 * @param int $limit  Maximum rows to fetch.
	 * @return array<int,array<string,mixed>>
	 */
	private function fetch_plaintext_rows( int $cursor, int $limit ): array {
		$pattern = self::ENVELOPE_LIKE;
		$sql     = $this->wpdb->prepare(
// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is trusted internal metadata.
			"SELECT id, first_name, last_initial FROM `{$this->table}` WHERE id > %d AND (first_name NOT LIKE %s OR last_initial NOT LIKE %s) ORDER BY id ASC LIMIT %d",
			$cursor,
			$pattern,
			$pattern,
			$limit
		);

		if ( ! is_string( $sql ) ) {
			return array();
		}

		$rows = $this->wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Prepared above.

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Fetch encrypted rows for rotation.
	 *
	 * @param int $cursor Last processed identifier.
	 * @param int $limit  Maximum rows to fetch.
	 * @return array<int,array<string,mixed>>
	 */
	private function fetch_encrypted_rows( int $cursor, int $limit ): array {
		$pattern = self::ENVELOPE_LIKE;
		$sql     = $this->wpdb->prepare(
// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is trusted internal metadata.
			"SELECT id, first_name, last_initial FROM `{$this->table}` WHERE id > %d AND first_name LIKE %s AND last_initial LIKE %s ORDER BY id ASC LIMIT %d",
			$cursor,
			$pattern,
			$pattern,
			$limit
		);

		if ( ! is_string( $sql ) ) {
			return array();
		}

		$rows = $this->wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Prepared above.

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Update encrypted columns for a member.
	 *
	 * @param int                  $member_id Member identifier.
	 * @param array<string,string> $updates   Column => encrypted value map.
	 * @return bool
	 */
	private function update_member( int $member_id, array $updates ): bool {
		if ( empty( $updates ) ) {
			return false;
		}

		$formats = array_fill( 0, count( $updates ), '%s' );

		$result = $this->wpdb->update(
			$this->table,
			$updates,
			array( 'id' => $member_id ),
			$formats,
			array( '%d' )
		);

		return false !== $result;
	}

	/**
	 * Determine whether processing is complete for the provided mode.
	 *
	 * @param string                         $mode    Processing mode identifier.
	 * @param int                            $limit   Batch size evaluated.
	 * @param array<int,array<string,mixed>> $rows    Current batch rows.
	 * @param int                            $last_id Last processed identifier.
	 * @return bool
	 */
	private function determine_completion( string $mode, int $limit, array $rows, int $last_id ): bool {
		if ( count( $rows ) < $limit ) {
			return true;
		}

		if ( self::MODE_MIGRATE === $mode ) {
			return ! $this->has_remaining_plaintext( $last_id );
		}

		return ! $this->has_remaining_encrypted( $last_id );
	}

	/**
	 * Determine whether plaintext records remain beyond the cursor.
	 *
	 * @param int $cursor Last processed identifier.
	 * @return bool
	 */
	private function has_remaining_plaintext( int $cursor ): bool {
		$pattern = self::ENVELOPE_LIKE;
		$sql     = $this->wpdb->prepare(
// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is trusted internal metadata.
			"SELECT id FROM `{$this->table}` WHERE id > %d AND (first_name NOT LIKE %s OR last_initial NOT LIKE %s) ORDER BY id ASC LIMIT 1",
			$cursor,
			$pattern,
			$pattern
		);

		if ( ! is_string( $sql ) ) {
			return false;
		}

		$result = $this->wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Prepared above.

		return null !== $result;
	}

	/**
	 * Determine whether encrypted records remain beyond the cursor.
	 *
	 * @param int $cursor Last processed identifier.
	 * @return bool
	 */
	private function has_remaining_encrypted( int $cursor ): bool {
		$pattern = self::ENVELOPE_LIKE;
		$sql     = $this->wpdb->prepare(
// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is trusted internal metadata.
			"SELECT id FROM `{$this->table}` WHERE id > %d AND first_name LIKE %s AND last_initial LIKE %s ORDER BY id ASC LIMIT 1",
			$cursor,
			$pattern,
			$pattern
		);

		if ( ! is_string( $sql ) ) {
			return false;
		}

		$result = $this->wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Prepared above.

		return null !== $result;
	}
}
