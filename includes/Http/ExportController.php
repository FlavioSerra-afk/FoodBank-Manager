<?php
/**
 * Export HTTP controller.
 *
 * @package FBM\Http
 */

declare(strict_types=1);

namespace FBM\Http;

use FBM\Exports\PdfReceipt;
use FBM\Exports\BulkPdfZip;
use FBM\Exports\XlsxWriter;
use FoodBankManager\Database\ApplicationsRepo;
use FoodBankManager\Attendance\AttendanceRepo;
use FoodBankManager\Shortcodes\Dashboard;
use function current_user_can;
use function check_admin_referer;
use function wp_die;
use function sanitize_key;
use function absint;
use function fbm_send_headers;
use function apply_filters;
use function __;

/**
 * Route export requests for PDFs and XLSX.
 */
final class ExportController {
    /**
     * Handle export actions.
     */
    public static function handle(): void {
        $action = isset($_GET['action']) ? sanitize_key((string) $_GET['action']) : '';
        switch ($action) {
            case 'fbm_export_entry_pdf':
                self::entryPdf();
                break;
            case 'fbm_export_entries_pdf_zip':
                self::entriesPdfZip();
                break;
            case 'fbm_export_dashboard_xlsx':
                self::dashboardXlsx();
                break;
        }
    }

    private static function ensureExportCap(string $nonce_action): void {
        if (! current_user_can('fbm_export')) {
            wp_die(__('Forbidden', 'foodbank-manager'));
        }
        check_admin_referer($nonce_action);
    }

    private static function entryPdf(): void {
        self::ensureExportCap('fbm_export_entry_pdf');
        $id = absint($_GET['id'] ?? 0);
        $entry = ApplicationsRepo::get_entry($id);
        if (! $entry) {
            wp_die(__('Not found', 'foodbank-manager'));
        }
        $masked = ! current_user_can('fbm_view_sensitive') || empty($_GET['unmask']);
        $res = PdfReceipt::build($entry, array('masked' => $masked));
        fbm_send_headers($res['headers']);
        echo $res['body'];
        if (apply_filters('fbm_http_exit', true)) {
            exit;
        }
    }

    private static function entriesPdfZip(): void {
        self::ensureExportCap('fbm_export_entries_pdf_zip');
        $masked = ! current_user_can('fbm_view_sensitive') || empty($_GET['unmask']);
        $ids = array();
        if (! empty($_GET['ids'])) {
            $raw = explode(',', (string) $_GET['ids']);
            foreach ($raw as $r) {
                $ids[] = absint($r);
            }
        }
        $entries = array();
        foreach ($ids as $id) {
            $e = ApplicationsRepo::get_entry($id);
            if ($e) {
                $entries[] = $e;
            }
        }
        $res = BulkPdfZip::build($entries, array('masked' => $masked));
        fbm_send_headers($res['headers']);
        echo $res['body'];
        if (apply_filters('fbm_http_exit', true)) {
            exit;
        }
    }

    private static function dashboardXlsx(): void {
        self::ensureExportCap('fbm_export_dashboard_xlsx');
        $period = Dashboard::sanitize_period(sanitize_key((string) ($_GET['period'] ?? '7d')));
        $since  = Dashboard::since_from_period($period);
        $event  = Dashboard::sanitize_event((string) ($_GET['event'] ?? ''));
        $type   = Dashboard::sanitize_type((string) ($_GET['type'] ?? 'all'));
        $policy = ! empty($_GET['policy_only']);
        $filters = array(
            'since'       => $since,
            'event'       => $event,
            'type'        => $type,
            'policy_only' => $policy,
        );
        $totals = AttendanceRepo::period_totals($since, $filters);
        $rows = array();
        foreach ($totals as $k => $v) {
            $label = ucwords(str_replace('_', ' ', (string) $k));
            $rows[] = array(__( $label, 'foodbank-manager' ), (string) (int) $v);
        }
        $columns = array(__( 'Metric', 'foodbank-manager' ), __( 'Count', 'foodbank-manager' ));
        $res = XlsxWriter::build($columns, $rows);
        fbm_send_headers($res['headers']);
        echo $res['body'];
        if (apply_filters('fbm_http_exit', true)) {
            exit;
        }
    }
}
