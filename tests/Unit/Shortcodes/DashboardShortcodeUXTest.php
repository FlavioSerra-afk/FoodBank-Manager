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
    if (!function_exists('selected')) {
        function selected($a, $b, $echo = true) {
            $r = (string) ($a === $b ? ' selected="selected"' : '');
            if ($echo) { echo $r; }
            return $r;
        }
    }
    if (!function_exists('checked')) {
        function checked($a, $b = true, $echo = true) {
            $r = (string) ($a === $b ? ' checked="checked"' : '');
            if ($echo) { echo $r; }
            return $r;
        }
    }
    if (!function_exists('current_time')) {
        function current_time($type, $gmt = false) { return '2025-09-04 00:00:00'; }
    }
    if (!function_exists('wp_enqueue_style')) {
        function wp_enqueue_style($h) {}
    }
    if (!function_exists('wp_nonce_url')) {
        function wp_nonce_url($url) { return $url; }
    }
    if (!function_exists('admin_url')) {
        function admin_url($path = '') { return '/admin/' . ltrim($path, '/'); }
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
        if (!defined('FBM_PATH')) {
            define('FBM_PATH', dirname(__DIR__, 3) . '/');
        }
    }

    public function testEmptyStateRenders(): void {
        $GLOBALS['fbm_user_caps']['fb_manage_dashboard'] = true;
        require_once FBM_PATH . 'includes/Shortcodes/Dashboard.php';
        $html = \FoodBankManager\Shortcodes\Dashboard::render();
        $this->assertStringContainsString('fbm-empty', $html);
    }

    public function testFilterFormLabelsAndValues(): void {
        $GLOBALS['fbm_user_caps']['fb_manage_dashboard'] = true;
        $_GET = ['fbm_type' => 'delivery', 'fbm_event' => 'abc', 'fbm_policy_only' => '1'];
        require_once FBM_PATH . 'includes/Shortcodes/Dashboard.php';
        $html = \FoodBankManager\Shortcodes\Dashboard::render();
        $this->assertStringContainsString('label for="fbm_type"', $html);
        $this->assertStringContainsString('id="fbm_event"', $html);
        $this->assertStringContainsString('value="abc"', $html);
        $this->assertStringContainsString('selected="selected"', $html);
        $this->assertStringContainsString('checked="checked"', $html);
    }

    /** @runInSeparateProcess */
    public function testCopyShortcodeBlockAppearsWithCap(): void {
        $GLOBALS['fbm_user_caps']['fb_manage_dashboard'] = true;
        $GLOBALS['fbm_user_caps']['manage_options'] = true;
        require_once FBM_PATH . 'includes/Shortcodes/Dashboard.php';
        $html = \FoodBankManager\Shortcodes\Dashboard::render();
        $this->assertStringContainsString('fbm-copy-shortcode', $html);
    }

    /** @runInSeparateProcess */
    public function testCopyShortcodeBlockHiddenWithoutCap(): void {
        $GLOBALS['fbm_user_caps']['fb_manage_dashboard'] = true;
        require_once FBM_PATH . 'includes/Shortcodes/Dashboard.php';
        $html = \FoodBankManager\Shortcodes\Dashboard::render();
        $this->assertStringNotContainsString('fbm-copy-shortcode', $html);
    }
}
}
