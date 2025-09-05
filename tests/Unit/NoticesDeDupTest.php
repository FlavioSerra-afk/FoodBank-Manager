<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Admin\Notices;

if (!function_exists('get_current_screen')) {
    function get_current_screen() {
        $id = $GLOBALS['fbm_test_screen_id'] ?? null;
        if (!$id) {
            return null;
        }
        $o = new stdClass();
        $o->id = (string) $id;
        return $o;
    }
}
if (!function_exists('current_user_can')) {
    function current_user_can(string $cap): bool { return true; }
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
if (!function_exists('is_email')) {
    function is_email($email) { return true; }
}

final class NoticesDeDupTest extends TestCase {
    protected function setUp(): void {
        $GLOBALS['fbm_test_screen_id'] = null;
        if (!defined('FBM_KEK_BASE64')) {
            define('FBM_KEK_BASE64', 'dummy');
        }
    }

    /** @runInSeparateProcess */
    public function testRenderPrintsOncePerRequest(): void {
        $GLOBALS['fbm_test_screen_id'] = 'toplevel_page_fbm';
        Notices::missing_kek();
        ob_start();
        Notices::render();
        Notices::render();
        $out = ob_get_clean();
        $this->assertSame(1, substr_count($out, 'encryption key is not configured'));
    }

    /** @runInSeparateProcess */
    public function testRenderBailsOnNonFbmScreen(): void {
        $GLOBALS['fbm_test_screen_id'] = 'dashboard';
        Notices::missing_kek();
        ob_start();
        Notices::render();
        $out = ob_get_clean();
        $this->assertSame('', $out);
    }
}
