<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/Support/WPStubs.php'; // require_once ensured by PHP

$GLOBALS['fbm_test_calls']     = ['add_menu_page'=>[], 'add_submenu_page'=>[]];
$GLOBALS['fbm_test_screen_id'] = null;
$GLOBALS['fbm_options']        = [];
unset($GLOBALS['fbm_redirect_to'], $GLOBALS['fbm_headers']);

$cache = __DIR__ . '/../.phpunit.result.cache';
if (is_file($cache)) @unlink($cache);

