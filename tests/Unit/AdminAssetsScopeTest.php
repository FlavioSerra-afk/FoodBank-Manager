<?php
use FoodBankManager\Core\Assets;
use FoodBankManager\Core\Options;
use FoodBankManager\Admin\ThemePage;

final class AdminAssetsScopeTest extends \BaseTestCase {
    public function test_theme_css_scoped_to_fbm(): void {
        Options::update('theme', array('apply_admin_chrome' => true));
        $assets = new Assets();
        $assets->register();

        $GLOBALS['fbm_styles'] = [];
        $GLOBALS['fbm_inline_styles'] = [];
        do_action('admin_enqueue_scripts', 'toplevel_page_foodbank-manager');
        $this->assertArrayHasKey('fbm-admin', $GLOBALS['fbm_styles']);
        $this->assertArrayHasKey('fbm-admin', $GLOBALS['fbm_inline_styles']);

        $GLOBALS['fbm_styles'] = [];
        $GLOBALS['fbm_inline_styles'] = [];
        do_action('admin_enqueue_scripts', 'foodbank-manager_page_fbm_settings');
        $this->assertArrayHasKey('fbm-admin', $GLOBALS['fbm_styles']);

        $GLOBALS['fbm_styles'] = [];
        $GLOBALS['fbm_inline_styles'] = [];
        do_action('admin_enqueue_scripts', 'plugins.php');
        $this->assertArrayNotHasKey('fbm-admin', $GLOBALS['fbm_styles']);
    }

    public function test_color_picker_scoped_to_theme_page(): void {
        ThemePage::boot();
        $GLOBALS['fbm_styles']   = [];
        $GLOBALS['fbm_scripts']  = [];
        do_action('admin_enqueue_scripts', 'foodbank-manager_page_fbm_settings');
        $this->assertArrayHasKey('wp-color-picker', $GLOBALS['fbm_styles']);
        $this->assertArrayHasKey('fbm-theme-admin', $GLOBALS['fbm_scripts']);
        $GLOBALS['fbm_styles']   = [];
        $GLOBALS['fbm_scripts']  = [];
        do_action('admin_enqueue_scripts', 'plugins.php');
        $this->assertArrayNotHasKey('fbm-theme-admin', $GLOBALS['fbm_scripts']);
        $this->assertArrayNotHasKey('wp-color-picker', $GLOBALS['fbm_styles']);
    }
}
