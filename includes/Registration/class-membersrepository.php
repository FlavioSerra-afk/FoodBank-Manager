<?php
/**
 * Members repository.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Registration;

use FoodBankManager\Core\Install;
use FoodBankManager\Crypto\Adapters\MembersPiiAdapter;
use wpdb;

use function array_key_exists;
use function gmdate;
use function is_array;
use function is_numeric;
use function is_string;

use const ARRAY_A;

/**
 * Persists member records.
 */
final class MembersRepository {

	public const STATUS_ACTIVE  = 'active';
	public const STATUS_PENDING = 'pending';
	public const STATUS_REVOKED = 'revoked';

		/**
		 * WordPress database client.
		 *
		 * @var wpdb
		 */
	private wpdb $wpdb;

		/**
		 * Fully qualified table name.
		 *
		 * @var string
		 */
	private string $table;

		/**
		 * Encryption adapter for PII fields.
		 *
		 * @var MembersPiiAdapter
		 */
	private MembersPiiAdapter $encryption;

		/**
		 * Constructor.
		 *
		 * @param wpdb $wpdb WordPress database abstraction.
		 */
	public function __construct( wpdb $wpdb ) {
			$this->wpdb       = $wpdb;
			$this->table      = Install::members_table_name( $wpdb );
			$this->encryption = new MembersPiiAdapter( $wpdb );
	}

