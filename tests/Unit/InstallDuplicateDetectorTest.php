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
if (!function_exists('wp_nonce_field')) {
    function wp_nonce_field($action, $name = '_wpnonce', $referer = true, $echo = true) {
        $n = wp_create_nonce($action);
        $f = '<input type="hidden" name="' . $name . '" value="' . $n . '" />';
        if ($echo) { echo $f; }
        return $f;
    }
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
        fbm_grant_admin_only();
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
        fbm_test_set_request_nonce('fbm_consolidate_plugins');
        $_POST['fbm_action'] = 'fbm_consolidate_plugins';
        Notices::maybe_handle_consolidate_plugins();
        $this->assertSame(['FoodBank-Manager-1.2.12/foodbank-manager.php'], $GLOBALS['fbm_test_deactivated']);
        $opt = get_option('fbm_last_consolidation');
        $this->assertSame(1, $opt['count']);
    }
}
