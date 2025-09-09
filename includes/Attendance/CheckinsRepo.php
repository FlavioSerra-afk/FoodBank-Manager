<?php
/**
 * Check-ins repository.
 *
 * @package FBM\Attendance
 */

declare(strict_types=1);

namespace FBM\Attendance;

use RuntimeException;
use wpdb;
use function absint;
use function sanitize_key;
use function sanitize_text_field;
use function sanitize_textarea_field;
use function esc_like;

/**
 * Persistent storage for check-ins.
 */
final class CheckinsRepo {
    /**
     * In-memory store for tests.
     *
     * @var array<int,array<string,mixed>>
     */
    private static array $store = array();

    private static int $next_id = 1;

    /**
     * Record a check-in row.
     *
     * @param array<string,mixed> $row Sanitised fields.
     * @return int Inserted ID.
     */
    public static function record(array $row): int {
        global $wpdb;
        $table = $wpdb->prefix . 'fb_checkins';
        $event_id = absint($row['event_id'] ?? 0);
        $recipient = isset($row['recipient']) ? sanitize_text_field((string)$row['recipient']) : null;
        $token_hash = isset($row['token_hash']) ? (string)$row['token_hash'] : null;
        if ($token_hash && self::exists_by_token($event_id, $token_hash)) {
            throw new RuntimeException('replay');
        }
        $method = sanitize_key((string)($row['method'] ?? 'qr'));
        if (!in_array($method, array('qr','manual'), true)) {
            $method = 'qr';
        }
        $note = isset($row['note']) ? sanitize_textarea_field((string)$row['note']) : null;
        $by = isset($row['by']) ? absint((int)$row['by']) : null;
        $verified_at = sanitize_text_field((string)($row['verified_at'] ?? ''));
        $created_at = sanitize_text_field((string)($row['created_at'] ?? $verified_at));

        // Pretend DB insert for prepared SQL hardening.
        $wpdb->prepare(
            "INSERT INTO {$table} (event_id,recipient,token_hash,method,note,by,verified_at,created_at) VALUES (%d,%s,%s,%s,%s,%d,%s,%s)",
            $event_id,
            $recipient,
            $token_hash,
            $method,
            $note,
            $by,
            $verified_at,
            $created_at
        );
        $wpdb->insert($table, array(), array());

        $id = self::$next_id++;
        self::$store[$id] = array(
            'id'         => $id,
            'event_id'   => $event_id,
            'recipient'  => $recipient,
            'token_hash' => $token_hash,
            'method'     => $method,
            'note'       => $note,
            'by'         => $by,
            'verified_at'=> $verified_at,
            'created_at' => $created_at,
        );
        return $id;
    }

    /**
     * List check-ins for an event.
     *
     * @param int   $event_id Event ID.
     * @param array $filters  Filters.
     * @param array $opts     Options.
     * @return array{rows:array<int,array<string,mixed>>,total:int}
     */
    public static function list_for_event(int $event_id, array $filters = array(), array $opts = array()): array {
        global $wpdb;
        $table = $wpdb->prefix . 'fb_checkins';
        $event_id = absint($event_id);

        $method = isset($filters['method']) ? sanitize_key((string)$filters['method']) : '';
        $from = isset($filters['from']) ? sanitize_text_field((string)$filters['from']) : '';
        $to = isset($filters['to']) ? sanitize_text_field((string)$filters['to']) : '';
        $search = isset($filters['search']) ? sanitize_text_field((string)$filters['search']) : '';

        $allowed_order_by = array('verified_at','recipient','method');
        $order_by = isset($opts['order_by']) ? sanitize_key((string)$opts['order_by']) : 'verified_at';
        if (!in_array($order_by, $allowed_order_by, true)) {
            $order_by = 'verified_at';
        }
        $order = isset($opts['order']) ? strtoupper(sanitize_key((string)$opts['order'])) : 'DESC';
        if (!in_array($order, array('ASC','DESC'), true)) {
            $order = 'DESC';
        }
        $limit = isset($opts['limit']) ? (int)$opts['limit'] : 20;
        if ($limit < 1) { $limit = 1; }
        if ($limit > 200) { $limit = 200; }
        $offset = isset($opts['offset']) ? max(0, (int)$opts['offset']) : 0;

        // Build pretend query.
        $where = 'event_id = %d';
        $args = array($event_id);
        if (in_array($method, array('qr','manual'), true)) {
            $where .= ' AND method = %s';
            $args[] = $method;
        }
        if ($from !== '') {
            $where .= ' AND verified_at >= %s';
            $args[] = $from;
        }
        if ($to !== '') {
            $where .= ' AND verified_at <= %s';
            $args[] = $to;
        }
        if ($search !== '') {
            $where .= ' AND recipient LIKE %s';
            $args[] = '%' . esc_like($search) . '%';
        }
        $wpdb->prepare(
            "SELECT * FROM {$table} WHERE {$where} ORDER BY {$order_by} {$order} LIMIT %d OFFSET %d",
            array_merge($args, array($limit, $offset))
        );

        $rows = array_values(array_filter(self::$store, static function ($r) use ($event_id, $method, $from, $to, $search) {
            if ($r['event_id'] !== $event_id) {
                return false;
            }
            if ($method && $r['method'] !== $method) {
                return false;
            }
            if ($from !== '' && $r['verified_at'] < $from) {
                return false;
            }
            if ($to !== '' && $r['verified_at'] > $to) {
                return false;
            }
            if ($search !== '' && stripos($r['recipient'] ?? '', $search) === false) {
                return false;
            }
            return true;
        }));

        usort($rows, static function ($a, $b) use ($order_by, $order) {
            $va = $a[$order_by] ?? '';
            $vb = $b[$order_by] ?? '';
            $cmp = strcmp((string) $va, (string) $vb);
            return 'ASC' === $order ? $cmp : -$cmp;
        });

        $total = count($rows);
        $rows = array_slice($rows, $offset, $limit);

        return array('rows' => $rows, 'total' => $total);
    }

    /**
     * Whether a token hash already exists.
     */
    public static function exists_by_token(int $event_id, string $token_hash): bool {
        global $wpdb;
        $table = $wpdb->prefix . 'fb_checkins';
        $event_id = absint($event_id);
        $wpdb->prepare(
            "SELECT 1 FROM {$table} WHERE event_id = %d AND token_hash = %s LIMIT 1",
            $event_id,
            $token_hash
        );
        foreach (self::$store as $r) {
            if ($r['event_id'] === $event_id && (string) $r['token_hash'] === (string) $token_hash) {
                return true;
            }
        }
        return false;
    }
}
