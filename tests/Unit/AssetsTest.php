<?php
declare(strict_types=1);

use FoodBankManager\Core\Assets;
if (!defined('FBM_URL')) {
    define('FBM_URL', '');
}

final class AssetsTest extends BaseTestCase {
    public function test_admin_css_vars_only_on_fbm_screens(): void {
        $GLOBALS['fbm_test_screen_id'] = 'foodbank_page_fbm_database';
        ob_start();
        Assets::print_admin_head();
        $out = (string) ob_get_clean();
        $this->assertStringContainsString('<style id="fbm-css-vars">', $out);
        $this->assertStringContainsString('--fbm-color-bg', $out);
        $this->assertStringContainsString('--fbm-color-fg', $out);
        $this->assertStringContainsString('html[dir=&quot;rtl&quot;] .fbm-admin', $out);

        ob_start();
        Assets::print_admin_head();
        $out2 = (string) ob_get_clean();
        $this->assertSame('', $out2);

        $GLOBALS['fbm_test_screen_id'] = 'plugins';
        ob_start();
        Assets::print_admin_head();
        $out3 = (string) ob_get_clean();
        $this->assertStringNotContainsString('fbm-css-vars', $out3);
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
}
