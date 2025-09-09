<?php
/**
 * Events repository.
 *
 * @package FoodBankManager\Attendance
 */

declare(strict_types=1);

namespace FBM\Attendance;

use wpdb;
use function absint;
use function esc_like;
use function sanitize_key;
use function sanitize_text_field;
use function sanitize_textarea_field;
use function apply_filters;
use function gmdate;

/**
 * Data access for events.
 */
final class EventsRepo {
    /**
     * Insert a new event.
     *
     * @param array $data Event data.
     * @return int Inserted ID.
     */
    public static function create(array $data): int {
        global $wpdb;
        $table = $wpdb->prefix . 'fb_events';

        $title     = sanitize_text_field(trim((string)($data['title'] ?? '')));
        $starts_at = sanitize_text_field((string)($data['starts_at'] ?? ''));
        $ends_at   = sanitize_text_field((string)($data['ends_at'] ?? ''));
        $location  = isset($data['location']) ? sanitize_text_field((string)$data['location']) : '';
        $capacity  = isset($data['capacity']) ? max(0, (int)$data['capacity']) : null;
        $notes     = isset($data['notes']) ? sanitize_textarea_field((string)$data['notes']) : '';
        $status    = sanitize_key((string)($data['status'] ?? 'active'));
        if (!in_array($status, array('active', 'cancelled'), true)) {
            $status = 'active';
        }

        if ($ends_at < $starts_at) {
            $ends_at = $starts_at;
        }

        $now = (int)apply_filters('fbm_now', time());
        $now = gmdate('Y-m-d H:i:s', $now);

        $row = array(
            'title'     => $title,
            'starts_at' => $starts_at,
            'ends_at'   => $ends_at,
            'location'  => $location,
            'capacity'  => $capacity,
            'notes'     => $notes,
            'status'    => $status,
            'created_at'=> $now,
            'updated_at'=> $now,
        );

        $wpdb->insert($table, $row, array('%s','%s','%s','%s','%d','%s','%s','%s','%s'));
        return (int)($wpdb->insert_id ?? 0);
    }

    /**
     * Update an existing event.
     *
     * @param int   $id   Event ID.
     * @param array $data Event data.
     * @return bool True on success.
     */
    public static function update(int $id, array $data): bool {
        global $wpdb;
        $table = $wpdb->prefix . 'fb_events';
        $id    = absint($id);
        if (!$id) {
            return false;
        }

        $fields = array();
        if (isset($data['title'])) {
            $fields['title'] = sanitize_text_field(trim((string)$data['title']));
        }
        if (isset($data['starts_at'])) {
            $fields['starts_at'] = sanitize_text_field((string)$data['starts_at']);
        }
        if (isset($data['ends_at'])) {
            $fields['ends_at'] = sanitize_text_field((string)$data['ends_at']);
        }
        if (isset($fields['starts_at'], $fields['ends_at']) && $fields['ends_at'] < $fields['starts_at']) {
            $fields['ends_at'] = $fields['starts_at'];
        }
        if (isset($data['location'])) {
            $fields['location'] = sanitize_text_field((string)$data['location']);
        }
        if (array_key_exists('capacity', $data)) {
            $fields['capacity'] = max(0, (int)$data['capacity']);
        }
        if (isset($data['notes'])) {
            $fields['notes'] = sanitize_textarea_field((string)$data['notes']);
        }
        if (isset($data['status'])) {
            $status = sanitize_key((string)$data['status']);
            $fields['status'] = in_array($status, array('active','cancelled'), true) ? $status : 'active';
        }
        if (!$fields) {
            return true;
        }
        $now                 = (int)apply_filters('fbm_now', time());
        $fields['updated_at'] = gmdate('Y-m-d H:i:s', $now);
        $where               = array('id' => $id);
        $where_format        = array('%d');
        $result = $wpdb->update($table, $fields, $where, null, $where_format);
        return (bool)$result;
    }

    /**
     * Delete an event.
     */
    public static function delete(int $id): bool {
        global $wpdb;
        $table = $wpdb->prefix . 'fb_events';
        $id    = absint($id);
        if (!$id) {
            return false;
        }
        $deleted = $wpdb->delete($table, array('id' => $id), array('%d'));
        return (bool)$deleted;
    }

