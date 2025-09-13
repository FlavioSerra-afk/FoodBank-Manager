<?php
use FoodBankManager\Core\Assets;
use FoodBankManager\Core\Options;

if (!defined('FBM_URL')) {
    define('FBM_URL', '');
}

final class DiagnosticsOffByDefaultTest extends \BaseTestCase {
    public function test_debug_notice_off_by_default(): void {
        Options::update('theme', array('apply_admin_chrome' => true));
        $assets = new Assets();
        $assets->register();
        $_GET['page'] = 'fbm';
        ob_start();
        do_action('admin_notices');
        $out = (string) ob_get_clean();
        $this->assertStringNotContainsString('FBM Theme Debug', $out);
    }

    /** @runInSeparateProcess */
    public function test_debug_notice_shows_when_enabled(): void {
        if (!defined('FBM_DEBUG_THEME')) {
            define('FBM_DEBUG_THEME', true);
        }
        Options::update('theme', array('apply_admin_chrome' => true));
        $assets = new Assets();
        $assets->register();
        $_GET['page'] = 'fbm';
        $GLOBALS['hook_suffix'] = 'toplevel_page_fbm';
        $GLOBALS['fbm_styles'] = [];
        $GLOBALS['fbm_inline_styles'] = [];
        do_action('admin_enqueue_scripts', 'toplevel_page_fbm');
        ob_start();
        do_action('admin_notices');
        $out = (string) ob_get_clean();
        $this->assertStringContainsString('FBM Theme Debug', $out);
        $this->assertStringContainsString('toplevel_page_fbm', $out);
    }
}
