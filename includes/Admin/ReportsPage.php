<?php
/**
 * Reports admin page.
 *
 * @package FBM\Admin
 */

declare(strict_types=1);

namespace FBM\Admin;

use FBM\Attendance\ReportsService;
use FBM\Attendance\EventsRepo;
use function current_user_can;
use function esc_html__;
use function esc_html;
use function esc_attr;
use function esc_url;
use function add_query_arg;
use function sanitize_text_field;
use function sanitize_key;
use function wp_create_nonce;
use function admin_url;
use function absint;

/**
 * Controller for Attendance reports page.
 */
final class ReportsPage {
    /**
     * Route handler.
     */
    public static function route(): void {
        if (!current_user_can('fbm_manage_events')) {
            echo '<div class="wrap fbm-admin"><p>' . esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ) . '</p></div>';
            return;
        }
        $filters = self::get_filters();
        $summary = ReportsService::period_summary($filters);
        $daily   = ReportsService::daily_counts(7, $filters);
        $events  = EventsRepo::list(array(), array('limit'=>100));
        $nonce   = wp_create_nonce('fbm_attendance_export');
        $base    = admin_url('admin-post.php');
        $query   = array_merge($filters, array('_wpnonce'=>$nonce));
        $csv_url  = esc_url(add_query_arg(array_merge($query, array('action'=>'fbm_export_attendance_csv')), $base));
        $xlsx_url = esc_url(add_query_arg(array_merge($query, array('action'=>'fbm_export_attendance_xlsx')), $base));
        $pdf_url  = esc_url(add_query_arg(array_merge($query, array('action'=>'fbm_export_attendance_pdf')), $base));
        require FBM_PATH . 'templates/admin/reports.php';
    }

    /**
     * Parse filters from query vars.
     *
     * @return array<string,mixed>
     */
    private static function get_filters(): array {
        $from = isset($_GET['from']) ? sanitize_text_field((string)$_GET['from']) : '';
        $to   = isset($_GET['to']) ? sanitize_text_field((string)$_GET['to']) : '';
        $event_id = isset($_GET['event_id']) ? absint($_GET['event_id']) : 0;
        $method = isset($_GET['method']) ? sanitize_key((string)$_GET['method']) : '';
        if (!in_array($method, array('qr','manual'), true)) {
            $method = '';
        }
        return array(
            'from'     => $from,
            'to'       => $to,
            'event_id' => $event_id,
            'method'   => $method,
        );
    }
}
