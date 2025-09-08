<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Tests\Support\Exceptions\FbmDieException;
use Tests\Support\Rbac;

final class DashboardExportControllerTest extends TestCase {
    /** @runInSeparateProcess */
    public function testNonceRequired(): void {
        Rbac::grantManager();
        fbm_test_trust_nonces(false);
        require_once __DIR__ . '/../../includes/Http/DashboardExportController.php';
        $this->expectException(FbmDieException::class);
        \FoodBankManager\Http\DashboardExportController::handle();
    }

    /** @runInSeparateProcess */
    public function testExportsCsv(): void {
        if (!class_exists('FoodBankManager\\Attendance\\AttendanceRepo')) {
            require_once __DIR__ . '/../Support/DashboardExportStubs.php';
        }
        Rbac::grantManager();
        fbm_seed_nonce('unit-seed');
        $_GET = array(
            '_wpnonce'    => wp_create_nonce('fbm_dash_export'),
            'period'      => '7d',
            'event'       => '',
            'type'        => 'all',
            'policy_only' => '0',
        );
        require_once __DIR__ . '/../../includes/Http/DashboardExportController.php';
        $cb = static function () { return false; };
        add_filter('fbm_http_exit', $cb);
        $this->expectOutputString("Metric,Count\n");
        \FoodBankManager\Http\DashboardExportController::handle();
        remove_filter('fbm_http_exit', $cb);
    }
}
