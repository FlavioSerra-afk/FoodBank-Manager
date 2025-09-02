<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Attendance;

use wpdb;

class AttendanceRepo {
    public static function peopleSummary(
        int $form_id,
        string $range_from,
        string $range_to,
        string $now_for_12m,
        int $policy_days,
        int $limit,
        int $offset
    ): array {
        global $wpdb;
        $sql = "SELECT
  a.id AS application_id,
  a.data_json,
  a.pii_encrypted_blob,
  MAX(CASE WHEN at.status='present' THEN at.attendance_at END) AS last_attended,
  SUM(CASE WHEN at.status='present' AND at.attendance_at BETWEEN %s AND %s THEN 1 ELSE 0 END) AS visits_range,
  SUM(CASE WHEN at.status='no_show' AND at.attendance_at BETWEEN %s AND %s THEN 1 ELSE 0 END) AS noshows_range,
  SUM(CASE WHEN at.status='present' AND at.attendance_at >= DATE_SUB(%s, INTERVAL 12 MONTH) THEN 1 ELSE 0 END) AS visits_12m,
  MAX(
    EXISTS(
      SELECT 1 FROM {$wpdb->prefix}fb_attendance at2
      WHERE at2.application_id = at.application_id
        AND at2.status='present'
        AND at2.attendance_at > DATE_SUB(at.attendance_at, INTERVAL %d DAY)
        AND at2.attendance_at <  at.attendance_at
    )
  ) AS policy_breach
FROM {$wpdb->prefix}fb_attendance at
JOIN {$wpdb->prefix}fb_applications a ON a.id = at.application_id
WHERE at.form_id = %d
GROUP BY at.application_id
ORDER BY last_attended DESC
LIMIT %d OFFSET %d";
        $prepared = $wpdb->prepare(
            $sql,
            $range_from,
            $range_to,
            $range_from,
            $range_to,
            $now_for_12m,
            $policy_days,
            $form_id,
            $limit,
            $offset
        );
        return $wpdb->get_results( $prepared, 'ARRAY_A' );
    }
}
