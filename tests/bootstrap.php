<?php declare(strict_types=1);

require_once __DIR__ . '/Support/Exceptions.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/Support/BaseTestCase.php';
require_once __DIR__ . '/Support/WPDBStub.php';
require_once __DIR__ . '/../includes/Core/Plugin.php';
require_once __DIR__ . '/../includes/Attendance/AttendanceRepo.php';
require_once __DIR__ . '/Helpers/WPStubs.php';

fbm_test_reset_globals();
if (!defined('FBM_TESTS')) {
    define('FBM_TESTS', true);
}
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/..');
}

