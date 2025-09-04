<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class DashboardExportControllerTest extends TestCase {
    /** @runInSeparateProcess */
    public function testNonceRequired(): void {
        $GLOBALS['fbm_can_dash'] = true;
        if ( ! function_exists( 'current_user_can' ) ) {
            function current_user_can() { return $GLOBALS['fbm_can_dash']; }
        }
        if ( ! function_exists( 'wp_verify_nonce' ) ) {
            function wp_verify_nonce() { return false; }
        }
        if ( ! function_exists( 'wp_die' ) ) {
            function wp_die( $msg ) { throw new Exception( $msg ); }
        }
        require_once __DIR__ . '/../../includes/Http/DashboardExportController.php';
        $this->expectException( Exception::class );
        \FoodBankManager\Http\DashboardExportController::handle();
    }
}
