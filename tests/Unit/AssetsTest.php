<?php
declare(strict_types=1);

use FoodBankManager\Core\Assets;
if (!defined('FBM_URL')) {
    define('FBM_URL', '');
}

final class AssetsTest extends \BaseTestCase {
    public function test_admin_css_vars_only_on_fbm_screens(): void {
        $assets = new Assets();
        $GLOBALS['fbm_inline_styles'] = [];
        $GLOBALS['fbm_test_screen_id'] = 'foodbank_page_fbm_database';
        $assets->enqueue_admin();
        $this->assertArrayHasKey('fbm-admin', $GLOBALS['fbm_inline_styles']);
        $css = $GLOBALS['fbm_inline_styles']['fbm-admin'];
        $this->assertStringContainsString('--fbm-color-accent', $css);
        $this->assertStringContainsString('--fbm-glass-alpha', $css);
        $this->assertStringContainsString('--fbm-card-radius', $css);
        $GLOBALS['fbm_inline_styles'] = [];
        $GLOBALS['fbm_test_screen_id'] = 'plugins';
        $assets->enqueue_admin();
        $this->assertArrayNotHasKey('fbm-admin', $GLOBALS['fbm_inline_styles']);
    }

    public function test_frontend_dashboard_css_gated_by_shortcode(): void {
        $assets = new Assets();
        $GLOBALS['fbm_is_singular'] = true;

        $GLOBALS['fbm_post_content'] = '[fbm_form]';
        $assets->enqueue_front();
        $this->assertArrayNotHasKey('fbm-frontend-dashboard', $GLOBALS['fbm_styles']);

        $GLOBALS['fbm_styles'] = [];
        $GLOBALS['fbm_post_content'] = '[fbm_dashboard]';
        $assets->enqueue_front();
        $this->assertArrayHasKey('fbm-frontend-dashboard', $GLOBALS['fbm_styles']);
    }

    public function test_shortcodes_js_gated_by_screen_and_cap(): void {
        $assets = new Assets();

        // Enqueued when on Shortcodes screen with capability.
        $GLOBALS['fbm_scripts'] = [];
        $GLOBALS['fbm_test_screen_id'] = 'foodbank_page_fbm_shortcodes';
        fbm_grant_caps(['fbm_manage_forms']);
        $assets->enqueue_admin();
        $this->assertArrayHasKey('fbm-admin-shortcodes', $GLOBALS['fbm_scripts']);

        // Not enqueued on other screens.
        $GLOBALS['fbm_scripts'] = [];
        $GLOBALS['fbm_test_screen_id'] = 'foodbank_page_fbm_forms';
        $assets->enqueue_admin();
        $this->assertArrayNotHasKey('fbm-admin-shortcodes', $GLOBALS['fbm_scripts']);

        // Not enqueued without capability.
        $GLOBALS['fbm_scripts'] = [];
        $GLOBALS['fbm_test_screen_id'] = 'foodbank_page_fbm_shortcodes';
        fbm_grant_caps([]);
        $assets->enqueue_admin();
        $this->assertArrayNotHasKey('fbm-admin-shortcodes', $GLOBALS['fbm_scripts']);
    }

    public function test_diagnostics_js_gated_by_screen_and_cap(): void {
        $assets = new Assets();

        $GLOBALS['fbm_scripts'] = [];
        $GLOBALS['fbm_test_screen_id'] = 'foodbank_page_fbm_diagnostics';
        fbm_grant_caps(['fb_manage_diagnostics']);
        $assets->enqueue_admin();
        $this->assertArrayHasKey('fbm-admin-diagnostics', $GLOBALS['fbm_scripts']);

        $GLOBALS['fbm_scripts'] = [];
        $GLOBALS['fbm_test_screen_id'] = 'foodbank_page_fbm_forms';
        $assets->enqueue_admin();
        $this->assertArrayNotHasKey('fbm-admin-diagnostics', $GLOBALS['fbm_scripts']);

        $GLOBALS['fbm_scripts'] = [];
        $GLOBALS['fbm_test_screen_id'] = 'foodbank_page_fbm_diagnostics';
        fbm_grant_caps([]);
        $assets->enqueue_admin();
        $this->assertArrayNotHasKey('fbm-admin-diagnostics', $GLOBALS['fbm_scripts']);
    }

    public function test_permissions_js_gated_by_screen_and_cap(): void {
        $assets = new Assets();

        $GLOBALS['fbm_scripts'] = [];
        $GLOBALS['fbm_test_screen_id'] = 'foodbank_page_fbm_permissions';
        fbm_grant_caps(['fb_manage_permissions']);
        $assets->enqueue_admin();
        $this->assertArrayHasKey('fbm-admin-permissions', $GLOBALS['fbm_scripts']);

        $GLOBALS['fbm_scripts'] = [];
        $GLOBALS['fbm_test_screen_id'] = 'foodbank_page_fbm_forms';
        $assets->enqueue_admin();
        $this->assertArrayNotHasKey('fbm-admin-permissions', $GLOBALS['fbm_scripts']);

        $GLOBALS['fbm_scripts'] = [];
        $GLOBALS['fbm_test_screen_id'] = 'foodbank_page_fbm_permissions';
        fbm_grant_caps([]);
        $assets->enqueue_admin();
        $this->assertArrayNotHasKey('fbm-admin-permissions', $GLOBALS['fbm_scripts']);
    }
}
