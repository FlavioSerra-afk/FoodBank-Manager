<?php
/**
 * Entry PDF receipt builder.
 *
 * @package FBM\Exports
 */

declare(strict_types=1);

namespace FBM\Exports;

use FoodBankManager\Security\Helpers;
use function esc_html;
use function sanitize_file_name;
use function wp_json_encode;
use function apply_filters;
use function class_exists;

/**
 * Build single entry PDF receipts with graceful HTML fallback.
 */
final class PdfReceipt {
    /**
     * Build a single entry receipt. Returns ['headers'=>[], 'body'=>string].
     * If no PDF engine, returns HTML fallback (Content-Type: text/html; charset=utf-8)
     *
     * @param array $entry  Canonical entry data (already fetched & sanitized)
     * @param array $options ['masked'=>bool, 'filename'=>string, 'brand'=>array]
     * @return array{headers:array<int,string>,body:string}
     */
    public static function build(array $entry, array $options = []): array {
        $masked   = $options['masked'] ?? true;
        $filename = (string)($options['filename'] ?? '');
        $entryOut = $entry;
        if ($masked !== false) {
            if (isset($entryOut['pii']['email'])) {
                $entryOut['pii']['email'] = Helpers::mask_email((string)$entryOut['pii']['email']);
            }
            if (isset($entryOut['pii']['postcode'])) {
                $entryOut['pii']['postcode'] = Helpers::mask_postcode((string)$entryOut['pii']['postcode']);
            }
            if (isset($entryOut['postcode'])) {
                $entryOut['postcode'] = Helpers::mask_postcode((string)$entryOut['postcode']);
            }
            if (isset($entryOut['email'])) {
                $entryOut['email'] = Helpers::mask_email((string)$entryOut['email']);
            }
        }
        $now = (int)apply_filters('fbm_now', time());
        $id  = isset($entry['id']) ? (int)$entry['id'] : 0;
        if ($filename === '') {
            $filename = 'entry-' . $id . '-' . gmdate('Ymd', $now);
        }
        $html = '<h1>Entry</h1><pre>' . esc_html(wp_json_encode($entryOut, JSON_PRETTY_PRINT)) . '</pre>';
        if (class_exists('\\Mpdf\\Mpdf') || class_exists('\\TCPDF')) {
            $pdf = null;
            if (class_exists('\\Mpdf\\Mpdf')) {
                $pdf = new \Mpdf\Mpdf();
                $pdf->WriteHTML($html);
                $body = (string)$pdf->Output('', 'S');
            } else {
                /** @phpstan-ignore-next-line */
                $pdf = new \TCPDF();
                /** @phpstan-ignore-next-line */
                $pdf->AddPage();
                /** @phpstan-ignore-next-line */
                $pdf->writeHTML($html);
                /** @phpstan-ignore-next-line */
                $body = (string)$pdf->Output('', 'S');
            }
            $headers = array(
                'Content-Type: application/pdf',
                'Content-Disposition: attachment; filename="' . sanitize_file_name($filename . '.pdf') . '"',
            );
            return array('headers' => $headers, 'body' => $body);
        }
        $headers = array(
            'Content-Type: text/html; charset=utf-8',
            'Content-Disposition: attachment; filename="' . sanitize_file_name($filename . '.html') . '"',
        );
        return array('headers' => $headers, 'body' => $html);
    }
}
