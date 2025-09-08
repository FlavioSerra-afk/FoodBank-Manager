<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class DashboardExportControllerTest extends TestCase {
    /** @runInSeparateProcess */
    public function testNonceRequired(): void {
        fbm_grant_for_page('fbm');
        fbm_test_trust_nonces(false);
        require_once __DIR__ . '/../../includes/Http/DashboardExportController.php';
        $this->expectException( \RuntimeException::class );
        \FoodBankManager\Http\DashboardExportController::handle();
    }
}
