<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Attendance;

use wpdb;
use FoodBankManager\Core\Options;

final class AttendanceRepo {
    /**
     * Fetch the last 'present' attendance timestamp for an application.
     */
    public static function lastPresent(int $application_id): ?string {
        global $wpdb;
        $sql  = $wpdb->prepare(
            "SELECT attendance_at FROM {$wpdb->prefix}fb_attendance WHERE application_id=%d AND status='present' ORDER BY attendance_at DESC LIMIT 1",
            $application_id
        );
        $last = $wpdb->get_var($sql);
        return $last ? (string) $last : null;
    }
    /**
     * @param array $args {
     *   @type string $range_from  UTC 'Y-m-d H:i:s'
     *   @type string $range_to    UTC 'Y-m-d H:i:s'
     *   @type int    $form_id     Optional
     *   @type int    $event_id    Optional
     *   @type array  $status      Optional ['present','no_show']
     *   @type array  $type        Optional ['in_person','delivery',...]
     *   @type int    $manager_id  Optional recorded_by_user_id
     *   @type bool   $policy_only Optional
     *   @type bool   $include_voided Optional defaults false
     *   @type int    $policy_days Frequency window (e.g., 7)
     *   @type int    $page        1-based
     *   @type int    $per_page    25/50/100
     *   @type string $orderby     'last_attended'|'visits_range'|'noshows_range'|'visits_12m'|'application_id'
     *   @type string $order       'ASC'|'DESC'
     * }
     *
     * @return array{rows:array<int,array>, total:int}
     */
    public static function peopleSummary(array $args): array {
        global $wpdb;
        $att = $wpdb->prefix . 'fb_attendance';
        $app = $wpdb->prefix . 'fb_applications';

        // required inputs
        $rf = $args['range_from'];
        $rt = $args['range_to'];
        $policyDays = (int)($args['policy_days'] ?? Options::get( 'attendance.policy_days' ));

        $where = ["1=1"];
        $params = [];
        if (empty($args['include_voided'])) {
            $where[] = 't.is_void = 0';
        }

        // Filter build (only indexed columns where possible)
        if (!empty($args['form_id']))  { $where[] = "a.form_id = %d";  $params[] = (int)$args['form_id']; }
        if (!empty($args['event_id'])) { $where[] = "t.event_id = %d"; $params[] = (int)$args['event_id']; }
        if (!empty($args['status']) && is_array($args['status'])) {
            $in = implode(',', array_fill(0, count($args['status']), '%s'));
            $where[] = "t.status IN ($in)";
            array_push($params, ...array_map('strval', $args['status']));
        }
        if (!empty($args['type']) && is_array($args['type'])) {
            $in = implode(',', array_fill(0, count($args['type']), '%s'));
            $where[] = "t.type IN ($in)";
            array_push($params, ...array_map('strval', $args['type']));
        }
        if (!empty($args['manager_id'])) { $where[] = "t.recorded_by_user_id = %d"; $params[] = (int)$args['manager_id']; }

        // Date window for the range-based counters
        // We still need all rows for last_attended/12m calc; no WHERE on time here except optional custom overall window
        // For very large tables you can add "t.attendance_at <= %s" to narrow scan.
        $whereSql = implode(' AND ', $where);
        $having   = !empty($args['policy_only']) ? 'HAVING policy_breach = 1' : '';

        // Sorting
        $orderby = in_array($args['orderby'] ?? '', ['last_attended','visits_range','noshows_range','visits_12m','application_id'], true)
            ? $args['orderby'] : 'last_attended';
        $order   = strtoupper($args['order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

        $limit  = max(1, (int)($args['per_page'] ?? 25));
        $page   = max(1, (int)($args['page'] ?? 1));
        $offset = ($page - 1) * $limit;

        // Main grouped query
        $sql = "
        SELECT
          a.id AS application_id,
          MAX(CASE WHEN t.status='present' THEN t.attendance_at END)                                                 AS last_attended,
          SUM(CASE WHEN t.status='present' AND t.attendance_at BETWEEN %s AND %s THEN 1 ELSE 0 END)                  AS visits_range,
          SUM(CASE WHEN t.status='no_show'  AND t.attendance_at BETWEEN %s AND %s THEN 1 ELSE 0 END)                 AS noshows_range,
          SUM(CASE WHEN t.status='present' AND t.attendance_at >= DATE_SUB(%s, INTERVAL 12 MONTH) THEN 1 ELSE 0 END) AS visits_12m,
          -- policy breach: any 'present' within policyDays before another 'present'
          MAX(
            EXISTS(
              SELECT 1 FROM {$att} t2
              WHERE t2.application_id = t.application_id
                AND t2.status='present'
                AND t2.attendance_at > DATE_SUB(t.attendance_at, INTERVAL %d DAY)
                AND t2.attendance_at <  t.attendance_at
            )
          ) AS policy_breach
        FROM {$att} t
        JOIN {$app} a ON a.id = t.application_id
        WHERE {$whereSql}
        GROUP BY t.application_id
        {$having}
        ORDER BY {$orderby} {$order}
        LIMIT %d OFFSET %d
        ";

        // Count
        if (!empty($args['policy_only'])) {
            $countSql = "
              SELECT COUNT(*) FROM (
                SELECT t.application_id,
                  MAX(
                    EXISTS(
                      SELECT 1 FROM {$att} t2
                      WHERE t2.application_id = t.application_id
                        AND t2.status='present'
                        AND t2.attendance_at > DATE_SUB(t.attendance_at, INTERVAL %d DAY)
                        AND t2.attendance_at <  t.attendance_at
                    )
                  ) AS policy_breach
                FROM {$att} t
                JOIN {$app} a ON a.id = t.application_id
                WHERE {$whereSql}
                GROUP BY t.application_id
                HAVING policy_breach = 1
              ) c
            ";
            $bindCount = [$policyDays];
        } else {
            $countSql = "
              SELECT COUNT(DISTINCT t.application_id)
              FROM {$att} t
              JOIN {$app} a ON a.id = t.application_id
              WHERE {$whereSql}
            ";
            $bindCount = [];
        }

        $bind = [
            $rf, $rt,  // visits_range
            $rf, $rt,  // noshows_range
            $rt,       // base for 12m
            $policyDays,
        ];

        // Merge user filters
        $allParams = array_merge($bind, $params, [$limit, $offset]);
        $allCount  = array_merge($bindCount, $params);

        $prepared = $wpdb->prepare($sql, $allParams);
        $rows     = $wpdb->get_results($prepared, 'ARRAY_A') ?: [];

        $preparedCount = $wpdb->prepare($countSql, $allCount);
        $total         = (int)$wpdb->get_var($preparedCount);

        return ['rows' => $rows, 'total' => $total];
    }

    public static function timeline(int $applicationId, string $from, string $to, bool $includeVoided=false): array {
        global $wpdb;
        $att   = $wpdb->prefix . 'fb_attendance';
        $notes = $wpdb->prefix . 'fb_attendance_notes';
        $where = ['t.application_id = %d'];
        $params = [$applicationId];
        if ($from !== '') { $where[] = 't.attendance_at >= %s'; $params[] = $from; }
        if ($to   !== '') { $where[] = 't.attendance_at <= %s'; $params[] = $to; }
        if (!$includeVoided) { $where[] = 't.is_void = 0'; }
        $whereSql = implode(' AND ', $where);
        $sql = "SELECT t.id,t.status,t.attendance_at,t.event_id,t.type,t.method,t.recorded_by_user_id,t.is_void,t.void_reason,t.void_by_user_id,t.void_at FROM {$att} t WHERE {$whereSql} ORDER BY t.attendance_at ASC";
        $rows = $wpdb->get_results($wpdb->prepare($sql, $params), 'ARRAY_A') ?: [];
        if (empty($rows)) { return []; }
        $ids = array_map('intval', array_column($rows, 'id'));
        $in  = implode(',', array_fill(0, count($ids), '%d'));
        $noteSql = "SELECT attendance_id,user_id,note_text,created_at FROM {$notes} WHERE attendance_id IN ($in) ORDER BY created_at ASC";
        $noteRows = $wpdb->get_results($wpdb->prepare($noteSql, $ids), 'ARRAY_A') ?: [];
        $grouped = [];
        foreach ($noteRows as $n) {
            $grouped[(int)$n['attendance_id']][] = $n;
        }
        foreach ($rows as &$r) {
            $r['notes'] = $grouped[(int)$r['id']] ?? [];
        }
        return $rows;
    }

    public static function setVoid(int $attendanceId, bool $void, ?string $reason, int $actorId, string $nowUtc): bool {
        global $wpdb;
        if ($void) {
            $data = [
                'is_void'        => 1,
                'void_reason'    => $reason,
                'void_by_user_id'=> $actorId,
                'void_at'        => $nowUtc,
            ];
        } else {
            $data = [
                'is_void'        => 0,
                'void_reason'    => null,
                'void_by_user_id'=> null,
                'void_at'        => null,
            ];
        }
        $updated = $wpdb->update(
            $wpdb->prefix . 'fb_attendance',
            $data,
            ['id' => $attendanceId],
            ['%d','%s','%d','%s'],
            ['%d']
        );
        return $updated !== false;
    }

    public static function addNote(int $attendanceId, int $userId, string $note, string $nowUtc): bool {
        global $wpdb;
        $inserted = $wpdb->insert(
            $wpdb->prefix . 'fb_attendance_notes',
            [
                'attendance_id' => $attendanceId,
                'user_id'       => $userId,
                'note_text'     => $note,
                'created_at'    => $nowUtc,
            ],
            ['%d','%d','%s','%s']
        );
        return $inserted !== false;
    }
}

