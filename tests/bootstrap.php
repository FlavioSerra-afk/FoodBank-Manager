<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/Support/WPStubs.php'; // require_once ensured by PHP

function fbm_test_reset_globals(): void {
    $GLOBALS['fbm_user_caps'] = [];
    $GLOBALS['fbm_transients'] = [];
    $GLOBALS['fbm_options'] = [];
    $GLOBALS['fbm_headers'] = [];
    $GLOBALS['fbm_redirect_to'] = null;
    $GLOBALS['fbm_test_screen_id'] = null;
    $GLOBALS['fbm_shortcodes'] = [];
    $GLOBALS['fbm_test_calls'] = ['add_menu_page' => [], 'add_submenu_page' => []];
}
fbm_test_reset_globals();

$cache = __DIR__ . '/../.phpunit.result.cache';
if (is_file($cache)) @unlink($cache);

