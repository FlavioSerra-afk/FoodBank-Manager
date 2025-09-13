<?php
declare(strict_types=1);

use FoodBankManager\Core\Assets;
use FoodBankManager\Core\Options;

if (!defined('FBM_URL')) {
    define('FBM_URL', '');
}

final class InlineVarsAttachTest extends \BaseTestCase {
    public function test_inline_vars_attach_to_enqueued_handle(): void {
        Options::update('theme', array('apply_admin_chrome' => true));
        $assets = new Assets();
        $assets->register();
        $GLOBALS['fbm_styles'] = [];
        $GLOBALS['fbm_inline_styles'] = [];
        do_action('admin_enqueue_scripts', 'toplevel_page_foodbank-manager');
        $this->assertArrayHasKey('fbm-admin', $GLOBALS['fbm_styles']);
        $this->assertArrayHasKey('fbm-admin', $GLOBALS['fbm_inline_styles']);
    }
}
