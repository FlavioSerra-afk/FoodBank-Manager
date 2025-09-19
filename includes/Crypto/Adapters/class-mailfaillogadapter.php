<?php
/**
 * Mail failure log encryption adapter.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Crypto\Adapters;

use FoodBankManager\Crypto\Crypto;
use FoodBankManager\Crypto\EncryptionAdapter;
use RuntimeException;
use function __;
use function array_keys;
use function count;
use function get_option;
use function is_array;
use function is_string;
use function max;
use function update_option;

/**
 * Provides envelope migration for the diagnostics mail failure option.
 */
final class MailFailLogAdapter extends EncryptionAdapter {
	private const OPTION_KEY   = 'fbm_mail_failures';
	private const MODE_MIGRATE = 'migrate';
	private const MODE_ROTATE  = 'rotate';

	/**
	 * Retrieve the adapter identifier.
	 */
	public function id(): string {
		return 'mail_fail_log';
	}

	/**
	 * Retrieve the adapter label for display contexts.
	 */
	public function label(): string {
		return __( 'Mail failure log', 'foodbank-manager' );
	}

	/**
	 * Retrieve adapter status metrics.
	 *
	 * @return array<string,mixed>
	 */
	public function status(): array {
		$entries   = $this->load_entries();
		$total     = count( $entries );
		$encrypted = 0;

		foreach ( $entries as $entry ) {
			if ( isset( $entry['email'] ) && is_string( $entry['email'] ) && $this->is_envelope( $entry['email'] ) ) {
				++$encrypted;
			}
		}

		$progress = $this->load_progress();
		$cursor   = isset( $progress['cursor'] ) && is_string( $progress['cursor'] ) ? $progress['cursor'] : null;
		$mode     = isset( $progress['mode'] ) ? (string) $progress['mode'] : '';

		return array(
			'id'        => $this->id(),
			'label'     => $this->label(),
			'total'     => $total,
			'encrypted' => $encrypted,
			'remaining' => max( 0, $total - $encrypted ),
			'progress'  => null !== $cursor ? array(
				'cursor' => $cursor,
				'mode'   => $mode,
			) : null,
		);
	}

	/**
	 * Migrate plaintext entries into encrypted envelopes.
	 *
	 * @param int  $limit   Maximum rows to evaluate.
	 * @param bool $dry_run Whether to simulate without persisting changes.
	 *
	 * @return array<string,mixed>
	 */
	public function migrate( int $limit, bool $dry_run ): array {
		return $this->process_entries( self::MODE_MIGRATE, $limit, $dry_run );
	}

	/**
	 * Rotate existing envelopes by decrypting and re-encrypting entries.
	 *
	 * @param int  $limit   Maximum rows to evaluate.
	 * @param bool $dry_run Whether to simulate without persisting changes.
	 *
	 * @return array<string,mixed>
	 */
	public function rotate( int $limit, bool $dry_run ): array {
		return $this->process_entries( self::MODE_ROTATE, $limit, $dry_run );
	}

	/**
	 * Verify that stored envelopes can be decrypted successfully.
	 *
	 * @return array<string,mixed>
	 */
	public function verify(): array {
		$entries  = $this->load_entries();
		$checked  = 0;
		$failures = array();

		foreach ( $entries as $entry ) {
			if ( ! isset( $entry['id'] ) || ! isset( $entry['email'] ) || ! is_string( $entry['email'] ) ) {
				continue;
			}

			$email = $entry['email'];

			if ( ! $this->is_envelope( $email ) ) {
				continue;
			}

			++$checked;
			$record_id = (string) $entry['id'];

			if ( null === Crypto::decrypt( $email, self::OPTION_KEY, 'email', $record_id ) ) {
				$failures[] = array(
					'id' => $record_id,
				);
			}
		}

		return array(
			'checked'  => $checked,
			'failures' => $failures,
		);
	}

	/**
	 * Encrypt a stored email value for the given entry identifier.
	 *
	 * @param string $entry_id Entry identifier.
	 * @param string $value    Plaintext email value.
	 * @return string
	 */
	public function encrypt_email( string $entry_id, string $value ): string {
		return Crypto::encrypt( $value, self::OPTION_KEY, 'email', $entry_id );
	}

	/**
	 * Decrypt an email value, returning the original value on success or the input when plaintext.
	 *
	 * @param string $entry_id Entry identifier.
	 * @param string $value    Potentially encrypted value.
	 * @return string|null
	 */
	public function decrypt_email( string $entry_id, string $value ): ?string {
		if ( ! $this->is_envelope( $value ) ) {
			return $value;
		}

		return Crypto::decrypt( $value, self::OPTION_KEY, 'email', $entry_id );
	}

