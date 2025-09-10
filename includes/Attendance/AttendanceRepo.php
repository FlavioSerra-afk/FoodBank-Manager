<?php
/**
 * Attendance repository.
 *
 * @package FoodBankManager\Attendance
 */

declare(strict_types=1);

namespace FoodBankManager\Attendance;

use DateTimeImmutable;
use FoodBankManager\Core\Options;
use wpdb;
use function absint;
use function sanitize_key;
use function sanitize_text_field;
use function sanitize_textarea_field;

/**
 * Data access for attendance records.
 *
 * @since 0.1.x
 */
final class AttendanceRepo {
	/**
	 * Get the most recent 'present' attendance timestamp for an application.
	 *
	 * @since 0.1.x
	 *
	 * @param int $application_id Application ID.
	 * @return string|null UTC datetime or null when none.
	 */
        public static function last_present( int $application_id ): ?string {
                global $wpdb;
                $application_id = absint( $application_id );

                $t_att = $wpdb->prefix . 'fb_attendance';
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name is constant.
                $sql    = "SELECT attendance_at
                        FROM {$t_att}
                        WHERE application_id = %d AND status = 'present'
                        ORDER BY attendance_at DESC
                        LIMIT 1";
                $last   = $wpdb->get_var( $wpdb->prepare( $sql, $application_id ) );

                return $last ? $last : null;
        }

		/**
		 * Find attendance rows for an application.
		 *
		 * @param int $application_id Application ID.
		 * @return array<int,array>
		 */
        public static function find_by_application_id( int $application_id ): array {
                global $wpdb;
                $application_id = absint( $application_id );
                $t_att          = $wpdb->prefix . 'fb_attendance';
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name is constant.
                $sql            = "SELECT id, status, attendance_at, event_id, type, method
                        FROM {$t_att} WHERE application_id = %d";
                $rows           = $wpdb->get_results( $wpdb->prepare( $sql, $application_id ), 'ARRAY_A' );
                $out            = array();
                foreach ( $rows ? $rows : array() as $row ) {
                        $out[] = array(
                                'id'            => (int) ( $row['id'] ?? 0 ),
                                'status'        => sanitize_key( (string) ( $row['status'] ?? '' ) ),
				'attendance_at' => sanitize_text_field( (string) ( $row['attendance_at'] ?? '' ) ),
				'event_id'      => (int) ( $row['event_id'] ?? 0 ),
				'type'          => sanitize_text_field( (string) ( $row['type'] ?? '' ) ),
				'method'        => sanitize_text_field( (string) ( $row['method'] ?? '' ) ),
			);
		}

					return $out;
	}

		/**
		 * Anonymise attendance rows.
		 *
		 * @param array<int> $ids IDs to anonymise.
		 * @return int Rows affected.
		 */
        public static function anonymise_batch( array $ids ): int {
                global $wpdb;
                $ids = array_values( array_filter( array_map( 'absint', $ids ) ) );
                if ( empty( $ids ) ) {
                        return 0;
                }

                $t_att       = $wpdb->prefix . 'fb_attendance';
                $placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name is constant.
                $sql         = "UPDATE {$t_att} SET notes = NULL, source_ip = NULL, token_hash = NULL WHERE id IN ($placeholders)";

                return (int) $wpdb->query( $wpdb->prepare( $sql, $ids ) );
        }

