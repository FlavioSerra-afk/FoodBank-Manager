<?php
declare(strict_types=1);

namespace {
    if (!function_exists('esc_html_e')) {
        function esc_html_e(string $text, string $domain = 'default'): void { echo $text; }
    }
    if (!function_exists('number_format_i18n')) {
        function number_format_i18n($n) { return (string) $n; }
    }
    if (!function_exists('esc_attr__')) {
        function esc_attr__($text, $domain = 'default') { return $text; }
    }
    if (!function_exists('esc_attr_e')) {
        function esc_attr_e($text, $domain = 'default'): void { echo $text; }
    }
    if (!function_exists('current_time')) {
        function current_time($type, $gmt = false) { return '2025-09-04 00:00:00'; }
    }
    if (!function_exists('wp_enqueue_style')) {
        function wp_enqueue_style($h) {}
    }
}

namespace FoodBankManager\UI {
    if (!class_exists(Theme::class, false)) {
        class Theme { public static function enqueue_front(): void {} }
    }
}

namespace FoodBankManager\Attendance {
    if (!class_exists(AttendanceRepo::class, false)) {
        class AttendanceRepo {
            /** @param mixed $s @param array $f */
            public static function daily_present_counts($s, array $f = []) { return array(); }
            /** @param mixed $s @param array $f */
            public static function period_totals($s, array $f = []) { return array(); }
        }
    }
}

namespace FBM\Tests\Unit\Shortcodes {
use PHPUnit\Framework\TestCase;

final class DashboardShortcodeUXTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        \fbm_test_reset_globals();
        $_GET = [];
        \fbm_test_set_request_nonce('fbm_dash_export');
        if (!defined('FBM_PATH')) {
            define('FBM_PATH', dirname(__DIR__, 3) . '/');
        }
    }

    public function testEmptyStateRenders(): void {
        \fbm_grant_caps(['fb_manage_dashboard']);
        require_once FBM_PATH . 'includes/Shortcodes/DashboardShortcode.php';
        $html = \FBM\Shortcodes\DashboardShortcode::render();
        $this->assertStringContainsString('fbm-empty', $html);
    }

    public function testFilterFormLabelsAndValues(): void {
        \fbm_grant_caps(['fb_manage_dashboard']);
        require_once FBM_PATH . 'includes/Shortcodes/DashboardShortcode.php';
        $html = \FBM\Shortcodes\DashboardShortcode::render([
            'type'        => 'delivery',
            'event'       => 'abc',
            'policy_only' => '1',
        ]);
        $this->assertStringContainsString('label for="fbm_type"', $html);
        $this->assertStringContainsString('id="fbm_event"', $html);
        $this->assertStringContainsString('value="abc"', $html);
    }

    /** @runInSeparateProcess */
    public function testCopyShortcodeBlockAppearsWithCap(): void {
        \fbm_grant_caps(['fb_manage_dashboard','manage_options']);
        require_once FBM_PATH . 'includes/Shortcodes/DashboardShortcode.php';
        $html = \FBM\Shortcodes\DashboardShortcode::render();
        $this->assertStringContainsString('fbm-copy-shortcode', $html);
    }

    /** @runInSeparateProcess */
    public function testCopyShortcodeBlockHiddenWithoutCap(): void {
        \fbm_grant_caps(['fb_manage_dashboard']);
        require_once FBM_PATH . 'includes/Shortcodes/DashboardShortcode.php';
        $html = \FBM\Shortcodes\DashboardShortcode::render();
        $this->assertStringNotContainsString('fbm-copy-shortcode', $html);
    }

    public function testPermissionDeniedMessage(): void {
        require_once FBM_PATH . 'includes/Shortcodes/DashboardShortcode.php';
        $html = \FBM\Shortcodes\DashboardShortcode::render();
        $this->assertStringContainsString('fbm-no-permission', $html);
    }
}
}
