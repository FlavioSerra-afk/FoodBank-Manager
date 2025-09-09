<?php // phpcs:ignoreFile
/**
 * Attendance export controller.
 *
 * @package FBM\Http
 */

declare(strict_types=1);

namespace FBM\Http;

use FBM\Attendance\ReportsService;
use FBM\Core\Jobs\JobsRepo;
use FBM\Exports\CsvWriter;
use FBM\Exports\XlsxWriter;
use function current_user_can;
use function wp_verify_nonce;
use function wp_die;
use function sanitize_text_field;
use function sanitize_key;
use function absint;
use function apply_filters;
use function admin_url;
use function wp_safe_redirect;
use function fbm_send_headers;
use function gmdate;
use function esc_html;

/**
 * Handle CSV/XLSX/PDF attendance exports.
 */
final class AttendanceExportController {
    private const THRESHOLD = 200;
    /**
     * Dispatch action based on request.
     */
    public static function handle(): void {
        $action = isset($_GET['action']) ? sanitize_key((string) $_GET['action']) : '';
        switch ($action) {
            case 'fbm_export_attendance_csv':
                self::csv();
                break;
            case 'fbm_export_attendance_xlsx':
                self::xlsx();
                break;
            case 'fbm_export_attendance_pdf':
                self::pdf();
                break;
        }
    }

    private static function ensure(): array {
        if (!current_user_can('fbm_manage_events')) {
            wp_die(__('Forbidden', 'foodbank-manager'));
        }
        $nonce = $_GET['_wpnonce'] ?? '';
        if (!wp_verify_nonce($nonce, 'fbm_attendance_export')) {
            wp_die(__('Invalid nonce', 'foodbank-manager'));
        }
        $from = isset($_GET['from']) ? sanitize_text_field((string) $_GET['from']) : '';
        $to = isset($_GET['to']) ? sanitize_text_field((string) $_GET['to']) : '';
        $event_id = isset($_GET['event_id']) ? absint($_GET['event_id']) : 0;
        $method = isset($_GET['method']) ? sanitize_key((string) $_GET['method']) : '';
        if (!in_array($method, array('qr','manual'), true)) {
            $method = '';
        }
        $masked = !current_user_can('fbm_view_sensitive') || empty($_GET['unmask']);
        return array(
            'filters' => array(
                'from'     => $from,
                'to'       => $to,
                'event_id' => $event_id,
                'method'   => $method,
            ),
            'masked' => $masked,
        );
    }

    private static function csv(): void {
        $data = self::ensure();
        if (self::maybe_queue('csv', $data, null)) {
            return;
        }
        $rows = ReportsService::export_rows($data['filters'], $data['masked']);
        if (self::maybe_queue('csv', $data, $rows)) {
            return;
        }
        $date = gmdate('Ymd');
        $headers = array(
            'Content-Type: text/csv; charset=utf-8',
            'Content-Disposition: attachment; filename="fbm-attendance-' . $date . '.csv"',
        );
        fbm_send_headers($headers);
        $out = fopen('php://output', 'wb');
        CsvWriter::writeBom($out);
        CsvWriter::put($out, array('date','event','recipient','method','note','operator'), ',', '"', '\\');
        foreach ($rows as $r) {
            CsvWriter::put($out, array($r['date'],$r['event'],$r['recipient_masked'],$r['method'],$r['note_masked'],$r['operator']), ',', '"', '\\');
        }
        if (apply_filters('fbm_http_exit', true)) {
            exit;
        }
    }

    private static function xlsx(): void {
        $data = self::ensure();
        if (self::maybe_queue('xlsx', $data, null)) {
            return;
        }
        $rows = ReportsService::export_rows($data['filters'], $data['masked']);
        if (self::maybe_queue('xlsx', $data, $rows)) {
            return;
        }
        $columns = array('date','event','recipient','method','note','operator');
        $body = XlsxWriter::build($columns, array_map(function ($r) {
            return array($r['date'],$r['event'],$r['recipient_masked'],$r['method'],$r['note_masked'],$r['operator']);
        }, $rows));
        fbm_send_headers($body['headers']);
        echo $body['body'];
        if (apply_filters('fbm_http_exit', true)) {
            exit;
        }
    }

    private static function pdf(): void {
        $data = self::ensure();
        if (self::maybe_queue('pdf', $data, null)) {
            return;
        }
        $rows = ReportsService::export_rows($data['filters'], $data['masked']);
        if (self::maybe_queue('pdf', $data, $rows)) {
            return;
        }
        $summary = ReportsService::period_summary($data['filters']);
        $html = '<h1>Attendance Report</h1>';
        $html .= '<p>Today: ' . (int)$summary['today'] . '</p>';
        $html .= '<table><tr><th>Date</th><th>Event</th><th>Recipient</th><th>Method</th><th>Note</th><th>Operator</th></tr>';
        foreach ($rows as $r) {
            $html .= '<tr><td>' . esc_html($r['date']) . '</td><td>' . esc_html($r['event']) . '</td><td>' . esc_html($r['recipient_masked']) . '</td><td>' . esc_html($r['method']) . '</td><td>' . esc_html($r['note_masked']) . '</td><td>' . esc_html($r['operator']) . '</td></tr>';
        }
        $html .= '</table>';
        $res = array();
        if (class_exists('\Mpdf\Mpdf')) {
            $pdf = new \Mpdf\Mpdf();
            $pdf->WriteHTML($html);
            $res['headers'] = array('Content-Type: application/pdf', 'Content-Disposition: attachment; filename="fbm-attendance-' . gmdate('Ymd') . '.pdf"');
            $res['body'] = (string)$pdf->Output('', 'S');
        } elseif (class_exists('\TCPDF')) {
            $pdf = new \TCPDF();
            $pdf->AddPage();
            $pdf->writeHTML($html);
            $res['headers'] = array('Content-Type: application/pdf', 'Content-Disposition: attachment; filename="fbm-attendance-' . gmdate('Ymd') . '.pdf"');
            $res['body'] = (string)$pdf->Output('', 'S');
        } else {
            $res['headers'] = array('Content-Type: text/html; charset=utf-8', 'Content-Disposition: attachment; filename="fbm-attendance-' . gmdate('Ymd') . '.html"');
            $res['body'] = '<div class="notice notice-error"><p>PDF engine not installed — printed HTML provided</p></div>' . $html;
        }
        fbm_send_headers($res['headers']);
        echo $res['body'];
        if (apply_filters('fbm_http_exit', true)) {
            exit;
        }
    }

    /**
     * Maybe queue export job.
     *
     * @param string     $format Export format.
     * @param array      $data   Request data.
     * @param array|null $rows   Rows if already computed.
     * @return bool Whether a job was queued.
     */
    private static function maybe_queue(string $format, array $data, ?array $rows): bool {
        $queue = isset($_GET['queue']) && '1' === sanitize_key((string) $_GET['queue']);
        if ($queue || (null !== $rows && count($rows) > self::THRESHOLD)) {
            $job_id = JobsRepo::create('attendance_export', $format, $data['filters'], $data['masked']);
            wp_safe_redirect(admin_url('admin.php?page=fbm_reports&notice=export_queued&job=' . $job_id));
            if (apply_filters('fbm_http_exit', true)) {
                exit;
            }
            return true;
        }
        return false;
    }
}