		/**
		 * Summarize attendance across applications.
		 *
		 * @since 0.1.x
		 *
		 * @param array<string,mixed> $args Arguments.
		 * @phpstan-param array{
		 *   range_from:string,
		 *   range_to:string,
		 *   form_id?:int,
		 *   event_id?:int,
		 *   status?:array<int,string>,
		 *   type?:array<int,string>,
		 *   manager_id?:int,
		 *   application_ids?:list<int>,
		 *   person_ids?:list<int>,
		 *   policy_only?:bool,
		 *   include_voided?:bool,
		 *   policy_days?:int,
		 *   page?:int,
		 *   per_page?:int,
		 *   orderby?:string,
		 *   order?:string
		 * } $args
		 *
		 * @return array{
		 *   rows:list<array{
		 *     application_id:int,
		 *     last_attended: string|null,
		 *     visits_range:int,
		 *     noshows_range:int,
		 *     visits_12m:int,
		 *     policy_breach:int
		 *   }>,
		 *   total:int
		 * }
		 */
        public static function people_summary( array $args ): array {
			global $wpdb;
			$self  = new self();
			$t_att = $wpdb->prefix . 'fb_attendance';
			$t_app = $wpdb->prefix . 'fb_applications';

			$range_from = sanitize_text_field( $args['range_from'] );
			$range_to   = sanitize_text_field( $args['range_to'] );
		if ( 1 !== preg_match( '/^\d{4}-\d{2}-\d{2}$/', $range_from ) || 1 !== preg_match( '/^\d{4}-\d{2}-\d{2}$/', $range_to ) ) {
				return array(
					'rows'  => array(),
					'total' => 0,
				);
		}
			$rf = $range_from . ' 00:00:00';
			$rt = $range_to . ' 23:59:59';

			$policy_days    = absint( $args['policy_days'] ?? Options::get( 'attendance.policy_days' ) );
			$include_voided = ! empty( $args['include_voided'] );

			$clauses    = array( 't.attendance_at BETWEEN %s AND %s' );
			$where_args = array( $rf, $rt );

		if ( ! $include_voided ) {
				$clauses[] = 't.is_void = 0';
		}

			$form_id = absint( $args['form_id'] ?? 0 );
		if ( $form_id ) {
				$clauses[]    = 'a.form_id = %d';
				$where_args[] = $form_id;
		}

			$event_id = absint( $args['event_id'] ?? 0 );
		if ( $event_id ) {
				$clauses[]    = 't.event_id = %d';
				$where_args[] = $event_id;
		}

			$statuses = array_values( array_filter( array_map( 'sanitize_text_field', (array) ( $args['status'] ?? array() ) ) ) );
		if ( isset( $args['status'] ) && empty( $statuses ) ) {
				return array(
					'rows'  => array(),
					'total' => 0,
				);
		}
		if ( $statuses ) {
				$placeholders = implode( ', ', array_fill( 0, count( $statuses ), '%s' ) );
				$clauses[]    = "t.status IN ($placeholders)";
				$where_args   = array_merge( $where_args, $statuses );
		}

			$types = array_values( array_filter( array_map( 'sanitize_text_field', (array) ( $args['type'] ?? array() ) ) ) );
		if ( isset( $args['type'] ) && empty( $types ) ) {
				return array(
					'rows'  => array(),
					'total' => 0,
				);
		}
		if ( $types ) {
				$placeholders = implode( ', ', array_fill( 0, count( $types ), '%s' ) );
				$clauses[]    = "t.type IN ($placeholders)";
				$where_args   = array_merge( $where_args, $types );
		}

			$manager_id = absint( $args['manager_id'] ?? 0 );
		if ( $manager_id ) {
				$clauses[]    = 't.recorded_by_user_id = %d';
				$where_args[] = $manager_id;
		}

			$app_ids = array_values( array_filter( array_map( 'absint', (array) ( $args['application_ids'] ?? array() ) ) ) );
		if ( isset( $args['application_ids'] ) && empty( $app_ids ) ) {
				return array(
					'rows'  => array(),
					'total' => 0,
				);
		}
		if ( $app_ids ) {
				$placeholders = implode( ', ', array_fill( 0, count( $app_ids ), '%d' ) );
				$clauses[]    = "t.application_id IN ($placeholders)";
				$where_args   = array_merge( $where_args, $app_ids );
		}

			$person_ids = array_values( array_filter( array_map( 'absint', (array) ( $args['person_ids'] ?? array() ) ) ) );
		if ( isset( $args['person_ids'] ) && empty( $person_ids ) ) {
				return array(
					'rows'  => array(),
					'total' => 0,
				);
		}
		if ( $person_ids ) {
				$placeholders = implode( ', ', array_fill( 0, count( $person_ids ), '%d' ) );
				$clauses[]    = "a.person_id IN ($placeholders)";
				$where_args   = array_merge( $where_args, $person_ids );
		}

			$where_sql = $self->fbm_sql_where( $clauses );
			$having    = ! empty( $args['policy_only'] ) ? ' HAVING policy_breach = 1' : '';

			$order_map = array(
				'created_at' => 'a.created_at',
				'status'     => 'a.status',
				'person_id'  => 'a.person_id',
				'event_id'   => 't.event_id',
				'last_seen'  => 'last_attended',
			);
			$requested = (string) ( $args['orderby'] ?? '' );
			$order_by  = $order_map[ $requested ] ?? 'a.created_at';
			$order     = 'ASC' === strtoupper( $args['order'] ?? '' ) ? 'ASC' : 'DESC';
			$order_sql = " ORDER BY {$order_by} {$order}";

			$limit     = min( 500, max( 1, absint( $args['per_page'] ?? 25 ) ) );
			$page      = max( 1, absint( $args['page'] ?? 1 ) );
			$offset    = max( 0, ( $page - 1 ) * $limit );
			$limit_sql = $wpdb->prepare( ' LIMIT %d OFFSET %d', $limit, $offset );

			$base_sql = "
SELECT
  a.id AS application_id,
  MAX(CASE WHEN t.status='present' THEN t.attendance_at END)                                                 AS last_attended,
  SUM(CASE WHEN t.status='present' AND t.attendance_at BETWEEN %s AND %s THEN 1 ELSE 0 END)                  AS visits_range,
  SUM(CASE WHEN t.status='no_show'  AND t.attendance_at BETWEEN %s AND %s THEN 1 ELSE 0 END)                 AS noshows_range,
  SUM(CASE WHEN t.status='present' AND t.attendance_at >= DATE_SUB(%s, INTERVAL 12 MONTH) THEN 1 ELSE 0 END) AS visits_12m,
  MAX(
    EXISTS(
      SELECT 1 FROM {$t_att} t2
      WHERE t2.application_id = t.application_id
        AND t2.status='present'
        AND t2.attendance_at > DATE_SUB(t.attendance_at, INTERVAL %d DAY)
        AND t2.attendance_at <  t.attendance_at
    )
  ) AS policy_breach
FROM {$t_att} t
JOIN {$t_app} a ON a.id = t.application_id
$where_sql
GROUP BY t.application_id{$having}";

			$select_args = array( $rf, $rt, $rf, $rt, $rt, $policy_days );
			$query_args  = array_merge( $select_args, $where_args );
			$prepared    = call_user_func_array(
				array( $wpdb, 'prepare' ),
				array_merge( array( $base_sql . $order_sql . $limit_sql ), $query_args )
			);
		$rows            = call_user_func( array( $wpdb, 'get_results' ), $prepared, 'ARRAY_A' );

		if ( ! empty( $args['policy_only'] ) ) {
				$count_base = "
SELECT COUNT(*) FROM (
  SELECT t.application_id,
         MAX(
           EXISTS(
             SELECT 1 FROM {$t_att} t2
             WHERE t2.application_id = t.application_id
               AND t2.status='present'
               AND t2.attendance_at > DATE_SUB(t.attendance_at, INTERVAL %d DAY)
               AND t2.attendance_at <  t.attendance_at
           )
         ) AS policy_breach
  FROM {$t_att} t
  JOIN {$t_app} a ON a.id = t.application_id
  $where_sql
  GROUP BY t.application_id
  HAVING policy_breach = 1
) c";
				$count_args = array_merge( array( $policy_days ), $where_args );
		} else {
				$count_base = "SELECT COUNT(DISTINCT t.application_id) FROM {$t_att} t JOIN {$t_app} a ON a.id = t.application_id $where_sql";
				$count_args = $where_args;
		}

		$prepared_count = call_user_func_array(
			array( $wpdb, 'prepare' ),
			array_merge( array( $count_base ), $count_args )
		);
		$total          = (int) call_user_func( array( $wpdb, 'get_var' ), $prepared_count );

			$rows = $rows ? array_values( $rows ) : array();

                        return array(
                                'rows'  => $rows,
                                'total' => $total,
                        );
        }

