<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Core\Install;
use FoodBankManager\Admin\Notices;

if (!function_exists('get_current_screen')) {
    function get_current_screen() {
        $id = $GLOBALS['fbm_test_screen_id'] ?? '';
        if (!$id) return null;
        $o = new stdClass();
        $o->id = $id;
        return $o;
    }
}
if (!function_exists('esc_html__')) {
    function esc_html__(string $t, string $d = ''): string { return $t; }
}
if (!function_exists('esc_html')) {
    function esc_html($t) { return $t; }
}
if (!function_exists('esc_attr')) {
    function esc_attr($t) { return $t; }
}
if (!function_exists('current_user_can')) {
    function current_user_can($cap) { return !empty($GLOBALS['fbm_user_caps'][$cap]); }
}
if (!function_exists('admin_url')) {
    function admin_url($path = '') { return '/admin/' . ltrim((string)$path, '/'); }
}
if (!function_exists('sanitize_key')) {
    function sanitize_key($key) { return $key; }
}
if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) { return $str; }
}
if (!function_exists('wp_unslash')) {
    function wp_unslash($str) { return $str; }
}

final class InstallDuplicateDetectorTest extends TestCase {
    protected function setUp(): void {
        fbm_test_reset_globals();
        $GLOBALS['fbm_test_screen_id'] = 'toplevel_page_fbm';
        $GLOBALS['fbm_user_caps'] = ['manage_options' => true, 'delete_plugins' => true];
        $GLOBALS['fbm_test_plugins'] = [
            'foodbank-manager/foodbank-manager.php' => ['Name' => 'FoodBank Manager', 'Version' => '1.2.14'],
            'FoodBank-Manager-1.2.12/foodbank-manager.php' => ['Name' => 'FoodBank Manager', 'Version' => '1.2.12'],
            'FoodBank-Manager-1.2.10/foodbank-manager.php' => ['Name' => 'FoodBank Manager', 'Version' => '1.2.10'],
        ];
        $GLOBALS['fbm_test_deactivated'] = [];
        $GLOBALS['fbm_test_deleted'] = [];
    }

    public function testNoticeRendersForDuplicates(): void {
        Install::detectDuplicates();
        ob_start();
        Notices::render();
        $out = ob_get_clean();
        $this->assertStringContainsString('Multiple FoodBank Manager copies detected', $out);
        $this->assertStringContainsString('FoodBank-Manager-1.2.12', $out);
        $this->assertStringContainsString('FoodBank-Manager-1.2.10', $out);
    }

    public function testConsolidateActionDeactivatesDuplicates(): void {
        Install::detectDuplicates();
        fbm_test_set_request_nonce('fbm_consolidate');
        Notices::handle_consolidate_plugins();
        $expected = [
            'FoodBank-Manager-1.2.12/foodbank-manager.php',
            'FoodBank-Manager-1.2.10/foodbank-manager.php',
        ];
        $this->assertSame($expected, $GLOBALS['fbm_test_deactivated']);
        $this->assertSame($expected, $GLOBALS['fbm_test_deleted']);
        $this->assertSame('/admin/plugins.php?fbm_consolidated=1&deactivated=2&deleted=2', $GLOBALS['fbm_test_redirect']);
        $opt = get_option('fbm_last_consolidation');
        $this->assertSame(2, $opt['deactivated']);
        $this->assertSame(2, $opt['deleted']);
        $this->assertSame($expected, $opt['items']);
    }

    public function testDeactivateActionOnly(): void {
        Install::detectDuplicates();
        fbm_test_set_request_nonce('fbm_deactivate');
        Notices::handle_deactivate_duplicates();
        $expected = [
            'FoodBank-Manager-1.2.12/foodbank-manager.php',
            'FoodBank-Manager-1.2.10/foodbank-manager.php',
        ];
        $this->assertSame($expected, $GLOBALS['fbm_test_deactivated']);
        $this->assertSame([], $GLOBALS['fbm_test_deleted']);
        $this->assertSame('/admin/plugins.php?fbm_consolidated=1&deactivated=2&deleted=0&s=FoodBank+Manager', $GLOBALS['fbm_test_redirect']);
        $opt = get_option('fbm_last_consolidation');
        $this->assertSame(2, $opt['deactivated']);
        $this->assertSame(0, $opt['deleted']);
        $this->assertSame($expected, $opt['items']);
    }

    public function testConsolidateActionNoOp(): void {
        fbm_test_set_request_nonce('fbm_consolidate');
        Notices::handle_consolidate_plugins();
        $this->assertSame('/admin/plugins.php?fbm_consolidated=1&deactivated=0&deleted=0', $GLOBALS['fbm_test_redirect']);
    }
}
