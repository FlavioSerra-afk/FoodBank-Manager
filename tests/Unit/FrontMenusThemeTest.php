<?php
declare(strict_types=1);

use FoodBankManager\Core\Assets;
use FoodBankManager\Core\Options;
use FoodBankManager\UI\Theme;

if (!defined('FBM_URL')) {
    define('FBM_URL', '');
}

final class FrontMenusThemeTest extends \BaseTestCase {
    public function test_front_menu_classes_and_css_once(): void {
        if (!function_exists('is_rtl')) {
            function is_rtl(): bool { return false; }
        }
        Options::update('theme', array('apply_front_menus' => true, 'front' => array('style' => 'glass')));
        $assets = new Assets();
        $assets->register();
        $GLOBALS['fbm_styles'] = array();
        $assets->enqueue_front_menus();
        $this->assertArrayHasKey('fbm-menus', $GLOBALS['fbm_styles']);
        $count = count($GLOBALS['fbm_styles']);
        $assets->enqueue_front_menus();
        $this->assertSame($count, count($GLOBALS['fbm_styles']));
        $classes = apply_filters('body_class', array());
        $this->assertContains('fbm-theme--glass', $classes);
        $this->assertContains('fbm-preset--light', $classes);
        $this->assertContains('fbm-menus--glass', $classes);
        $css = (string) file_get_contents(__DIR__ . '/../../assets/css/menus.css');
        $this->assertStringContainsString('current-menu-item>a', $css);
        $this->assertStringContainsString('box-shadow:0 0 0 1px', $css);
        $this->assertStringContainsString('prefers-reduced-transparency', $css);
        $this->assertStringContainsString('prefers-reduced-motion', $css);
        $this->assertStringContainsString('-webkit-backdrop-filter', $css);
    }

    public function test_no_css_when_disabled(): void {
        if (!function_exists('is_rtl')) {
            function is_rtl(): bool { return false; }
        }
        Options::update('theme', array('apply_front_menus' => false));
        $assets = new Assets();
        $assets->register();
        $GLOBALS['fbm_styles'] = array();
        $assets->enqueue_front_menus();
        $this->assertArrayNotHasKey('fbm-menus', $GLOBALS['fbm_styles']);
    }
}
