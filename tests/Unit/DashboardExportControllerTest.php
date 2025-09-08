<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Tests\Support\Exceptions\FbmDieException;
use Tests\Support\Rbac;

final class DashboardExportControllerTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        global $wpdb;
        $wpdb = new \FBM\Tests\Support\WPDBStub();
        $wpdb->prefix = 'wp_';
        unset( $GLOBALS['__fbm_sent_headers'], $GLOBALS['__fbm_bom_written'] );
    }
    /** */
    public function testNonceRequired(): void {
        Rbac::grantManager();
        fbm_test_trust_nonces(false);
        require_once __DIR__ . '/../../includes/Http/DashboardExportController.php';
        $this->expectException(FbmDieException::class);
        \FoodBankManager\Http\DashboardExportController::handle();
    }

    /** */
    public function testExportsCsv(): void {
        require_once __DIR__ . '/../Support/DashboardExportControllerStubs.php';
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
        if ( ! function_exists( 'fbm_return_false' ) ) {
            function fbm_return_false() { return false; }
        }
        add_filter('fbm_http_exit', 'fbm_return_false');
        ob_start();
        \FoodBankManager\Http\DashboardExportController::handle();
        $out = ob_get_clean();
        $this->assertStringStartsWith("\xEF\xBB\xBFMetric,Count\n", $out); // Expect BOM + header.
        remove_filter('fbm_http_exit', 'fbm_return_false');
    }
}