		/**
		 * Locate a member record by email address.
		 *
		 * @param string $email Normalized email address.
		 *
		 * @return array{id:int,status:string,member_reference:string}|null
		 */
	public function find_by_email( string $email ): ?array {
			$sql = $this->wpdb->prepare(
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is trusted.
				"SELECT id, status, member_reference FROM `{$this->table}` WHERE email = %s LIMIT 1",
				$email
			);

		if ( ! is_string( $sql ) ) {
				return null;
		}

			/**
			 * Result row data.
			 *
			 * @var array{id:numeric,status:string,member_reference:string}|null $row
			 */
			$row = $this->wpdb->get_row( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Prepared above.

		if ( ! is_array( $row ) ) {
				return null;
		}

			return array(
				'id'               => (int) $row['id'],
				'status'           => (string) $row['status'],
				'member_reference' => (string) $row['member_reference'],
			);
	}

		/**
		 * Locate a member record by canonical reference string.
		 *
		 * @param string $reference Canonical member reference.
		 *
		 * @return array{id:int,status:string,member_reference:string}|null
		 */
	public function find_by_reference( string $reference ): ?array {
			$sql = $this->wpdb->prepare(
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is trusted.
				"SELECT id, status, member_reference FROM `{$this->table}` WHERE member_reference = %s LIMIT 1",
				$reference
			);

		if ( ! is_string( $sql ) ) {
				return null;
		}

			/**
			 * Result row data.
			 *
			 * @var array{id:numeric,status:string,member_reference:string}|null $row
			 */
			$row = $this->wpdb->get_row( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Prepared above.

		if ( ! is_array( $row ) ) {
				return null;
		}

			return array(
				'id'               => (int) $row['id'],
				'status'           => (string) $row['status'],
				'member_reference' => (string) $row['member_reference'],
			);
	}

		/**
		 * Determine whether a member reference already exists.
		 *
		 * @param string $reference Candidate member reference.
		 */
	public function reference_exists( string $reference ): bool {
			$sql = $this->wpdb->prepare(
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is trusted.
				"SELECT id FROM `{$this->table}` WHERE member_reference = %s LIMIT 1",
				$reference
			);

		if ( ! is_string( $sql ) ) {
				return false;
		}

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Prepared above.
			$result = $this->wpdb->get_var( $sql );

			return null !== $result;
	}

		/**
		 * Persist a new active member record.
		 *
		 * @param string      $reference           Canonical member reference.
		 * @param string      $first_name          Sanitized first name.
		 * @param string      $last_initial        Sanitized last initial.
		 * @param string      $email               Normalized email address.
		 * @param int         $household_size      Household size clamp.
		 * @param string|null $consent_recorded_at Consent acknowledgement timestamp.
		 *
		 * @return int|null Inserted member identifier when successful.
		 */
	public function insert_active_member( string $reference, string $first_name, string $last_initial, string $email, int $household_size, ?string $consent_recorded_at = null ): ?int {
			return $this->insert_member( $reference, $first_name, $last_initial, $email, $household_size, self::STATUS_ACTIVE, $consent_recorded_at );
	}

		/**
		 * Persist a new pending member record.
		 *
		 * @param string      $reference           Canonical member reference.
		 * @param string      $first_name          Sanitized first name.
		 * @param string      $last_initial        Sanitized last initial.
		 * @param string      $email               Normalized email address.
		 * @param int         $household_size      Household size clamp.
		 * @param string|null $consent_recorded_at Consent acknowledgement timestamp.
		 *
		 * @return int|null Inserted member identifier when successful.
		 */
	public function insert_pending_member( string $reference, string $first_name, string $last_initial, string $email, int $household_size, ?string $consent_recorded_at = null ): ?int {
			return $this->insert_member( $reference, $first_name, $last_initial, $email, $household_size, self::STATUS_PENDING, $consent_recorded_at );
	}

		/**
		 * Persist a member record with the provided status.
		 *
		 * @param string      $reference           Canonical member reference.
		 * @param string      $first_name          Sanitized first name.
		 * @param string      $last_initial        Sanitized last initial.
		 * @param string      $email               Normalized email address.
		 * @param int         $household_size      Household size clamp.
		 * @param string      $status              Member status value.
		 * @param string|null $consent_recorded_at Consent acknowledgement timestamp.
		 */
	private function insert_member( string $reference, string $first_name, string $last_initial, string $email, int $household_size, string $status, ?string $consent_recorded_at = null ): ?int {
			$now = gmdate( 'Y-m-d H:i:s' );

			$data = array(
				'member_reference'    => $reference,
				'first_name'          => $first_name,
				'last_initial'        => $last_initial,
				'email'               => $email,
				'household_size'      => $household_size,
				'status'              => $status,
				'created_at'          => $now,
				'updated_at'          => $now,
				'activated_at'        => self::STATUS_ACTIVE === $status ? $now : null,
				'consent_recorded_at' => null,
			);

			if ( null !== $consent_recorded_at && '' !== $consent_recorded_at ) {
					$data['consent_recorded_at'] = $consent_recorded_at;
			} else {
					unset( $data['consent_recorded_at'] );
			}

			$formats = array(
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
			);

			if ( array_key_exists( 'consent_recorded_at', $data ) ) {
					$formats[] = '%s';
			}

			$result = $this->wpdb->insert( $this->table, $data, $formats );

			if ( false === $result ) {
					return null;
			}

			$insert_id = null;

			if ( property_exists( $this->wpdb, 'insert_id' ) && is_numeric( $this->wpdb->insert_id ) ) {
					$candidate = (int) $this->wpdb->insert_id;

				if ( $candidate > 0 ) {
						$insert_id = $candidate;
				}
			}

			if ( null === $insert_id ) {
					return null;
			}

			$this->encryption->encrypt_new_values(
				$insert_id,
				array(
					'first_name'   => $first_name,
					'last_initial' => $last_initial,
				)
			);

			return $insert_id;
	}

		/**
		 * Mark an existing member record as active.
		 *
		 * @param int         $id                  Member row ID.
		 * @param string|null $consent_recorded_at Consent acknowledgement timestamp.
		 */
	public function mark_active( int $id, ?string $consent_recorded_at = null ): bool {
			$now    = gmdate( 'Y-m-d H:i:s' );
			$data   = array(
				'status'       => self::STATUS_ACTIVE,
				'updated_at'   => $now,
				'activated_at' => $now,
			);
			$format = array( '%s', '%s', '%s' );

			if ( null !== $consent_recorded_at && '' !== $consent_recorded_at ) {
					$data['consent_recorded_at'] = $consent_recorded_at;
					$format[]                    = '%s';
			}

			$result = $this->wpdb->update( $this->table, $data, array( 'id' => $id ), $format, array( '%d' ) );

			return false !== $result;
	}

		/**
		 * Mark an existing member record as pending.
		 *
		 * @param int $id Member row ID.
		 */
	public function mark_pending( int $id ): bool {
			$now    = gmdate( 'Y-m-d H:i:s' );
			$data   = array(
				'status'       => self::STATUS_PENDING,
				'updated_at'   => $now,
				'activated_at' => null,
			);
			$format = array( '%s', '%s', '%s' );

			$result = $this->wpdb->update( $this->table, $data, array( 'id' => $id ), $format, array( '%d' ) );

			return false !== $result;
	}

		/**
		 * Mark an existing member record as revoked.
		 *
		 * @param int $id Member row ID.
		 */
	public function mark_revoked( int $id ): bool {
			$now    = gmdate( 'Y-m-d H:i:s' );
			$data   = array(
				'status'       => self::STATUS_REVOKED,
				'updated_at'   => $now,
				'activated_at' => null,
			);
			$format = array( '%s', '%s', '%s' );

			$result = $this->wpdb->update( $this->table, $data, array( 'id' => $id ), $format, array( '%d' ) );

			return false !== $result;
	}

		/**
		 * Retrieve all member records for administrative display.
		 *
		 * @return array<int,array{id:int,member_reference:string,first_name:string,last_initial:string,email:string,status:string,activated_at:?string}>
		 */
	public function all(): array {
			$sql = $this->wpdb->prepare(
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is trusted.
				"SELECT id, member_reference, first_name, last_initial, email, status, activated_at FROM `{$this->table}` ORDER BY activated_at DESC, first_name ASC"
			);

		if ( ! is_string( $sql ) ) {
				return array();
		}

			/**
			 * Raw member rows.
			 *
			 * @var array<int,array<string,mixed>>|null $rows
			 */
			$rows = $this->wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Prepared above.

		if ( ! is_array( $rows ) ) {
				return array();
		}

			$members = array();

		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
					continue;
			}

				$row = $this->encryption->decrypt_row( $row );

				$members[] = array(
					'id'               => (int) $row['id'],
					'member_reference' => (string) $row['member_reference'],
					'first_name'       => (string) $row['first_name'],
					'last_initial'     => (string) ( $row['last_initial'] ?? '' ),
					'email'            => (string) $row['email'],
					'status'           => (string) $row['status'],
					'activated_at'     => isset( $row['activated_at'] ) && '' !== $row['activated_at'] ? (string) $row['activated_at'] : null,
				);
		}

			return $members;
	}

		/**
		 * Locate a member record by identifier.
		 *
		 * @param int $member_id Member identifier.
		 *
		 * @return array{id:int,member_reference:string,first_name:string,last_initial:string,email:string,status:string}|null
		 */
	public function find( int $member_id ): ?array {
			$sql = $this->wpdb->prepare(
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is trusted.
				"SELECT id, status, member_reference, first_name, last_initial, email FROM `{$this->table}` WHERE id = %d LIMIT 1",
				$member_id
			);

		if ( ! is_string( $sql ) ) {
				return null;
		}

			/**
			 * Result row data.
			 *
			 * @var array{id:numeric,status:string,member_reference:string,first_name:string,last_initial?:string,email:string}|null $row
			 */
			$row = $this->wpdb->get_row( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Prepared above.

		if ( ! is_array( $row ) ) {
				return null;
		}

			$row = $this->encryption->decrypt_row( $row );

			return array(
				'id'               => (int) $row['id'],
				'status'           => (string) $row['status'],
				'member_reference' => (string) $row['member_reference'],
				'first_name'       => (string) $row['first_name'],
				'last_initial'     => isset( $row['last_initial'] ) ? (string) $row['last_initial'] : '',
				'email'            => (string) $row['email'],
			);
	}
}