        /**
         * @deprecated 1.2.16 Use people_summary() instead.
         * @codeCoverageIgnore
         *
         * @param array<string,mixed> $args Arguments.
         * @return array{rows:list<array{application_id:int,last_attended:string|null,visits_range:int,noshows_range:int,visits_12m:int,policy_breach:int}>,total:int}
         */
        public static function peopleSummary( array $args ): array {
                return self::people_summary( $args );
        }

		/**
		 * Retrieve attendance timeline rows for an application.
		 *
		 * @since 0.1.x
		 *
		 * @param int    $application_id Application ID.
		 * @param string $from           Optional UTC start 'Y-m-d'.
		 * @param string $to             Optional UTC end 'Y-m-d'.
		 * @param bool   $include_voided Include voided rows.
		 * @return list<array{
		 *   id:int,
		 *   status:string,
		 *   attendance_at:string,
		 *   event_id:int,
		 *   type:string|null,
		 *   method:string|null,
		 *   recorded_by_user_id:int,
		 *   is_void:int,
		 *   void_reason:string|null,
		 *   void_by_user_id:int|null,
		 *   void_at:string|null,
		 *   notes:list<array{
		 *     attendance_id:int,
		 *     user_id:int,
		 *     note_text:string,
		 *     created_at:string
		 *   }>
		 * }>
		 */
	public static function timeline( int $application_id, string $from, string $to, bool $include_voided = false ): array {
		global $wpdb;
		$application_id = absint( $application_id );
		$from           = sanitize_text_field( $from );
		$to             = sanitize_text_field( $to );

		$t_att   = $wpdb->prefix . 'fb_attendance';
		$t_notes = $wpdb->prefix . 'fb_attendance_notes';

		$clauses = array( 't.application_id = %d' );
		$args    = array( $application_id );
		if ( '' !== $from && 1 === preg_match( '/^\d{4}-\d{2}-\d{2}$/', $from ) ) {
			$clauses[] = 't.attendance_at >= %s';
			$args[]    = $from . ' 00:00:00';
		}
		if ( '' !== $to && 1 === preg_match( '/^\d{4}-\d{2}-\d{2}$/', $to ) ) {
			$clauses[] = 't.attendance_at <= %s';
			$args[]    = $to . ' 23:59:59';
		}
		if ( ! $include_voided ) {
			$clauses[] = 't.is_void = 0';
		}
                $self      = new self();
                $where_sql = $self->fbm_sql_where( $clauses );
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table and WHERE clause are constants or prepared.
                $sql       = "SELECT t.id, t.status, t.attendance_at, t.event_id, t.type, t.method,
                       t.recorded_by_user_id, t.is_void, t.void_reason,
                       t.void_by_user_id, t.void_at
                FROM {$t_att} t
                {$where_sql}
                ORDER BY t.attendance_at ASC";
                $rows      = $wpdb->get_results( $wpdb->prepare( $sql, $args ), 'ARRAY_A' );

		if ( empty( $rows ) ) {
			return array();
		}

		$ids = array_values( array_filter( array_map( 'absint', array_column( $rows, 'id' ) ) ) );
		if ( empty( $ids ) ) {
			return array();
		}

                $placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
                $note_args    = $ids;
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name and placeholders are controlled.
                $note_sql     = "SELECT attendance_id, user_id, note_text, created_at
FROM {$t_notes}
WHERE attendance_id IN ($placeholders)
ORDER BY created_at ASC";
                $note_rows    = $wpdb->get_results( $wpdb->prepare( $note_sql, $note_args ), ARRAY_A );
                $note_rows    = $note_rows ? $note_rows : array();

		$grouped = array();
		foreach ( $note_rows as $n ) {
			$grouped[ (int) $n['attendance_id'] ][] = $n;
		}
		foreach ( $rows as &$r ) {
				$r['notes'] = $grouped[ (int) $r['id'] ] ?? array();
		}
			unset( $r );

			return array_values( $rows );
	}

