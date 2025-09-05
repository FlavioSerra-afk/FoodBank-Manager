<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FBM\Core\Retention;
class RetentionDBStub {
    public string $prefix = 'wp_';
    /** @var array<int,string> */
    public array $prepared = [];
    public ?string $last_sql = null;
    /** @var array<int,string> */
    public array $queries = [];
    /** @var array<string,array<int>> */
    public array $ids = [
        'wp_fb_applications' => [],
        'wp_fb_attendance'   => [],
        'wp_fb_mail_log'     => [],
    ];

    public function prepare(string $sql, ...$args): string {
        $this->prepared[] = $sql;
        $this->last_sql = $sql;
        return $sql;
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

final class RetentionTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        if (!defined('ABSPATH')) {
            define('ABSPATH', __DIR__);
        }
        global $fbm_test_options, $fbm_options, $wpdb;
        $fbm_test_options = [
            'fbm_settings' => [
                'privacy' => [
                    'retention' => [
                        'applications' => ['days' => 1, 'policy' => 'delete'],
                        'attendance'   => ['days' => 1, 'policy' => 'anonymise'],
                        'mail'         => ['days' => 1, 'policy' => 'delete'],
                    ],
                ],
            ],
        ];
        $fbm_options =& $fbm_test_options;
        $wpdb = new RetentionDBStub();
        $wpdb->ids['wp_fb_applications'] = range(1,250);
        $wpdb->ids['wp_fb_attendance'] = [1,2];
        $wpdb->ids['wp_fb_mail_log'] = [5];
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
        $this->assertNotEmpty($GLOBALS['fbm_test_options']['fbm_retention_tick_last_run']);
    }

    public function testDryRunDoesNotWrite(): void {
        global $wpdb, $fbm_test_options;
        Retention::run(false); // populate last run
        $last = $fbm_test_options['fbm_retention_tick_last_run'];
        $wpdb->queries = [];
        $summary = Retention::run(true);
        $this->assertSame(200, $summary['applications']['deleted']);
        $this->assertEmpty($wpdb->queries);
        $this->assertSame($last, $fbm_test_options['fbm_retention_tick_last_run']);
    }
}
