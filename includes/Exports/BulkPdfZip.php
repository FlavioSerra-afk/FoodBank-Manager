<?php
/**
 * Bulk PDF ZIP exporter.
 *
 * @package FBM\Exports
 */

declare(strict_types=1);

namespace FBM\Exports;

use function class_exists;
use function apply_filters;
use function sanitize_file_name;
use function file_get_contents;
use function unlink;
use function tempnam;
use function sys_get_temp_dir;

/**
 * Build a ZIP of entry receipts.
 */
final class BulkPdfZip {
    /**
     * Build a zip of receipt PDFs (or HTML fallbacks).
     *
     * @param iterable $entries  list of entry arrays
     * @param array $options ['masked'=>bool, 'basename'=>'entries-YYYYMMDD']
     * @return array{headers:array<int,string>,body:string}
     */
    public static function build(iterable $entries, array $options = []): array {
        $masked   = $options['masked'] ?? true;
        $now      = (int) apply_filters('fbm_now', time());
        $basename = (string) ($options['basename'] ?? ('entries-' . gmdate('Ymd', $now)));
        $files    = array();
        foreach ($entries as $entry) {
            $res   = PdfReceipt::build($entry, array('masked' => $masked));
            $ctype = '';
            foreach ($res['headers'] as $h) {
                $h = strtolower($h);
                if (str_starts_with($h, 'content-type:')) {
                    $ctype = trim(substr($h, 13));
                }
            }
            $id  = isset($entry['id']) ? (int) $entry['id'] : 0;
            $ext = str_contains($ctype, 'pdf') ? '.pdf' : '.html';
            $name = 'receipts/entry-' . $id . $ext;
            $files[$name] = $res['body'];
        }
        if (class_exists('\ZipArchive')) {
            $tmp = sys_get_temp_dir() . '/fbm_zip_' . uniqid('', true) . '.zip';
            $zip = new \ZipArchive();
            if ($zip->open($tmp, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                foreach ($files as $name => $content) {
                    $zip->addFromString($name, $content);
                }
                $zip->close();
                $body = (string) @file_get_contents($tmp);
                if (file_exists($tmp)) {
                    unlink($tmp);
                }
                if ($body === '') {
                    $body = self::simpleZip($files);
                }
            } else {
                $body = self::simpleZip($files);
            }
        } else {
            $body = self::simpleZip($files);
        }
        $headers = array(
            'Content-Type: application/zip',
            'Content-Disposition: attachment; filename="' . sanitize_file_name($basename . '.zip') . '"',
        );
        return array('headers' => $headers, 'body' => $body);
    }

    /**
     * Build a minimal ZIP archive without ZipArchive.
     *
     * @param array<string,string> $files Files to include.
     * @return string
     */
    private static function simpleZip(array $files): string {
        $data = '';
        $central = '';
        $offset = 0;
        foreach ($files as $name => $content) {
            $name = str_replace('\\', '/', $name);
            $compressed = gzcompress($content);
            if ($compressed === false) {
                $compressed = $content;
                $method = 0;
            } else {
                $compressed = substr($compressed, 2, -4);
                $method = 8;
            }
            $crc = crc32($content);
            $len = strlen($content);
            $zlen = strlen($compressed);
            $data .= pack('VvvvVVVvv', 0x04034b50, 20, 0, $method, 0, $crc, $zlen, $len, strlen($name), 0)
                . $name . $compressed;
            $central .= pack('VvvvvvVVVvvvvvVV', 0x02014b50, 20, 20, 0, $method, 0, $crc, $zlen, $len, strlen($name), 0, 0, 0, 0, 0, $offset)
                . $name;
            $offset += 30 + strlen($name) + $zlen;
        }
        $end = pack('VvvvvVVv', 0x06054b50, 0, 0, count($files), count($files), strlen($central), strlen($data), 0);
        return $data . $central . $end;
    }
}
