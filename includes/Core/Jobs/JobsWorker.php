<?php
/**
 * Background export jobs worker.
 *
 * @package FBM\Core\Jobs
 */

declare(strict_types=1);

namespace FBM\Core\Jobs;

use FBM\Attendance\ReportsService;
use FBM\Exports\CsvWriter;
use FBM\Exports\XlsxWriter;
use function absint;
use function apply_filters;
use function class_exists;
use function esc_html;
use function file_put_contents;
use function gmdate;
use function is_dir;
use function sanitize_file_name;
use function wp_mkdir_p;
use function wp_next_scheduled;
use function wp_schedule_event;
use function wp_upload_dir;
use function add_filter;
use function add_action;
use function time;

/**
 * Process export jobs via cron.
 */
final class JobsWorker {
    public const EVENT = 'fbm_jobs_tick';
    private const DIR   = 'fbm-exports';

    /** Register cron hook. */
    public static function init(): void {
        add_filter('cron_schedules', array(self::class, 'schedule_interval'));
        add_action(self::EVENT, array(self::class, 'tick'));
    }

    /** Add 5-minute schedule. */
    public static function schedule_interval(array $schedules): array {
        if (!isset($schedules['fbm_jobs'])) {
            $schedules['fbm_jobs'] = array(
                'interval' => 300,
                'display'  => 'FBM Jobs',
            );
        }
        return $schedules;
    }

    /** Ensure event scheduled. */
    public static function schedule(): void {
        if (!wp_next_scheduled(self::EVENT)) {
            wp_schedule_event(time() + 300, 'fbm_jobs', self::EVENT);
        }
    }

    /** Cron tick handler. */
    public static function tick(): void {
        $job = JobsRepo::claim();
        if (!$job) {
            return;
        }
        try {
            self::process($job);
        } catch (\Throwable $e) {
            JobsRepo::mark_failed(absint($job['id']), $e->getMessage());
        }
    }

    /**
     * Process a single job.
     *
     * @param array $job Job data.
     * @return void
     */
    private static function process(array $job): void {
        if ('attendance_export' !== ($job['type'] ?? '')) {
            throw new \RuntimeException('unsupported job');
        }
        $rows = ReportsService::export_rows($job['filters'], (bool) $job['masked']);
        $upload = wp_upload_dir();
        $dir = rtrim($upload['basedir'], '/') . '/' . self::DIR;
        if (!is_dir($dir)) {
            wp_mkdir_p($dir);
        }
        $ts   = gmdate('Ymd-His', (int) apply_filters('fbm_now', time()));
        $base = 'fbm-' . $job['type'] . '-' . $ts . '-' . $job['id'];
        $ext  = $job['format'];
        $path = $dir . '/' . sanitize_file_name($base . '.' . $ext);
        switch ($job['format']) {
            case 'csv':
                $out = fopen($path, 'wb');
                CsvWriter::writeBom($out);
                CsvWriter::put($out, array('date','event','recipient','method','note','operator'), ',', '"', '\\');
                foreach ($rows as $r) {
                    CsvWriter::put($out, array($r['date'],$r['event'],$r['recipient_masked'],$r['method'],$r['note_masked'],$r['operator']), ',', '"', '\\');
                }
                fclose($out);
                break;
            case 'xlsx':
                $cols = array('date','event','recipient','method','note','operator');
                $body = XlsxWriter::build($cols, array_map(function ($r) {
                    return array($r['date'],$r['event'],$r['recipient_masked'],$r['method'],$r['note_masked'],$r['operator']);
                }, $rows));
                file_put_contents($path, $body['body']);
                break;
            case 'pdf':
                $html = '<h1>Attendance Report</h1><table><tr><th>Date</th><th>Event</th><th>Recipient</th><th>Method</th><th>Note</th><th>Operator</th></tr>';
                foreach ($rows as $r) {
                    $html .= '<tr><td>' . esc_html($r['date']) . '</td><td>' . esc_html($r['event']) . '</td><td>' . esc_html($r['recipient_masked']) . '</td><td>' . esc_html($r['method']) . '</td><td>' . esc_html($r['note_masked']) . '</td><td>' . esc_html($r['operator']) . '</td></tr>';
                }
                $html .= '</table>';
                if (class_exists('\\Mpdf\\Mpdf')) {
                    $pdf = new \Mpdf\Mpdf();
                    $pdf->WriteHTML($html);
                    file_put_contents($path, (string) $pdf->Output('', 'S'));
                } elseif (class_exists('\\TCPDF')) {
                    $pdf = new \TCPDF();
                    $pdf->AddPage();
                    $pdf->writeHTML($html);
                    file_put_contents($path, (string) $pdf->Output('', 'S'));
                } else {
                    $ext  = 'html';
                    $path = $dir . '/' . sanitize_file_name($base . '.html');
                    $fallback = '<div class="notice notice-error"><p>PDF engine not installed — printed HTML provided</p></div>';
                    file_put_contents($path, $fallback . $html);
                }
                break;
            default:
                throw new \RuntimeException('invalid format');
        }
        JobsRepo::mark_done(absint($job['id']), $path);
    }
}