    /**
     * Get a single event.
     *
     * @return array|null
     */
    public static function get(int $id): ?array {
        global $wpdb;
        $table = $wpdb->prefix . 'fb_events';
        $id    = absint($id);
        if (!$id) {
            return null;
        }
        $sql  = "SELECT id,title,starts_at,ends_at,location,capacity,notes,status,created_at,updated_at FROM {$table} WHERE id = %d";
        $query = $wpdb->prepare($sql, $id);
        $row   = $wpdb->get_row($query, \ARRAY_A);
        if (!$row) {
            return null;
        }
        return self::sanitize_row($row);
    }

    /**
     * List events with filters.
     *
     * @param array $filters Filters.
     * @param array $opts    Options.
     * @return array{rows:array<int,array>, total:int}
     */
    public static function list(array $filters = array(), array $opts = array()): array {
        global $wpdb;
        $table = $wpdb->prefix . 'fb_events';

        $where = array();
        $args  = array();

        $status = isset($filters['status']) ? sanitize_key((string)$filters['status']) : '';
        if (in_array($status, array('active', 'cancelled'), true)) {
            $where[] = 'status = %s';
            $args[]  = $status;
        }

        $q = isset($filters['q']) ? sanitize_text_field((string)$filters['q']) : '';
        if ($q !== '') {
            $where[] = 'title LIKE %s';
            $args[]  = '%' . esc_like($q) . '%';
        }

        $from = isset($filters['from']) ? sanitize_text_field((string)$filters['from']) : '';
        if ($from !== '') {
            $where[] = 'starts_at >= %s';
            $args[]  = $from;
        }
        $to = isset($filters['to']) ? sanitize_text_field((string)$filters['to']) : '';
        if ($to !== '') {
            $where[] = 'ends_at <= %s';
            $args[]  = $to;
        }

        $where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $allowed_order_by = array('starts_at','ends_at','title','created_at');
        $order_by = isset($opts['order_by']) ? sanitize_key((string)$opts['order_by']) : 'starts_at';
        if (!in_array($order_by, $allowed_order_by, true)) {
            $order_by = 'starts_at';
        }
        $order = isset($opts['order']) ? strtoupper(sanitize_key((string)$opts['order'])) : 'ASC';
        if (!in_array($order, array('ASC','DESC'), true)) {
            $order = 'ASC';
        }
        $limit = isset($opts['limit']) ? (int)$opts['limit'] : 20;
        if ($limit < 1) {
            $limit = 1;
        }
        if ($limit > 200) {
            $limit = 200;
        }
        $offset = isset($opts['offset']) ? max(0, (int)$opts['offset']) : 0;

        $sql = "SELECT id,title,starts_at,ends_at,location,capacity,notes,status,created_at,updated_at FROM {$table} {$where_sql} ORDER BY {$order_by} {$order} LIMIT %d OFFSET %d";
        $args_list = array_merge($args, array($limit, $offset));
        $prepared  = $wpdb->prepare($sql, ...$args_list);
        $rows_raw  = $wpdb->get_results($prepared, \ARRAY_A);
        $rows      = array();
        foreach ($rows_raw as $row) {
            $rows[] = self::sanitize_row($row);
        }

        $count_sql = "SELECT COUNT(*) FROM {$table} {$where_sql}";
        $count_prepared = $wpdb->prepare($count_sql, ...$args);
        $total = (int)$wpdb->get_var($count_prepared);

        return array(
            'rows'  => $rows,
            'total' => $total,
        );
    }

    /**
     * Sanitize a row from the DB.
     *
     * @param array $row Raw row.
     * @return array<string,mixed>
     */
    private static function sanitize_row(array $row): array {
        return array(
            'id'         => (int)($row['id'] ?? 0),
            'title'      => sanitize_text_field((string)($row['title'] ?? '')),
            'starts_at'  => sanitize_text_field((string)($row['starts_at'] ?? '')),
            'ends_at'    => sanitize_text_field((string)($row['ends_at'] ?? '')),
            'location'   => sanitize_text_field((string)($row['location'] ?? '')),
            'capacity'   => isset($row['capacity']) ? (int)$row['capacity'] : null,
            'notes'      => sanitize_textarea_field((string)($row['notes'] ?? '')),
            'status'     => sanitize_key((string)($row['status'] ?? 'active')),
            'created_at' => sanitize_text_field((string)($row['created_at'] ?? '')),
            'updated_at' => sanitize_text_field((string)($row['updated_at'] ?? '')),
        );
    }
}
