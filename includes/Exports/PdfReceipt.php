<?php
/**
 * Entry PDF receipt builder.
 *
 * @package FBM\Exports
 */

declare(strict_types=1);

namespace FBM\Exports;

use FoodBankManager\Security\Helpers;
use FoodBankManager\Exports\PdfRenderer;
use FoodBankManager\Core\Options;
use function sanitize_file_name;
use function wp_get_attachment_url;
use function apply_filters;

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
        $brand = Options::get( 'pdf.brand', array() );
        $brand['logo_url'] = isset( $brand['logo'] ) && (int) $brand['logo'] > 0 ? (string) wp_get_attachment_url( (int) $brand['logo'] ) : '';
        $letter = require FBM_PATH . 'templates/pdf/letterhead.php';
        ob_start();
        $entry = $entryOut; // for template scope
        /* @psalm-suppress UnresolvableInclude */
        require FBM_PATH . 'templates/pdf/receipt.php';
        $html = (string) ob_get_clean();
        $body = PdfRenderer::render( $html, array(
            'paper'       => $brand['page_size'] ?? 'A4',
            'orientation' => $brand['orientation'] ?? 'portrait',
            'header_html' => $letter['header_html'] ?? '',
            'footer_html' => $letter['footer_html'] ?? '',
        ) );
        $headers = array(
            'Content-Type: application/pdf',
            'Content-Disposition: attachment; filename="' . sanitize_file_name( $filename . '.pdf' ) . '"',
        );
        return array( 'headers' => $headers, 'body' => $body );
    }
}
