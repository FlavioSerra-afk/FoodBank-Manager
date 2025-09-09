<?php
/**
 * Export jobs repository.
 *
 * @package FBM\Core\Jobs
 */

declare(strict_types=1);

namespace FBM\Core\Jobs;

use wpdb;
use function absint;
use function gmdate;
use function json_decode;
use function wp_json_encode;

/**
 * Persist export jobs in custom table.
 */
final class JobsRepo {
    private static function table(): string {
        global $wpdb;
        return $wpdb->prefix . 'fbm_jobs';
    }

    /**
     * Enqueue a new job.
     *
     * @param string $type    Job type.
     * @param string $format  Export format.
     * @param array  $filters Filters.
     * @param bool   $masked  Masked flag.
     * @return int Job ID.
     */
    public static function create(string $type, string $format, array $filters, bool $masked): int {
        global $wpdb;
        $table = self::table();
        $now   = gmdate('Y-m-d H:i:s');
        $row   = array(
            'type'       => $type,
            'format'     => $format,
            'filters'    => wp_json_encode($filters),
            'masked'     => $masked ? 1 : 0,
            'status'     => 'pending',
            'attempts'   => 0,
            'file_path'  => '',
            'created_at' => $now,
            'updated_at' => $now,
            'last_error' => '',
        );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $wpdb->insert($table, $row);
        return (int) ($wpdb->insert_id ?? 0);
    }

    /**
     * Get a job by id.
     *
     * @param int $id Job id.
     * @return array|null
     */
    public static function get(int $id): ?array {
        global $wpdb;
        $table = self::table();
        $id    = absint($id);
        if (!$id) {
            return null;
        }
        // phpcs:ignore WordPress.DB.PreparedSQL
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id=%d", $id), ARRAY_A);
        if (!$row) {
            return null;
        }
        $row['filters'] = json_decode((string) $row['filters'], true) ?: array();
        $row['masked']  = (bool) $row['masked'];
        return $row;
    }

    /**
     * List recent jobs.
     *
     * @param array $opts Options.
     * @return array<int,array>
     */
    public static function list(array $opts = array()): array {
        global $wpdb;
        $table = self::table();
        $allowed = array('created_at', 'status');
        $order_by = isset($opts['order_by']) && in_array($opts['order_by'], $allowed, true) ? $opts['order_by'] : 'created_at';
        $order    = isset($opts['order']) && 'ASC' === strtoupper((string) $opts['order']) ? 'ASC' : 'DESC';
        $limit    = isset($opts['limit']) ? absint($opts['limit']) : 20;
        $limit    = max(1, min(100, $limit));
        // phpcs:ignore WordPress.DB.PreparedSQL
        $sql  = $wpdb->prepare("SELECT * FROM {$table} ORDER BY {$order_by} {$order} LIMIT %d", $limit);
        // phpcs:ignore WordPress.DB.PreparedSQL
        $rows = $wpdb->get_results($sql, ARRAY_A);
        foreach ($rows as &$r) {
            $r['filters'] = json_decode((string) $r['filters'], true) ?: array();
            $r['masked']  = (bool) $r['masked'];
        }
        return $rows;
    }

    /**
     * Count pending or running jobs.
     *
     * @return int Number of jobs.
     */
    public static function pending_count(): int {
        global $wpdb;
        $table = self::table();
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $sql = "SELECT COUNT(*) FROM {$table} WHERE status IN ('pending','running')";
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return (int) $wpdb->get_var($sql);
    }

    /**
     * Claim the next pending job and mark running.
     *
     * @return array|null Claimed job.
     */
    public static function claim(): ?array {
        global $wpdb;
        $table = self::table();
        $now   = gmdate('Y-m-d H:i:s');
        // phpcs:ignore WordPress.DB.PreparedSQL
        $updated = $wpdb->query($wpdb->prepare("UPDATE {$table} SET status='running', updated_at=%s, id=LAST_INSERT_ID(id) WHERE status='pending' ORDER BY id ASC LIMIT 1", $now));
        if (!$updated) {
            return null;
        }
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $id = (int) $wpdb->get_var('SELECT LAST_INSERT_ID()');
        return self::get($id);
    }

    /**
     * Mark job as completed.
     */
    public static function mark_done(int $id, string $file): void {
        global $wpdb;
        $table = self::table();
        $id    = absint($id);
        if (!$id) {
            return;
        }
        $wpdb->update(
            $table,
            array(
                'status'     => 'done',
                'file_path'  => $file,
                'updated_at' => gmdate('Y-m-d H:i:s'),
            ),
            array('id' => $id),
            array('%s','%s','%s'),
            array('%d')
        );
    }

    /**
     * Mark job as failed.
     */
    public static function mark_failed(int $id, string $error): void {
        global $wpdb;
        $table = self::table();
        $id    = absint($id);
        if (!$id) {
            return;
        }
        $wpdb->update(
            $table,
            array(
                'status'     => 'failed',
                'last_error' => substr($error, 0, 190),
                'updated_at' => gmdate('Y-m-d H:i:s'),
            ),
            array('id' => $id),
            array('%s','%s','%s'),
            array('%d')
        );
        // Increment attempts separately to allow expression.
        // phpcs:ignore WordPress.DB.PreparedSQL
        $wpdb->query($wpdb->prepare("UPDATE {$table} SET attempts = attempts + 1 WHERE id = %d", $id));
    }

    /**
     * Retry a failed job.
     */
    public static function retry(int $id): void {
        global $wpdb;
        $table = self::table();
        $id    = absint($id);
        if (!$id) {
            return;
        }
        $wpdb->update(
            $table,
            array(
                'status'     => 'pending',
                'last_error' => '',
                'updated_at' => gmdate('Y-m-d H:i:s'),
            ),
            array('id' => $id),
            array('%s','%s','%s'),
            array('%d')
        );
    }
}
