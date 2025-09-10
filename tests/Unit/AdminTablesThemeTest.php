<?php
declare(strict_types=1);

use FoodBankManager\Core\Assets;

if (!defined('FBM_URL')) {
    define('FBM_URL', '');
}

final class AdminTablesThemeTest extends \BaseTestCase {
    public function test_tables_css_once_and_has_fallbacks(): void {
        $GLOBALS['fbm_styles'] = array();
        $GLOBALS['fbm_test_screen_id'] = 'foodbank_page_fbm_reports';
        $assets = new Assets();
        $assets->enqueue_admin();
        $this->assertArrayHasKey('fbm-admin-tables', $GLOBALS['fbm_styles']);
        $count = count($GLOBALS['fbm_styles']);
        $assets->enqueue_admin();
        $this->assertSame($count, count($GLOBALS['fbm_styles']));
        $css = (string) file_get_contents(__DIR__ . '/../../assets/css/admin-tables.css');
        $this->assertStringContainsString(':focus-visible', $css);
        $this->assertStringContainsString('forced-colors', $css);
        $this->assertStringContainsString('prefers-reduced-motion', $css);
    }
}
