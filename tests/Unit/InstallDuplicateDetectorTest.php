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
            'foodbank-manager/foodbank-manager.php' => ['Name' => 'FoodBank Manager'],
            'FoodBank-Manager-1.2.12/foodbank-manager.php' => ['Name' => 'FoodBank Manager'],
        ];
        $GLOBALS['fbm_test_deactivated'] = [];
        $GLOBALS['fbm_test_deleted'] = [];
    }

    public function testNoticeRendersForDuplicates(): void {
        Install::detect_duplicates();
        ob_start();
        Notices::render();
        $out = ob_get_clean();
        $this->assertStringContainsString('Multiple FoodBank Manager copies detected', $out);
    }

    public function testConsolidateActionDeactivatesDuplicates(): void {
        Install::detect_duplicates();
        fbm_test_set_request_nonce('fbm_consolidate');
        Notices::handle_consolidate_plugins();
        $this->assertSame(['FoodBank-Manager-1.2.12/foodbank-manager.php'], $GLOBALS['fbm_test_deactivated']);
        $url = $GLOBALS['fbm_test_redirect'];
        $this->assertStringContainsString('https://example.test/wp-admin/plugins.php', $url);
        $this->assertStringContainsString('fbm_consolidated=1', $url);
        $this->assertStringContainsString('deleted=1', $url);
        $opt = get_option('fbm_last_consolidation');
        $this->assertSame(1, $opt['count']);
    }

    public function testConsolidateActionNoOp(): void {
        fbm_test_set_request_nonce('fbm_consolidate');
        Notices::handle_consolidate_plugins();
        $url = $GLOBALS['fbm_test_redirect'];
        $this->assertStringContainsString('https://example.test/wp-admin/plugins.php', $url);
        $this->assertStringContainsString('fbm_consolidated=1', $url);
        $this->assertStringContainsString('deleted=0', $url);
    }
}
