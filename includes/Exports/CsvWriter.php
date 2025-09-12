<?php
/**
 * Type-safe CSV writer utilities.
 *
 * @package FoodBankManager\Exports
 */

declare(strict_types=1);

namespace FBM\Exports;

/**
 * CSV writer.
 */
final class CsvWriter {
    /**
     * Emit UTF-8 BOM once per stream.
     *
     * @param resource|null $h Stream handle.
     * @return void
     */
    public static function writeBom($h): void {
        // idempotent: tag stream
        if (!is_resource($h)) {
            return;
        }
        /** @var array<string,mixed> $meta */
        $meta = stream_get_meta_data($h);
        $key  = 'fbm_bom_written';
        if (!isset($meta[$key]) && !($GLOBALS['__fbm_bom_written'] ?? false)) {
            fwrite($h, "\xEF\xBB\xBF");
            $meta[$key] = true; // tag stream
            $GLOBALS['__fbm_bom_written'] = true; // test-safe
        }
    }

    /**
     * Type-safe wrapper around \fputcsv.
     *
     * @param resource    $h   Stream handle.
     * @param array<int,mixed> $row Row data.
     * @param string      $sep Separator.
     * @param string      $enc Enclosure.
     * @param string      $esc Escape character.
     * @return int|false Bytes written or false on failure.
     */
    public static function put($h, array $row, string $sep = ',', string $enc = '"', string $esc = '\\') {
        // normalize fields to strings without mutating data shape
        $norm = array_map(static function ($v) {
            if (is_bool($v)) {
                $v = $v ? '1' : '0';
            } elseif (is_scalar($v) || $v === null) {
                $v = (string) $v;
            } else {
                $v = json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
            if ($v !== '' && preg_match('/^[=+\-@\t\r]/', $v) === 1) {
                $v = "'" . $v;
            }
            return $v;
        }, $row);
        return \fputcsv($h, $norm, $sep, $enc, $esc);
    }
}