	/**
	 * Process option entries for migration or rotation.
	 *
	 * @param string $mode    Processing mode identifier.
	 * @param int    $limit   Maximum number of records to evaluate.
	 * @param bool   $dry_run Whether to simulate without persisting changes.
	 * @return array<string,mixed>
	 */
	private function process_entries( string $mode, int $limit, bool $dry_run ): array {
		$limit         = max( 1, $limit );
		$entries       = $this->load_entries();
		$progress      = $this->load_progress();
		$last_id       = isset( $progress['cursor'] ) && is_string( $progress['cursor'] ) ? $progress['cursor'] : null;
		$processed_set = array();

		if ( self::MODE_ROTATE === $mode && isset( $progress['processed_ids'] ) && is_array( $progress['processed_ids'] ) ) {
			foreach ( $progress['processed_ids'] as $processed_id => $flag ) {
				if ( ! is_string( $processed_id ) ) {
					continue;
				}

				if ( ! empty( $flag ) ) {
					$processed_set[ $processed_id ] = true;
				}
			}
		}

		$processed = 0;
		$changed   = 0;
		$failures  = array();
		$rotated   = array();

		foreach ( $entries as $index => $entry ) {
			if ( ! isset( $entry['id'] ) || ! isset( $entry['email'] ) || ! is_string( $entry['email'] ) ) {
				continue;
			}

			$record_id = (string) $entry['id'];
			$email     = $entry['email'];

			if ( self::MODE_ROTATE === $mode && isset( $processed_set[ $record_id ] ) ) {
				continue;
			}

			$requires_action = ( self::MODE_MIGRATE === $mode ) ? ( '' !== $email && ! $this->is_envelope( $email ) ) : $this->is_envelope( $email );

			if ( ! $requires_action ) {
				continue;
			}

			if ( $processed >= $limit ) {
				break;
			}

			++$processed;
			$last_id = $record_id;

			if ( $dry_run ) {
				++$changed;
				if ( self::MODE_ROTATE === $mode ) {
					$rotated[] = $record_id;
				}
				continue;
			}

			try {
				if ( self::MODE_MIGRATE === $mode ) {
					$entries[ $index ]['email'] = Crypto::encrypt( $email, self::OPTION_KEY, 'email', $record_id );
				} else {
					$plaintext = Crypto::decrypt( $email, self::OPTION_KEY, 'email', $record_id );

					if ( null === $plaintext ) {
						$failures[] = array(
							'id'    => $record_id,
							'error' => 'decrypt-failed',
						);
						continue;
					}

					$entries[ $index ]['email'] = Crypto::encrypt( $plaintext, self::OPTION_KEY, 'email', $record_id );
					$rotated[]                  = $record_id;
				}
			} catch ( RuntimeException $exception ) {
				$failures[] = array(
					'id'    => $record_id,
					'error' => $exception->getMessage(),
				);
				continue;
			}

			++$changed;
		}

		if ( ! $dry_run && $changed > 0 ) {
			$this->persist_entries( $entries );
		}

		$progress_ids = $processed_set;

		foreach ( $rotated as $identifier ) {
			$progress_ids[ $identifier ] = true;
		}

		$complete = self::MODE_ROTATE === $mode ? $this->is_mode_complete( $entries, $mode, $progress_ids ) : $this->is_mode_complete( $entries, $mode );

		if ( ! $dry_run ) {
			if ( $complete ) {
				$this->reset_progress();
			} elseif ( null !== $last_id ) {
				$payload = array(
					'mode'   => $mode,
					'cursor' => $last_id,
				);

				if ( self::MODE_ROTATE === $mode ) {
					$payload['processed_ids'] = array_keys( $progress_ids );
				}

				$this->save_progress( $payload );
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
	 * Determine whether additional entries require processing for the given mode.
	 *
	 * @param array<int,array<string,mixed>> $entries       Mail failure entries.
	 * @param string                         $mode          Processing mode identifier.
	 * @param array<string,bool>             $processed_ids Map of rotated entry identifiers.
	 * @return bool
	 */
	private function is_mode_complete( array $entries, string $mode, array $processed_ids = array() ): bool {
		foreach ( $entries as $entry ) {
			if ( ! isset( $entry['email'] ) || ! is_string( $entry['email'] ) ) {
				continue;
			}

			$email    = $entry['email'];
			$entry_id = isset( $entry['id'] ) ? (string) $entry['id'] : '';

			if ( self::MODE_MIGRATE === $mode ) {
				if ( '' !== $email && ! $this->is_envelope( $email ) ) {
					return false;
				}
			} elseif ( $this->is_envelope( $email ) ) {
				if ( '' === $entry_id || ! isset( $processed_ids[ $entry_id ] ) ) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Load stored entries from the option table.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	private function load_entries(): array {
		$raw = get_option( self::OPTION_KEY, array() );

		return is_array( $raw ) ? $raw : array();
	}

	/**
	 * Persist entries back to the option store.
	 *
	 * @param array<int,array<string,mixed>> $entries Mail failure entries.
	 */
	private function persist_entries( array $entries ): void {
		update_option( self::OPTION_KEY, $entries, false );
	}
}
