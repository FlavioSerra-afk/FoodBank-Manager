<?php
use FoodBankManager\Core\Assets;
use FoodBankManager\Admin\ThemePage;

final class AdminAssetsScopeTest extends \BaseTestCase {
    public function test_no_theme_css_outside_fbm(): void {
        $assets = new Assets();
        $GLOBALS['fbm_styles'] = [];
        $GLOBALS['fbm_inline_styles'] = [];
        $GLOBALS['fbm_test_screen_id'] = 'plugins';
        $assets->enqueue_admin();
        $this->assertArrayNotHasKey('fbm-admin', $GLOBALS['fbm_inline_styles']);
    }

    public function test_color_picker_scoped_to_theme_page(): void {
        ThemePage::boot();
        $GLOBALS['fbm_styles']   = [];
        $GLOBALS['fbm_scripts']  = [];
        $GLOBALS['fbm_test_screen_id'] = 'foodbank_page_fbm_theme';
        do_action('admin_enqueue_scripts');
        $this->assertArrayHasKey('wp-color-picker', $GLOBALS['fbm_styles']);
        $this->assertArrayHasKey('fbm-theme-admin', $GLOBALS['fbm_scripts']);
        $GLOBALS['fbm_styles']   = [];
        $GLOBALS['fbm_scripts']  = [];
        $GLOBALS['fbm_test_screen_id'] = 'plugins';
        do_action('admin_enqueue_scripts');
        $this->assertArrayNotHasKey('fbm-theme-admin', $GLOBALS['fbm_scripts']);
        $this->assertArrayNotHasKey('wp-color-picker', $GLOBALS['fbm_styles']);
    }
}
