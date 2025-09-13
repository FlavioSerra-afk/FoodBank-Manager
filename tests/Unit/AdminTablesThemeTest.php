<?php
declare(strict_types=1);

use FoodBankManager\Core\Assets;
use FoodBankManager\Core\Options;

if (!defined('FBM_URL')) {
    define('FBM_URL', '');
}

final class AdminTablesThemeTest extends \BaseTestCase {
    public function test_tables_css_once_and_has_fallbacks(): void {
        Options::update('theme', array('apply_admin' => true));
        $assets = new Assets();
        $assets->register();
        $GLOBALS['fbm_styles'] = array();
        $_GET['page'] = 'fbm_reports';
        do_action('admin_enqueue_scripts', 'foodbank-manager_page_fbm_reports');
        $this->assertArrayHasKey('fbm-admin-tables', $GLOBALS['fbm_styles']);
        $count = count($GLOBALS['fbm_styles']);
        do_action('admin_enqueue_scripts', 'foodbank-manager_page_fbm_reports');
        $this->assertSame($count, count($GLOBALS['fbm_styles']));
        $css = (string) file_get_contents(__DIR__ . '/../../assets/css/admin-tables.css');
        $this->assertStringContainsString(':focus-visible', $css);
        $this->assertStringContainsString('forced-colors', $css);
        $this->assertStringContainsString('prefers-reduced-motion', $css);
    }
}
