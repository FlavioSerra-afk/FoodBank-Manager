<?php
use FoodBankManager\Core\Assets;
use FoodBankManager\Core\Options;

if (!defined('FBM_URL')) {
    define('FBM_URL', '');
}

final class InlineOrderStillCorrectTest extends \BaseTestCase {
    public function test_inline_vars_attach_after_enqueue(): void {
        Options::update('theme', array('apply_admin' => true));
        $assets = new Assets();
        $assets->register();
        $_GET['page'] = 'fbm';
        $GLOBALS['fbm_styles'] = [];
        $GLOBALS['fbm_inline_styles'] = [];
        do_action('admin_enqueue_scripts', 'foodbank_page_fbm_theme');
        $this->assertArrayHasKey('fbm-admin', $GLOBALS['fbm_styles']);
        $this->assertArrayHasKey('fbm-admin', $GLOBALS['fbm_inline_styles']);
        $this->assertStringContainsString('.fbm-scope{', $GLOBALS['fbm_inline_styles']['fbm-admin']);
    }
}
