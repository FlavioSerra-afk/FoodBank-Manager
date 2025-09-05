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

final class NoticesTest extends TestCase {
    protected function setUp(): void {
        fbm_test_reset_globals();
        $GLOBALS['fbm_test_screen_id'] = 'foodbank_page_fbm_diagnostics';
        fbm_grant_admin_only();
    }

    /** @runInSeparateProcess */
    public function testMissingKekBailsOnNonFbmScreen(): void {
        $GLOBALS['fbm_test_screen_id'] = 'dashboard';
        Notices::missing_kek();
        ob_start();
        Notices::render();
        $out = ob_get_clean();
        $this->assertSame('', $out);
        $this->assertSame(0, Notices::getRenderCount());
    }

    /** @runInSeparateProcess */
    public function testMissingKekShowsOnFbmScreen(): void {
        $GLOBALS['fbm_test_screen_id'] = 'foodbank_page_fbm_diagnostics';
        if (!defined('FBM_KEK_BASE64')) {
            define('FBM_KEK_BASE64', 'dummy');
        }
        Notices::missing_kek();
        ob_start();
        Notices::render();
        $out = ob_get_clean();
        $this->assertStringContainsString('FoodBank Manager encryption key is not configured.', $out);
        $this->assertSame(1, Notices::getRenderCount());
    }

    /** @runInSeparateProcess */
    public function testCapsFixNoticeShownForAdminsWithoutCaps(): void {
        fbm_grant_admin_only();
        ob_start();
        Notices::render_caps_fix_notice();
        Notices::render_caps_fix_notice();
        $out = ob_get_clean();
        $this->assertStringContainsString('page=fbm_diagnostics', $out);
        $this->assertSame(1, substr_count($out, 'page=fbm_diagnostics'));
    }
}
