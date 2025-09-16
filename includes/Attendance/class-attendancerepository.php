<?php
/**
 * Attendance repository.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Attendance;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use FoodBankManager\Core\Install;
use wpdb;
use function gmdate;

/**
 * Persists attendance records.
 */
final class AttendanceRepository {
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
		 * Class constructor.
		 *
		 * @param wpdb $wpdb WordPress database abstraction.
		 */
	public function __construct( wpdb $wpdb ) {
			$this->wpdb  = $wpdb;
			$this->table = Install::attendance_table_name( $wpdb );
	}

	/**
	 * Determine if a member has already checked in for the provided date.
	 *
	 * @param string            $member_reference Canonical member reference.
	 * @param DateTimeImmutable $date             Date to check (UTC).
	 */
	public function has_checked_in_for_date( string $member_reference, DateTimeImmutable $date ): bool {
				$sql = $this->wpdb->prepare(
					'SELECT id FROM %i WHERE member_reference = %s AND collected_date = %s LIMIT 1',
					$this->table,
					$member_reference,
					$date->format( 'Y-m-d' )
				);
		if ( ! is_string( $sql ) ) {
				return false;
		}

                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $sql prepared above via $wpdb->prepare().
				$result = $this->wpdb->get_var( $sql );

			return null !== $result;
	}

	/**
	 * Insert a new attendance record.
	 *
	 * @param string            $member_reference Canonical member reference.
	 * @param string            $method           Collection method.
	 * @param int|null          $user_id          Recording user ID.
	 * @param DateTimeImmutable $recorded_at      Timestamp in UTC.
	 * @param string|null       $note             Optional note.
	 */
	public function record( string $member_reference, string $method, ?int $user_id, DateTimeImmutable $recorded_at, ?string $note = null ): bool {
			$data = array(
				'member_reference' => $member_reference,
				'collected_at'     => $recorded_at->format( 'Y-m-d H:i:s' ),
				'collected_date'   => $recorded_at->format( 'Y-m-d' ),
				'method'           => $method,
				'note'             => $note,
				'recorded_by'      => $user_id,
				'created_at'       => gmdate( 'Y-m-d H:i:s' ),
			);

			$formats = array(
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
			);

			$result = $this->wpdb->insert( $this->table, $data, $formats );

			return false !== $result;
	}

		/**
		 * Retrieve the most recent collection timestamp for a member.
		 *
		 * @param string $member_reference Canonical member reference.
		 *
		 * @return DateTimeImmutable|null Most recent timestamp in UTC, or null when unavailable.
		 */
	public function latest_for_member( string $member_reference ): ?DateTimeImmutable {
			$sql = $this->wpdb->prepare(
				'SELECT collected_at FROM %i WHERE member_reference = %s ORDER BY collected_at DESC LIMIT 1',
				$this->table,
				$member_reference
			);

		if ( ! is_string( $sql ) ) {
				return null;
		}

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $sql prepared above via $wpdb->prepare().
			$value = $this->wpdb->get_var( $sql );

		if ( ! is_string( $value ) || '' === $value ) {
				return null;
		}

		try {
				return new DateTimeImmutable( $value, new DateTimeZone( 'UTC' ) );
		} catch ( Exception ) {
				return null;
		}
	}
}
