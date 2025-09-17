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
use FoodBankManager\Registration\MembersRepository;
use wpdb;
use function array_key_exists;
use function gmdate;
use function is_array;
use const ARRAY_A;

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
         * Fully qualified override audit table name.
         *
         * @var string
         */
        private string $override_table;

        /**
         * Fully qualified members table name.
         *
         * @var string
         */
        private string $members_table;

	/**
	 * Class constructor.
	 *
	 * @param wpdb $wpdb WordPress database abstraction.
	 */
	public function __construct( wpdb $wpdb ) {
		$this->wpdb           = $wpdb;
		$this->table          = Install::attendance_table_name( $wpdb );
                $this->override_table = Install::attendance_overrides_table_name( $wpdb );
                $this->members_table  = Install::members_table_name( $wpdb );
	}

	/**
	 * Determine if a member has already checked in for the provided date.
	 *
	 * @param string            $member_reference Canonical member reference.
	 * @param DateTimeImmutable $date             Date to check (UTC).
	 */
	public function has_checked_in_for_date( string $member_reference, DateTimeImmutable $date ): bool {
		$sql = $this->wpdb->prepare(
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is trusted.
			"SELECT id FROM `{$this->table}` WHERE member_reference = %s AND collected_date = %s LIMIT 1",
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
	public function record( string $member_reference, string $method, ?int $user_id, DateTimeImmutable $recorded_at, ?string $note = null ): ?int {
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

		if ( false === $result ) {
			return null;
		}

                $insert_id = (int) $this->wpdb->insert_id;

                if ( $insert_id > 0 ) {
                        AttendanceReportService::invalidate_cache();

                        return $insert_id;
                }

                return null;
	}

	/**
	 * Record an override audit entry.
	 *
	 * @param int               $attendance_id    Recorded attendance identifier.
	 * @param string            $member_reference Canonical member reference.
	 * @param int               $user_id          Acting user ID.
	 * @param DateTimeImmutable $recorded_at      Timestamp in UTC.
	 * @param string            $note             Override note.
	 */
	public function record_override_audit( int $attendance_id, string $member_reference, int $user_id, DateTimeImmutable $recorded_at, string $note ): bool {
		$data = array(
			'attendance_id'    => $attendance_id,
			'member_reference' => $member_reference,
			'override_by'      => $user_id,
			'override_note'    => $note,
			'override_at'      => $recorded_at->format( 'Y-m-d H:i:s' ),
			'created_at'       => gmdate( 'Y-m-d H:i:s' ),
		);

		$formats = array(
			'%d',
			'%s',
			'%d',
			'%s',
			'%s',
			'%s',
		);

		$result = $this->wpdb->insert( $this->override_table, $data, $formats );

		return false !== $result;
	}

	/**
	 * Delete an attendance record by identifier.
	 *
	 * @param int $attendance_id Attendance identifier to delete.
	 */
        public function delete_attendance_record( int $attendance_id ): void {
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- wpdb::delete() is used directly for cleanup.
                $this->wpdb->delete( $this->table, array( 'id' => $attendance_id ), array( '%d' ) );

                AttendanceReportService::invalidate_cache();
        }

        /**
         * Summarize attendance records within a date range grouped by member status.
         *
         * @param DateTimeImmutable $start Inclusive range start (UTC).
         * @param DateTimeImmutable $end   Inclusive range end (UTC).
         *
         * @return array{start:string,end:string,total:int,active:int,revoked:int,other:int}
         */
        public function summarize_range( DateTimeImmutable $start, DateTimeImmutable $end ): array {
                $sql = $this->wpdb->prepare(
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table names are trusted.
                        "SELECT COUNT(*) AS total,
                                SUM(CASE WHEN m.status = %s THEN 1 ELSE 0 END) AS active_total,
                                SUM(CASE WHEN m.status = %s THEN 1 ELSE 0 END) AS revoked_total
                        FROM `{$this->table}` a
                        LEFT JOIN `{$this->members_table}` m ON m.member_reference = a.member_reference
                        WHERE a.collected_date BETWEEN %s AND %s",
                        MembersRepository::STATUS_ACTIVE,
                        MembersRepository::STATUS_REVOKED,
                        $start->format( 'Y-m-d' ),
                        $end->format( 'Y-m-d' )
                );

                if ( ! is_string( $sql ) ) {
                        return $this->empty_summary( $start, $end );
                }

                /**
                 * Aggregated row.
                 *
                 * @var array<string,mixed>|null $row
                 */
                $row = $this->wpdb->get_row( $sql, ARRAY_A );

                if ( ! is_array( $row ) ) {
                        return $this->empty_summary( $start, $end );
                }

                $total   = isset( $row['total'] ) ? (int) $row['total'] : 0;
                $active  = isset( $row['active_total'] ) ? (int) $row['active_total'] : 0;
                $revoked = isset( $row['revoked_total'] ) ? (int) $row['revoked_total'] : 0;
                $other   = $total - $active - $revoked;

                if ( $other < 0 ) {
                        $other = 0;
                }

                return array(
                        'start'   => $start->format( 'Y-m-d' ),
                        'end'     => $end->format( 'Y-m-d' ),
                        'total'   => $total,
                        'active'  => $active,
                        'revoked' => $revoked,
                        'other'   => $other,
                );
        }

        /**
         * Retrieve attendance records for export within the provided date range.
         *
         * @param DateTimeImmutable $start Inclusive range start (UTC).
         * @param DateTimeImmutable $end   Inclusive range end (UTC).
         *
         * @return array<int,array{member_reference:string,collected_at:string,collected_date:string,method:string,note:?string,recorded_by:?int,status:string}>
         */
        public function fetch_range( DateTimeImmutable $start, DateTimeImmutable $end ): array {
                $sql = $this->wpdb->prepare(
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table names are trusted.
                        "SELECT a.member_reference, a.collected_at, a.collected_date, a.method, a.note, a.recorded_by, m.status
                        FROM `{$this->table}` a
                        LEFT JOIN `{$this->members_table}` m ON m.member_reference = a.member_reference
                        WHERE a.collected_date BETWEEN %s AND %s
                        ORDER BY a.collected_at ASC",
                        $start->format( 'Y-m-d' ),
                        $end->format( 'Y-m-d' )
                );

                if ( ! is_string( $sql ) ) {
                        return array();
                }

                /**
                 * Export rows.
                 *
                 * @var array<int,array<string,mixed>>|null $rows
                 */
                $rows = $this->wpdb->get_results( $sql, ARRAY_A );

                if ( ! is_array( $rows ) ) {
                        return array();
                }

                $normalized = array();

                foreach ( $rows as $row ) {
                        if ( ! is_array( $row ) ) {
                                continue;
                        }

                        $normalized[] = array(
                                'member_reference' => isset( $row['member_reference'] ) ? (string) $row['member_reference'] : '',
                                'collected_at'     => isset( $row['collected_at'] ) ? (string) $row['collected_at'] : '',
                                'collected_date'   => isset( $row['collected_date'] ) ? (string) $row['collected_date'] : '',
                                'method'           => isset( $row['method'] ) ? (string) $row['method'] : '',
                                'note'             => array_key_exists( 'note', $row ) ? ( null !== $row['note'] ? (string) $row['note'] : null ) : null,
                                'recorded_by'      => array_key_exists( 'recorded_by', $row ) && null !== $row['recorded_by'] ? (int) $row['recorded_by'] : null,
                                'status'           => isset( $row['status'] ) ? (string) $row['status'] : '',
                        );
                }

                return $normalized;
        }

        /**
         * Provide a zeroed summary for invalid query states.
         *
         * @param DateTimeImmutable $start Range start.
         * @param DateTimeImmutable $end   Range end.
         *
         * @return array{start:string,end:string,total:int,active:int,revoked:int,other:int}
         */
        private function empty_summary( DateTimeImmutable $start, DateTimeImmutable $end ): array {
                return array(
                        'start'   => $start->format( 'Y-m-d' ),
                        'end'     => $end->format( 'Y-m-d' ),
                        'total'   => 0,
                        'active'  => 0,
                        'revoked' => 0,
                        'other'   => 0,
                );
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
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is trusted.
			"SELECT collected_at FROM `{$this->table}` WHERE member_reference = %s ORDER BY collected_at DESC LIMIT 1",
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