	/**
	 * Toggle the void flag for an attendance entry.
	 *
	 * @since 0.1.x
	 *
	 * @param int         $attendance_id Attendance ID.
	 * @param bool        $voided        Whether to void.
	 * @param string|null $reason        Optional void reason.
	 * @param int         $actor_id      Acting user ID.
	 * @param string      $now_utc       Current UTC datetime 'Y-m-d H:i:s'.
	 * @return bool True on success, false on failure.
	 */
	public static function set_void( int $attendance_id, bool $voided, ?string $reason, int $actor_id, string $now_utc ): bool {
		global $wpdb;
		$attendance_id = absint( $attendance_id );
		$actor_id      = absint( $actor_id );
		$now_utc       = sanitize_text_field( $now_utc );
		if ( null !== $reason ) {
			$reason = sanitize_text_field( $reason );
		}

		if ( $voided ) {
			$data = array(
				'is_void'         => 1,
				'void_reason'     => $reason,
				'void_by_user_id' => $actor_id,
				'void_at'         => $now_utc,
			);
		} else {
			$data = array(
				'is_void'         => 0,
				'void_reason'     => null,
				'void_by_user_id' => null,
				'void_at'         => null,
			);
		}

			$updated = $wpdb->update(
				$wpdb->prefix . 'fb_attendance',
				$data,
				array( 'id' => $attendance_id ),
				array( '%d', '%s', '%d', '%s' ),
				array( '%d' )
			);
		return false !== $updated;
	}

