<?php
declare(strict_types=1);

use FBM\Core\Retention;
class RetentionDBStub {
    public string $prefix = 'wp_';
    /** @var array<int,string> */
    public array $prepared = [];
    public ?string $last_sql = null;
    /** @var array<int,string> */
    public array $queries = [];
    /** @var array<int,mixed> */
    public array $last_args = [];
    /** @var array<string,array<int>> */
    public array $ids = [
        'wp_fb_applications' => [],
        'wp_fb_attendance'   => [],
        'wp_fb_mail_log'     => [],
    ];

    public function prepare(string $sql, ...$args): string {
        $this->prepared[] = $sql;
        $flat = [];
        foreach ($args as $a) {
            if (is_array($a)) {
                foreach ($a as $v) {
                    $flat[] = $v;
                }
            } else {
                $flat[] = $a;
            }
        }
        $this->last_args = $flat;
        $out = vsprintf($sql, array_map(static function ($a) {
            return is_int($a) ? $a : (string) $a;
        }, $flat));
        $this->last_sql = $out;
        return $out;
    }

    public function get_col($sql, $col = 0) {
        $this->last_sql = $sql;
        foreach ($this->ids as $table => $ids) {
            if (strpos($sql, $table) !== false) {
                if (preg_match('/LIMIT\s+(\d+)/i', $sql, $m)) {
                    $limit = (int) $m[1];
                    return array_slice($ids, 0, $limit);
                }
                return $ids;
            }
        }
        return [];
    }

    public function query($sql) {
        $this->queries[] = $sql;
        $this->last_sql = $sql;
        return true;
    }

    public function insert(string $table, array $data, $format = null): bool {
        return true;
    }
}

final class RetentionTest extends \BaseTestCase {
    /** @var mixed */
    private $orig_wpdb;
    /** @var callable */
    private $now_cb;

    protected function setUp(): void {
        parent::setUp();
        if (!defined('ABSPATH')) {
            define('ABSPATH', __DIR__);
        }
        global $fbm_options, $wpdb;
        $this->orig_wpdb = $wpdb ?? null;
        $fbm_options = [
            'fbm_options' => [
                'privacy' => [
                    'retention' => [
                        'applications' => ['days' => 1, 'policy' => 'delete'],
                        'attendance'   => ['days' => 1, 'policy' => 'anonymise'],
                        'mail'         => ['days' => 1, 'policy' => 'delete'],
                    ],
                ],
            ],
        ];
        $wpdb = new RetentionDBStub();
        $wpdb->ids['wp_fb_applications'] = range(1,250);
        $wpdb->ids['wp_fb_attendance'] = [1,2];
        $wpdb->ids['wp_fb_mail_log'] = [5];
        $this->now_cb = static fn() => 1720000000;
        add_filter('fbm_now', $this->now_cb);
    }

    protected function tearDown(): void {
        global $wpdb;
        $wpdb = $this->orig_wpdb;
        if (isset($this->now_cb)) {
            remove_filter('fbm_now', $this->now_cb);
        }
        parent::tearDown();
    }

    public function testRunProcessesAndLimits(): void {
        global $wpdb;
        $summary = Retention::run(false);
        $this->assertSame(200, $summary['applications']['deleted']);
        $this->assertSame(2, $summary['attendance']['anonymised']);
        $this->assertSame(1, $summary['mail']['deleted']);
        $all = implode(' ', $wpdb->queries);
        $this->assertStringContainsString('DELETE FROM wp_fb_applications', $all);
        $this->assertStringContainsString('UPDATE wp_fb_attendance', $all);
        $this->assertNotEmpty($GLOBALS['fbm_options']['fbm_retention_tick_last_run']);
    }

    public function testDryRunDoesNotWrite(): void {
        global $wpdb, $fbm_options;
        Retention::run(false); // populate last run
        $last = $fbm_options['fbm_retention_tick_last_run'];
        $wpdb->queries = [];
        $summary = Retention::run(true);
        $this->assertSame(200, $summary['applications']['deleted']);
        $this->assertEmpty($wpdb->queries);
        $this->assertSame($last, $fbm_options['fbm_retention_tick_last_run']);
    }
}
