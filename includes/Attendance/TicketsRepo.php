<?php
/**
 * Tickets repository.
 *
 * @package FBM\Attendance
 */

declare(strict_types=1);

namespace FBM\Attendance;

use wpdb;
use function absint;
use function apply_filters;
use function gmdate;
use function sanitize_email;
use function sanitize_key;
use function sanitize_text_field;
use function strtolower;
use function time;

/**
 * Data access for ticket tokens.
 */
final class TicketsRepo {
    /**
     * Issue a ticket row.
     *
     * @return int Inserted ID.
     */
    public static function issue(int $event_id, string $recipient, int $exp, string $nonce, string $token_hash): int {
        global $wpdb;
        $table = $wpdb->prefix . 'fb_tickets';
        $event_id = absint($event_id);
        $recipient = strtolower(trim(sanitize_email($recipient)));
        $exp_dt = gmdate('Y-m-d H:i:s', $exp);
        $now = gmdate('Y-m-d H:i:s', (int) apply_filters('fbm_now', time()));
        $wpdb->insert(
            $table,
            array(
                'event_id'   => $event_id,
                'recipient'  => $recipient,
                'token_hash' => $token_hash,
                'exp'        => $exp_dt,
                'nonce'      => TicketService::b64url_decode($nonce),
                'status'     => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ),
            array('%d','%s','%s','%s','%s','%s','%s','%s')
        );
        return (int) ($wpdb->insert_id ?? 0);
    }

    /**
     * Get a ticket by ID.
     *
     * @return array<string,mixed>|null
     */
    public static function get(int $id): ?array {
        global $wpdb;
        $table = $wpdb->prefix . 'fb_tickets';
        $id = absint($id);
        if (!$id) {
            return null;
        }
        $sql = $wpdb->prepare("SELECT id,event_id,recipient,token_hash,exp,nonce,status FROM {$table} WHERE id = %d", $id);
        $row = $wpdb->get_row($sql, ARRAY_A);
        if (!$row) {
            return null;
        }
        return array(
            'id'        => (int) ($row['id'] ?? 0),
            'event_id'  => (int) ($row['event_id'] ?? 0),
            'recipient' => sanitize_email((string) ($row['recipient'] ?? '')),
            'token_hash'=> (string) ($row['token_hash'] ?? ''),
            'exp'       => sanitize_text_field((string) ($row['exp'] ?? '')),
            'nonce'     => TicketService::b64url_encode((string) ($row['nonce'] ?? '')),
            'status'    => sanitize_key((string) ($row['status'] ?? '')),
        );
    }

    /**
     * Regenerate ticket for ID.
     *
     * @return int New ticket ID or 0.
     */
    public static function regenerate(int $id, int $exp, string $nonce, string $token_hash): int {
        $current = self::get($id);
        if (!$current) {
            return 0;
        }
        global $wpdb;
        $table = $wpdb->prefix . 'fb_tickets';
        $now   = gmdate('Y-m-d H:i:s', (int) apply_filters('fbm_now', time()));
        $wpdb->update(
            $table,
            array('status' => 'revoked', 'updated_at' => $now),
            array('id' => $id),
            array('%s','%s'),
            array('%d')
        );
        return self::issue($current['event_id'], $current['recipient'], $exp, $nonce, $token_hash);
    }

    /**
     * Revoke a ticket.
     */
    public static function revoke(int $id): bool {
        global $wpdb;
        $table = $wpdb->prefix . 'fb_tickets';
        $id = absint($id);
        if (!$id) {
            return false;
        }
        $now = gmdate('Y-m-d H:i:s', (int) apply_filters('fbm_now', time()));
        $updated = $wpdb->update(
            $table,
            array('status' => 'revoked', 'updated_at' => $now),
            array('id' => $id),
            array('%s','%s'),
            array('%d')
        );
        return (bool) $updated;
    }

    /**
     * List tickets for an event.
     *
     * @param array $opts Options.
     * @return array{rows:array<int,array>,total:int}
     */
    public static function list_for_event(int $event_id, array $opts = array()): array {
        global $wpdb;
        $table = $wpdb->prefix . 'fb_tickets';
        $event_id = absint($event_id);
        $order_by = sanitize_key((string) ($opts['order_by'] ?? 'created_at'));
        if (!in_array($order_by, array('created_at','recipient'), true)) {
            $order_by = 'created_at';
        }
        $order = strtoupper(sanitize_key((string) ($opts['order'] ?? 'DESC')));
        if (!in_array($order, array('ASC','DESC'), true)) {
            $order = 'DESC';
        }
        $limit  = absint($opts['limit'] ?? 20);
        if ($limit < 1) { $limit = 20; }
        if ($limit > 100) { $limit = 100; }
        $offset = max(0, absint($opts['offset'] ?? 0));
        $status = isset($opts['status']) ? sanitize_key((string) $opts['status']) : '';
        $where  = 'event_id = %d';
        $args   = array($event_id);
        if ($status && in_array($status, array('active','revoked'), true)) {
            $where .= ' AND status = %s';
            $args[] = $status;
        }
        $query = $wpdb->prepare(
            "SELECT id,recipient,status,exp,created_at FROM {$table} WHERE {$where} ORDER BY {$order_by} {$order} LIMIT %d OFFSET %d",
            array_merge($args, array($limit, $offset))
        );
        $rows = $wpdb->get_results($query, ARRAY_A);
        $total_q = $wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE {$where}", $args);
        $total = (int) $wpdb->get_var($total_q);
        $out = array();
        foreach ($rows ? $rows : array() as $r) {
            $out[] = array(
                'id'        => (int) ($r['id'] ?? 0),
                'recipient' => sanitize_email((string) ($r['recipient'] ?? '')),
                'status'    => sanitize_key((string) ($r['status'] ?? '')),
                'exp'       => sanitize_text_field((string) ($r['exp'] ?? '')),
                'created_at'=> sanitize_text_field((string) ($r['created_at'] ?? '')),
            );
        }
        return array(
            'rows'  => $out,
            'total' => $total,
        );
    }
}
