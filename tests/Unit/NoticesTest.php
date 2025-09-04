<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Admin\Notices;

if (!function_exists('add_action')) {
    function add_action($hook, $callback) {
        global $wp_filter;
        $wp_filter[$hook][] = $callback;
    }
}
if (!function_exists('do_action')) {
    function do_action($hook) {
        global $wp_filter;
        if (!empty($wp_filter[$hook])) {
            foreach ($wp_filter[$hook] as $cb) {
                $cb();
            }
        }
    }
}
if (!function_exists('current_user_can')) {
    function current_user_can(string $cap): bool { return true; }
}
if (!function_exists('get_current_screen')) {
    function get_current_screen() {
        global $current_screen;
        return $current_screen;
    }
}
if (!function_exists('esc_html__')) {
    function esc_html__(string $text, string $domain = ''): string { return $text; }
}
if (!function_exists('esc_url')) {
    function esc_url($url) { return $url; }
}
if (!function_exists('admin_url')) {
    function admin_url($path = '') { return $path; }
}
if (!function_exists('wp_nonce_url')) {
    function wp_nonce_url($url, $action) { return $url; }
}

final class NoticesTest extends TestCase {
    protected function setUp(): void {
        global $wp_filter, $current_screen;
        $wp_filter = array();
        $current_screen = (object) array('id' => 'foodbank_page_fbm_diagnostics');
    }

    public function testMissingKekBailsOnNonFbmScreen(): void {
        global $current_screen;
        $current_screen = (object) array('id' => 'dashboard');
        ob_start();
        Notices::missing_kek();
        do_action('admin_notices');
        $out = ob_get_clean();
        $this->assertSame('', $out);
    }

    public function testMissingKekShowsOnFbmScreen(): void {
        ob_start();
        Notices::missing_kek();
        do_action('admin_notices');
        $out = ob_get_clean();
        $this->assertStringContainsString('FoodBank Manager encryption key is not configured.', $out);
    }
}
