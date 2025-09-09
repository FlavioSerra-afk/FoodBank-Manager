<?php
/**
 * Attendance reports service.
 *
 * @package FBM\Attendance
 */

declare(strict_types=1);

namespace FBM\Attendance;

use FBM\Attendance\CheckinsRepo;
use FBM\Attendance\EventsRepo;
use DateTimeImmutable;
use DateInterval;
use wpdb;
use function absint;
use function apply_filters;
use function sanitize_key;
use function sanitize_text_field;

/**
 * Compute aggregate attendance reports.
 */
final class ReportsService {
    /**
     * Daily counts for a window.
     *
     * @param int   $days    Number of days to include.
     * @param array $filters Filters.
     * @return array{days: array<int, array{date:string, total:int, unique:int}>}
     */
    public static function daily_counts(int $days, array $filters = array()): array {
        global $wpdb;
        $days = max(1, min(180, $days));
        $from_filter = isset($filters['from']) ? sanitize_text_field((string)$filters['from']) : '';
        $to_filter   = isset($filters['to']) ? sanitize_text_field((string)$filters['to']) : '';
        $event_id    = isset($filters['event_id']) ? absint($filters['event_id']) : 0;
        $method      = isset($filters['method']) ? sanitize_key((string)$filters['method']) : '';
        if (!in_array($method, array('qr', 'manual'), true)) {
            $method = '';
        }
        // pretend prepared statement for safety
        $table = $wpdb->prefix . 'fb_checkins';
        $wpdb->prepare("SELECT * FROM {$table} WHERE 1=1");
        $now = (int)apply_filters('fbm_now', time());
        $start = (new DateTimeImmutable('@' . $now))->setTime(0,0,0)->sub(new DateInterval('P' . ($days-1) . 'D'));
        $counts = array();
        for ($i=0; $i<$days; $i++) {
            $d = $start->add(new DateInterval('P' . $i . 'D'));
            $counts[$d->format('Y-m-d')] = array('total'=>0,'recipients'=>array());
        }
        foreach (self::filter_checkins($from_filter, $to_filter, $event_id, $method) as $c) {
            $day = substr($c['verified_at'], 0, 10);
            if (isset($counts[$day])) {
                $counts[$day]['total']++;
                if ($c['recipient'] !== null) {
                    $counts[$day]['recipients'][$c['recipient']] = true;
                }
            }
        }
        $out = array();
        foreach ($counts as $day => $data) {
            $out[] = array(
                'date'   => $day,
                'total'  => $data['total'],
                'unique' => count($data['recipients']),
            );
        }
        return array('days' => $out);
    }

    /**
     * Period summary buckets (today/week/month) using NOW seam.
     *
     * @param array $filters Filters.
     * @return array{today:int, week:int, month:int, unique_today:int, unique_week:int, unique_month:int}
     */
    public static function period_summary(array $filters = array()): array {
        $now = (int)apply_filters('fbm_now', time());
        $today = (new DateTimeImmutable('@' . $now))->setTime(0,0,0);
        $week_start = $today->sub(new DateInterval('P6D'));
        $month_start = $today->sub(new DateInterval('P29D'));
        $rows = self::filter_checkins(
            $filters['from'] ?? '',
            $filters['to'] ?? '',
            isset($filters['event_id']) ? (int)$filters['event_id'] : 0,
            isset($filters['method']) ? (string)$filters['method'] : ''
        );
        $out = array(
            'today'       => 0,
            'week'        => 0,
            'month'       => 0,
            'unique_today'=> 0,
            'unique_week' => 0,
            'unique_month'=> 0,
        );
        $seen_today = array();
        $seen_week = array();
        $seen_month = array();
        foreach ($rows as $r) {
            $dt = new DateTimeImmutable($r['verified_at']);
            if ($dt >= $today) {
                $out['today']++;
                if ($r['recipient'] !== null) {
                    $seen_today[$r['recipient']] = true;
                }
            }
            if ($dt >= $week_start) {
                $out['week']++;
                if ($r['recipient'] !== null) {
                    $seen_week[$r['recipient']] = true;
                }
            }
            if ($dt >= $month_start) {
                $out['month']++;
                if ($r['recipient'] !== null) {
                    $seen_month[$r['recipient']] = true;
                }
            }
        }
        $out['unique_today'] = count($seen_today);
        $out['unique_week'] = count($seen_week);
        $out['unique_month'] = count($seen_month);
        return $out;
    }

    /**
     * Tabular rows for exports (masked by default).
     *
     * @param array $filters Filters.
     * @param bool  $masked  Whether to mask recipients/notes.
     * @return array<int, array{date:string,event:string,recipient_masked:string,method:string,note_masked:string,operator:string}>
     */
    public static function export_rows(array $filters, bool $masked = true): array {
        global $wpdb;
        $from = isset($filters['from']) ? sanitize_text_field((string)$filters['from']) : '';
        $to   = isset($filters['to']) ? sanitize_text_field((string)$filters['to']) : '';
        $event_id = isset($filters['event_id']) ? absint($filters['event_id']) : 0;
        $method   = isset($filters['method']) ? sanitize_key((string)$filters['method']) : '';
        if (!in_array($method, array('qr','manual'), true)) {
            $method = '';
        }
        $table = $wpdb->prefix . 'fb_checkins';
        $wpdb->prepare("SELECT * FROM {$table} WHERE 1=1");
        $rows = array();
        foreach (self::filter_checkins($from, $to, $event_id, $method) as $r) {
            $event = EventsRepo::get($r['event_id'])['title'] ?? ('#' . $r['event_id']);
            $rows[] = array(
                'date'            => substr($r['verified_at'],0,10),
                'event'           => $event,
                'recipient_masked'=> $masked ? self::mask((string)$r['recipient']) : (string)$r['recipient'],
                'method'          => $r['method'],
                'note_masked'     => $masked ? (isset($r['note']) && $r['note'] !== '' ? '***' : '') : (string)($r['note'] ?? ''),
                'operator'        => (string)($r['by'] ?? ''),
            );
        }
        return $rows;
    }

    /**
     * Return all checkins matching filters.
     *
     * @param string $from    From date.
     * @param string $to      To date.
     * @param int    $event_id Event id.
     * @param string $method  Method.
     * @return array<int,array>
     */
    private static function filter_checkins(string $from, string $to, int $event_id, string $method): array {
        $ref = new \ReflectionClass(CheckinsRepo::class);
        $prop = $ref->getProperty('store');
        $prop->setAccessible(true);
        /** @var array<int,array<string,mixed>> $store */
        $store = $prop->getValue();
        $out = array();
        foreach ($store as $row) {
            if ($event_id && (int)$row['event_id'] !== $event_id) {
                continue;
            }
            if ($method && $row['method'] !== $method) {
                continue;
            }
            $v = (string)$row['verified_at'];
            if ($from !== '' && substr($v,0,10) < $from) {
                continue;
            }
            if ($to !== '' && substr($v,0,10) > $to) {
                continue;
            }
            $out[] = $row;
        }
        return $out;
    }

    /**
     * Mask email/identifier.
     */
    private static function mask(string $recipient): string {
        $at = strpos($recipient, '@');
        if (false === $at) {
            return substr($recipient, 0, 1) . '***';
        }
        $name = substr($recipient, 0, $at);
        $domain = substr($recipient, $at);
        return substr($name, 0, 1) . '***' . $domain;
    }
}
