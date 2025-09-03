<?php
/**
 * Attendance repository.
 *
 * @package FoodBankManager\Attendance
 */

declare(strict_types=1);

namespace FoodBankManager\Attendance;

use FoodBankManager\Core\Options;
use wpdb;
use function absint;
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
		$last  = $wpdb->get_var(
			$wpdb->prepare(
// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- table name is constant and placeholders match.
				"SELECT attendance_at FROM {$t_att} WHERE application_id = %d AND status = 'present' ORDER BY attendance_at DESC LIMIT 1",
				$application_id
			)
		);
		return $last ? $last : null;
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

		$where  = array( '1=1' );
		$params = array();

		if ( ! $include_voided ) {
			$where[] = 't.is_void = 0';
		}

		$form_id = absint( $args['form_id'] ?? 0 );
		if ( $form_id ) {
			$where[]  = 'a.form_id = %d';
			$params[] = $form_id;
		}

		$event_id = absint( $args['event_id'] ?? 0 );
		if ( $event_id ) {
			$where[]  = 't.event_id = %d';
			$params[] = $event_id;
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
				$where[]      = "t.status IN ($placeholders)";
				$params       = array_merge( $params, $statuses );
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
				$where[]      = "t.type IN ($placeholders)";
				$params       = array_merge( $params, $types );
		}

		$manager_id = absint( $args['manager_id'] ?? 0 );
		if ( $manager_id ) {
			$where[]  = 't.recorded_by_user_id = %d';
			$params[] = $manager_id;
		}

			$where_sql = implode( ' AND ', $where );
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

							$limit = min( 500, max( 1, absint( $args['per_page'] ?? 25 ) ) );
			$page                  = max( 1, absint( $args['page'] ?? 1 ) );
			$offset                = max( 0, ( $page - 1 ) * $limit );
			$limit_sql             = $wpdb->prepare( ' LIMIT %d OFFSET %d', $limit, $offset );

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
WHERE {$where_sql}
GROUP BY t.application_id{$having}";

			$rows = $wpdb->get_results(
// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- placeholders strictly match params length.
				$wpdb->prepare( $base_sql . $order_sql . $limit_sql, array_merge( array( $rf, $rt, $rf, $rt, $rt, $policy_days ), $params ) ),
				'ARRAY_A'
			);

		if ( ! empty( $args['policy_only'] ) ) {
			$count_base   = "
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
  WHERE {$where_sql}
  GROUP BY t.application_id
  HAVING policy_breach = 1
) c";
			$count_params = array_merge( array( $policy_days ), $params );
		} else {
			$count_base   = "SELECT COUNT(DISTINCT t.application_id) FROM {$t_att} t JOIN {$t_app} a ON a.id = t.application_id WHERE {$where_sql}";
			$count_params = $params;
		}

		$total = (int) $wpdb->get_var(
// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- placeholders strictly match params length.
			$wpdb->prepare( $count_base, $count_params )
		);

							$rows = $rows ? array_values( $rows ) : array();

							return array(
								'rows'  => $rows,
								'total' => $total,
							);
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

		$where  = array( 't.application_id = %d' );
		$params = array( $application_id );
		if ( '' !== $from && 1 === preg_match( '/^\d{4}-\d{2}-\d{2}$/', $from ) ) {
			$where[]  = 't.attendance_at >= %s';
			$params[] = $from . ' 00:00:00';
		}
		if ( '' !== $to && 1 === preg_match( '/^\d{4}-\d{2}-\d{2}$/', $to ) ) {
			$where[]  = 't.attendance_at <= %s';
			$params[] = $to . ' 23:59:59';
		}
		if ( ! $include_voided ) {
			$where[] = 't.is_void = 0';
		}
			$where_sql = implode( ' AND ', $where );

			$sql  = "
SELECT t.id, t.status, t.attendance_at, t.event_id, t.type, t.method,
       t.recorded_by_user_id, t.is_void, t.void_reason,
       t.void_by_user_id, t.void_at
FROM {$t_att} t
WHERE {$where_sql}
ORDER BY t.attendance_at ASC
";
			$rows = $wpdb->get_results(
				   // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name is constant and placeholders match params length.
				$wpdb->prepare( $sql, $params ),
				'ARRAY_A'
			);

		if ( empty( $rows ) ) {
			return array();
		}

		$ids = array_values( array_filter( array_map( 'absint', array_column( $rows, 'id' ) ) ) );
		if ( empty( $ids ) ) {
			return array();
		}

		$placeholders = implode( ', ', array_fill( 0, count( $ids ), '%d' ) );
		$sql          = "
SELECT attendance_id, user_id, note_text, created_at
FROM {$t_notes}
WHERE attendance_id IN ($placeholders)
ORDER BY created_at ASC
";
		$note_rows    = $wpdb->get_results(
// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name is constant and placeholders match count.
			$wpdb->prepare( $sql, $ids ),
			'ARRAY_A'
		);
		$note_rows = $note_rows ? $note_rows : array();

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
}
