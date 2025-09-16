<?php
/**
 * Members repository.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Registration;

use FoodBankManager\Core\Install;
use wpdb;

use function gmdate;
use function is_array;
use function is_numeric;
use function is_string;

use const ARRAY_A;

/**
 * Persists member records.
 */
final class MembersRepository {

	private const STATUS_ACTIVE = 'active';

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
		 * Constructor.
		 *
		 * @param wpdb $wpdb WordPress database abstraction.
		 */
	public function __construct( wpdb $wpdb ) {
			$this->wpdb  = $wpdb;
			$this->table = Install::members_table_name( $wpdb );
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
						'SELECT id, status, member_reference FROM %i WHERE email = %s LIMIT 1',
						$this->table,
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
					$row = $this->wpdb->get_row( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $sql prepared above.
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
				'SELECT id FROM %i WHERE member_reference = %s LIMIT 1',
				$this->table,
				$reference
			);

		if ( ! is_string( $sql ) ) {
				return false;
		}

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $sql prepared above.
			$result = $this->wpdb->get_var( $sql );

			return null !== $result;
	}

				/**
				 * Persist a new active member record.
				 *
				 * @param string $reference      Canonical member reference.
				 * @param string $first_name     Sanitized first name.
				 * @param string $last_initial   Sanitized last initial.
				 * @param string $email          Normalized email address.
				 * @param int    $household_size Household size clamp.
				 *
				 * @return int|null Inserted member identifier when successful.
				 */
	public function insert_active_member( string $reference, string $first_name, string $last_initial, string $email, int $household_size ): ?int {
					$now  = gmdate( 'Y-m-d H:i:s' );
					$data = array(
						'member_reference' => $reference,
						'first_name'       => $first_name,
						'last_initial'     => $last_initial,
						'email'            => $email,
						'household_size'   => $household_size,
						'status'           => self::STATUS_ACTIVE,
						'created_at'       => $now,
						'updated_at'       => $now,
						'activated_at'     => $now,
					);

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

					$result = $this->wpdb->insert( $this->table, $data, $formats );

					if ( false === $result ) {
							return null;
					}

					if ( property_exists( $this->wpdb, 'insert_id' ) && is_numeric( $this->wpdb->insert_id ) ) {
							$insert_id = (int) $this->wpdb->insert_id;

						if ( $insert_id > 0 ) {
								return $insert_id;
						}
					}

					return null;
	}

		/**
		 * Mark an existing member record as active.
		 *
		 * @param int $id Member row ID.
		 */
	public function mark_active( int $id ): bool {
					$now   = gmdate( 'Y-m-d H:i:s' );
					$data  = array(
						'status'       => self::STATUS_ACTIVE,
						'updated_at'   => $now,
						'activated_at' => $now,
					);
					$where = array( 'id' => $id );

					$result = $this->wpdb->update( $this->table, $data, $where, array( '%s', '%s', '%s' ), array( '%d' ) );

					return false !== $result;
	}

	/**
	 * Retrieve all member records for administrative display.
	 *
	 * @return array<int, array{id:int,member_reference:string,first_name:string,last_initial:string,email:string,status:string,activated_at:?string}>
	 */
	public function all(): array {
		$sql = $this->wpdb->prepare(
			'SELECT id, member_reference, first_name, last_initial, email, status, activated_at FROM %i ORDER BY activated_at DESC, first_name ASC',
			$this->table
		);

		if ( ! is_string( $sql ) ) {
			return array();
		}

		/**
		 * Raw member rows.
		 *
		 * @var array<int, array<string, mixed>>|null $rows
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

			$members[] = array(
				'id'               => (int) $row['id'],
				'member_reference' => (string) $row['member_reference'],
				'first_name'       => (string) $row['first_name'],
				'last_initial'     => (string) $row['last_initial'],
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
	 * @return array{id:int,member_reference:string,first_name:string,email:string}|null
	 */
	public function find( int $member_id ): ?array {
		$sql = $this->wpdb->prepare(
			'SELECT id, member_reference, first_name, email FROM %i WHERE id = %d LIMIT 1',
			$this->table,
			$member_id
		);

		if ( ! is_string( $sql ) ) {
			return null;
		}

		/**
		 * Result row data.
		 *
		 * @var array{id:numeric,member_reference:string,first_name:string,email:string}|null $row
		 */
		$row = $this->wpdb->get_row( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Prepared above.

		if ( ! is_array( $row ) ) {
			return null;
		}

		return array(
			'id'               => (int) $row['id'],
			'member_reference' => (string) $row['member_reference'],
			'first_name'       => (string) $row['first_name'],
			'email'            => (string) $row['email'],
		);
	}
}
