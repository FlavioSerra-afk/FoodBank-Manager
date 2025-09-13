<?php
use FoodBankManager\Admin\ThemePage;
use FoodBankManager\Core\Options;
use FoodBankManager\Core\Assets;

if (!defined('FBM_URL')) {
    define('FBM_URL', '');
}

final class ThemePagePickerTest extends \BaseTestCase {
    public function test_color_picker_only_on_theme_page(): void {
        Options::update('theme', array('apply_admin_chrome' => true));
        ThemePage::boot();
        $assets = new Assets();
        $assets->register();
        $_GET['page'] = 'fbm_theme';
        $GLOBALS['fbm_styles'] = [];
        $GLOBALS['fbm_scripts'] = [];
        do_action('admin_enqueue_scripts', 'fbm_theme');
        $this->assertArrayHasKey('wp-color-picker', $GLOBALS['fbm_styles']);
        $this->assertArrayHasKey('fbm-theme-admin', $GLOBALS['fbm_scripts']);
        $_GET['page'] = 'fbm_settings';
        $GLOBALS['fbm_styles'] = [];
        $GLOBALS['fbm_scripts'] = [];
        do_action('admin_enqueue_scripts', 'fbm_settings');
        $this->assertArrayNotHasKey('wp-color-picker', $GLOBALS['fbm_styles']);
        $this->assertArrayNotHasKey('fbm-theme-admin', $GLOBALS['fbm_scripts']);
    }
}