	/**
	 * Add a note to an attendance entry.
	 *
	 * @since 0.1.x
	 *
	 * @param int    $attendance_id Attendance ID.
	 * @param int    $user_id       User ID.
	 * @param string $note          Note text.
	 * @param string $now_utc       Current UTC datetime 'Y-m-d H:i:s'.
	 * @return bool True on success, false on failure.
	 */
	public static function add_note( int $attendance_id, int $user_id, string $note, string $now_utc ): bool {
		global $wpdb;
		$attendance_id = absint( $attendance_id );
		$user_id       = absint( $user_id );
		$note          = sanitize_textarea_field( $note );
		$now_utc       = sanitize_text_field( $now_utc );

		$inserted = $wpdb->insert(
			$wpdb->prefix . 'fb_attendance_notes',
			array(
				'attendance_id' => $attendance_id,
				'user_id'       => $user_id,
				'note_text'     => $note,
				'created_at'    => $now_utc,
			),
			array( '%d', '%d', '%s', '%s' )
		);
		return false !== $inserted;
	}

	/**
	 * Count present check-ins since a date.
	 *
	 * @param string              $since   UTC datetime.
	 * @param array<string,mixed> $filters Optional filters.
	 * @return int
	 */
	public static function count_present( string $since, array $filters = array() ): int {
			global $wpdb;
			$since                            = sanitize_text_field( $since );
			$t_att                            = $wpdb->prefix . 'fb_attendance';
			list( $filter_sql, $filter_args ) = self::build_filter_clauses( $filters );
			$prepared                         = call_user_func_array(
				array( $wpdb, 'prepare' ),
				array_merge( array( "SELECT COUNT(*) FROM {$t_att} WHERE status = 'present' AND attendance_at >= %s{$filter_sql}", $since ), $filter_args )
			);
			return (int) call_user_func( array( $wpdb, 'get_var' ), $prepared );
	}

