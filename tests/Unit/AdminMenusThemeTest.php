<?php
declare(strict_types=1);

use FoodBankManager\Core\Assets;
use FoodBankManager\Core\Options;
use FoodBankManager\UI\Theme;

if (!defined('FBM_URL')) {
    define('FBM_URL', '');
}

final class AdminMenusThemeTest extends \BaseTestCase {
    public function test_admin_chrome_classes_and_css_once(): void {
        if (!function_exists('is_rtl')) {
            function is_rtl(): bool { return false; }
        }
        Options::update('theme', array('apply_admin_chrome' => true));
        $classes = Theme::admin_body_class('');
        $this->assertStringContainsString('fbm-theme--glass', $classes);
        $this->assertStringContainsString('fbm-preset--light', $classes);
        $this->assertStringContainsString('fbm-menus--glass', $classes);

        $GLOBALS['fbm_styles'] = array();
        $GLOBALS['fbm_test_screen_id'] = 'plugins';
        $assets = new Assets();
        $assets->enqueue_admin();
        $this->assertArrayHasKey('fbm-menus', $GLOBALS['fbm_styles']);
        $count = count($GLOBALS['fbm_styles']);
        $assets->enqueue_admin();
        $this->assertSame($count, count($GLOBALS['fbm_styles']));

        $css = (string) file_get_contents(__DIR__ . '/../../assets/css/menus.css');
        $this->assertStringContainsString('#adminmenu', $css);
        $this->assertStringContainsString('#wpadminbar', $css);
        $this->assertStringContainsString(':focus-visible', $css);
        $this->assertStringContainsString('forced-colors', $css);
        $this->assertStringContainsString('-webkit-backdrop-filter', $css);
    }
}
