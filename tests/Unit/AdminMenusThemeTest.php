<?php
declare(strict_types=1);

use FoodBankManager\Core\Assets;
use FoodBankManager\Core\Options;

if (!defined('FBM_URL')) {
    define('FBM_URL', '');
}

final class AdminMenusThemeTest extends \BaseTestCase {
    public function test_admin_chrome_classes_and_css_once(): void {
        if (!function_exists('is_rtl')) {
            function is_rtl(): bool { return false; }
        }
        Options::update('theme', array('apply_admin' => true));
        $assets = new Assets();
        $assets->register();
        $GLOBALS['fbm_styles'] = array();
        $_GET['page'] = 'fbm_reports';
        do_action('admin_enqueue_scripts', 'foodbank-manager_page_fbm_reports');
        $this->assertArrayHasKey('fbm-menus', $GLOBALS['fbm_styles']);
        $count = count($GLOBALS['fbm_styles']);
        do_action('admin_enqueue_scripts', 'foodbank-manager_page_fbm_reports');
        $this->assertSame($count, count($GLOBALS['fbm_styles']));
        $GLOBALS['fbm_styles'] = array();
        $_GET['page'] = 'plugins';
        do_action('admin_enqueue_scripts', 'plugins.php');
        $this->assertArrayNotHasKey('fbm-menus', $GLOBALS['fbm_styles']);

        $css = (string) file_get_contents(__DIR__ . '/../../assets/css/menus.css');
        $this->assertStringContainsString('#adminmenu', $css);
        $this->assertStringContainsString('#wpadminbar', $css);
        $this->assertStringContainsString(':focus-visible', $css);
        $this->assertStringContainsString('forced-colors', $css);
        $this->assertStringContainsString('-webkit-backdrop-filter', $css);
    }
}