	/**
	 * Count unique households served since date.
	 *
	 * @param string              $since   UTC datetime.
	 * @param array<string,mixed> $filters Optional filters.
	 * @return int
	 */
	public static function count_unique_households( string $since, array $filters = array() ): int {
					global $wpdb;
					$since                            = sanitize_text_field( $since );
					$t_att                            = $wpdb->prefix . 'fb_attendance';
					list( $filter_sql, $filter_args ) = self::build_filter_clauses( $filters );
										$sql          = "SELECT COUNT(DISTINCT application_id) FROM {$t_att} WHERE status = 'present'"
																				. " AND attendance_at >= %s{$filter_sql}";
					$prepared                         = call_user_func_array(
						array( $wpdb, 'prepare' ),
						array_merge(
							array( $sql, $since ),
							$filter_args,
						)
					);
					return (int) call_user_func( array( $wpdb, 'get_var' ), $prepared );
	}

	/**
	 * Count no-shows since date.
	 *
	 * @param string              $since   UTC datetime.
	 * @param array<string,mixed> $filters Optional filters.
	 * @return int
	 */
	public static function count_no_shows( string $since, array $filters = array() ): int {
					global $wpdb;
					$since                            = sanitize_text_field( $since );
					$t_att                            = $wpdb->prefix . 'fb_attendance';
					list( $filter_sql, $filter_args ) = self::build_filter_clauses( $filters );
					$sql                              = "SELECT COUNT(*) FROM {$t_att} WHERE status = 'no_show' AND attendance_at >= %s{$filter_sql}";
					$prepared                         = call_user_func_array(
						array( $wpdb, 'prepare' ),
						array_merge(
							array( $sql, $since ),
							$filter_args,
						)
					);
					return (int) call_user_func( array( $wpdb, 'get_var' ), $prepared );
	}

	/**
	 * Count attendance by type since date.
	 *
	 * @param string              $since   UTC datetime.
	 * @param array<string,mixed> $filters Optional filters.
	 * @return array{in_person:int,delivery:int}
	 */
	public static function count_by_type( string $since, array $filters = array() ): array {
					global $wpdb;
					$since                            = sanitize_text_field( $since );
					$t_att                            = $wpdb->prefix . 'fb_attendance';
					list( $filter_sql, $filter_args ) = self::build_filter_clauses( $filters );
										$sql          = "SELECT type, COUNT(*) as c FROM {$t_att} WHERE status = 'present'"
																				. " AND attendance_at >= %s{$filter_sql} GROUP BY type";
					$prepared                         = call_user_func_array(
						array( $wpdb, 'prepare' ),
						array_merge(
							array( $sql, $since ),
							$filter_args,
						)
					);
			$rows                                     = call_user_func( array( $wpdb, 'get_results' ), $prepared );
		if ( ! is_array( $rows ) ) {
			$rows = array();
		}
		$out = array(
			'in_person' => 0,
			'delivery'  => 0,
		);
		foreach ( $rows as $row ) {
			$type         = sanitize_key( (string) $row->type );
			$out[ $type ] = (int) $row->c;
		}
		return $out;
	}

	/**
	 * Count voided records since date.
	 *
	 * @param string              $since   UTC datetime.
	 * @param array<string,mixed> $filters Optional filters.
	 * @return int
	 */
	public static function count_voided( string $since, array $filters = array() ): int {
					global $wpdb;
					$since                            = sanitize_text_field( $since );
					$t_att                            = $wpdb->prefix . 'fb_attendance';
					list( $filter_sql, $filter_args ) = self::build_filter_clauses( $filters );
					$sql                              = "SELECT COUNT(*) FROM {$t_att} WHERE is_void = 1 AND attendance_at >= %s{$filter_sql}";
					$prepared                         = call_user_func_array(
						array( $wpdb, 'prepare' ),
						array_merge(
							array( $sql, $since ),
							$filter_args,
						)
					);
					return (int) call_user_func( array( $wpdb, 'get_var' ), $prepared );
	}

				/**
				 * Get daily present counts since a date.
				 *
				 * @param DateTimeImmutable   $since   Start date/time (UTC).
				 * @param array<string,mixed> $filters Optional filters.
				 * @return array<int,int> One value per day (or hour for today).
				 */
	public static function daily_present_counts( DateTimeImmutable $since, array $filters = array() ): array {
			global $wpdb;
			$t_att                            = $wpdb->prefix . 'fb_attendance';
			$since_str                        = sanitize_text_field( $since->format( 'Y-m-d H:i:s' ) );
			$today                            = gmdate( 'Y-m-d' );
			list( $filter_sql, $filter_args ) = self::build_filter_clauses( $filters );

		if ( $since->format( 'Y-m-d' ) === $today ) {
				$sql      = "SELECT DATE_FORMAT(attendance_at,'%H') h, COUNT(*) c FROM {$t_att} WHERE status = 'present'"
						. " AND attendance_at >= %s{$filter_sql} GROUP BY h";
				$prepared = call_user_func_array(
					array( $wpdb, 'prepare' ),
					array_merge(
						array( $sql, $since_str ),
						$filter_args,
					),
				);
				$rows     = call_user_func( array( $wpdb, 'get_results' ), $prepared );
				$out      = array_fill( 0, 24, 0 );
			if ( is_array( $rows ) ) {
				foreach ( $rows as $row ) {
						$h = (int) $row->h;
					if ( $h >= 0 && $h < 24 ) {
						$out[ $h ] = (int) $row->c;
					}
				}
			}
				return $out;
		}

			$sql       = "SELECT DATE(attendance_at) d, COUNT(*) c FROM {$t_att} WHERE status = 'present'"
					. " AND attendance_at >= %s{$filter_sql} GROUP BY d";
			$prepared  = call_user_func_array(
				array( $wpdb, 'prepare' ),
				array_merge(
					array( $sql, $since_str ),
					$filter_args,
				),
			);
			$rows      = call_user_func( array( $wpdb, 'get_results' ), $prepared );
			$now       = new DateTimeImmutable( 'today', new \DateTimeZone( 'UTC' ) );
			$days      = $now->diff( $since )->days;
			$len       = $days + 1;
			$out       = array_fill( 0, (int) $len, 0 );
                        $since_day = strtotime( $since->format( 'Y-m-d' ) );
                if ( is_array( $rows ) ) {
                        foreach ( $rows as $row ) {
                                $d   = sanitize_text_field( (string) $row->d );
                                $idx = (int) floor( ( strtotime( $d ) - $since_day ) / 86400 );
                                if ( $idx >= 0 && $idx < $len ) {
                                                $out[ $idx ] = (int) $row->c;
                                }
                        }
                }
                        return $out;
        }

        /**
         * Get daily counts since a date.
         *
         * @param DateTimeImmutable   $since   Start date/time (UTC).
         * @param array<string,mixed> $filters Optional filters.
         * @return array<int,int> One value per day (or hour for today).
         */
        public static function daily_counts( DateTimeImmutable $since, array $filters = array() ): array {
                return self::daily_present_counts( $since, $filters );
        }

        /**
         * @deprecated 1.2.16 Use daily_counts() instead.
         * @codeCoverageIgnore
         *
         * @param DateTimeImmutable   $since   Start date/time (UTC).
         * @param array<string,mixed> $filters Optional filters.
         * @return array<int,int> One value per day (or hour for today).
         */
        public static function getDailyCounts( DateTimeImmutable $since, array $filters = array() ): array {
                return self::daily_counts( $since, $filters );
        }

				/**
				 * Get totals for the period since a date.
				 *
				 * @param DateTimeImmutable   $since   Start date/time (UTC).
				 * @param array<string,mixed> $filters Optional filters.
				 * @return array{present:int,households:int,no_shows:int,in_person:int,delivery:int,voided:int}
				 */
        public static function period_totals( DateTimeImmutable $since, array $filters = array() ): array {
                                        $since_str     = sanitize_text_field( $since->format( 'Y-m-d H:i:s' ) );
                                                        $types = self::count_by_type( $since_str, $filters );
                                                        return array(
                                                                'present'    => self::count_present( $since_str, $filters ),
                                                                'households' => self::count_unique_households( $since_str, $filters ),
                                                                'no_shows'   => self::count_no_shows( $since_str, $filters ),
                                                                'in_person'  => (int) $types['in_person'],
                                                                'delivery'   => (int) $types['delivery'],
                                                                'voided'     => self::count_voided( $since_str, $filters ),
                                                        );
        }

        /**
         * Get totals for the period since a date.
         *
         * @param DateTimeImmutable   $since   Start date/time (UTC).
         * @param array<string,mixed> $filters Optional filters.
         * @return array{present:int,households:int,no_shows:int,in_person:int,delivery:int,voided:int}
         */
        public static function counts( DateTimeImmutable $since, array $filters = array() ): array {
                return self::period_totals( $since, $filters );
        }

        /**
         * @deprecated 1.2.16 Use counts() instead.
         * @codeCoverageIgnore
         *
         * @param DateTimeImmutable   $since   Start date/time (UTC).
         * @param array<string,mixed> $filters Optional filters.
         * @return array{present:int,households:int,no_shows:int,in_person:int,delivery:int,voided:int}
         */
        public static function getCounts( DateTimeImmutable $since, array $filters = array() ): array {
                return self::counts( $since, $filters );
        }

        /**
         * Prepare filter SQL and arguments.
         *
         * @param array<string,mixed> $filters Filters.
         * @return array{0:string,1:array}
         */
        public static function filters_prepared( array $filters ): array {
                return self::build_filter_clauses( $filters );
        }

        /**
         * @deprecated 1.2.16 Use filters_prepared() instead.
         * @codeCoverageIgnore
         *
         * @param array<string,mixed> $filters Filters.
         * @return array{0:string,1:array}
         */
        public static function filtersPrepared( array $filters ): array {
                return self::filters_prepared( $filters );
        }

	/**
	 * Join WHERE clauses.
	 *
	 * @param array $clauses Clauses.
	 * @return string WHERE clause.
	 */
        private function fbm_sql_where( array $clauses ): string {
			return $clauses ? 'WHERE ' . implode( ' AND ', $clauses ) : '';
	}

		/**
		 * Build optional filter SQL and args.
		 *
		 * @param array $filters Filters.
		 * @return array{0:string,1:array}
		 */
	private static function build_filter_clauses( array $filters ): array {
			$clauses = array();
			$args    = array();

			$event = $filters['event'] ?? null;
		if ( null !== $event && '' !== $event ) {
				$event = sanitize_text_field( (string) $event );
			if ( ctype_digit( $event ) ) {
				$clauses[] = 'event_id = %d';
				$args[]    = (int) $event;
			} else {
					$clauses[] = 'event_id = %s';
					$args[]    = $event;
			}
		}

			$type = sanitize_key( (string) ( $filters['type'] ?? '' ) );
		if ( in_array( $type, array( 'in_person', 'delivery' ), true ) ) {
				$clauses[] = 'type = %s';
				$args[]    = $type;
		}

		if ( ! empty( $filters['policy_only'] ) ) {
				$clauses[] = 'policy_breach = 1';
		}

			$sql = $clauses ? ' AND ' . implode( ' AND ', $clauses ) : '';
			return array( $sql, $args );
	}
}
