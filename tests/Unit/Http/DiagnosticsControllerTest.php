<?php
declare(strict_types=1);

namespace FoodBankManager\Tests\Unit\Http {

use \BaseTestCase;
use FoodBankManager\Http\DiagnosticsController;
use Tests\Support\Exceptions\FbmDieException;

final class DiagnosticsControllerTest extends BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        fbm_grant_manager();
        global $fbm_test_options, $fbm_options;
        $fbm_test_options = array(
            'emails' => array(
                'from_name'  => 'FoodBank',
                'from_email' => 'from@example.com',
            ),
            'admin_email' => 'admin@example.com',
        );
        $fbm_options =& $fbm_test_options;
    }

    public function testMailTestSuccess(): void {
        fbm_seed_nonce('unit-seed');
        fbm_test_set_request_nonce('fbm_diag_mail_test', '_fbm_nonce');
        $_POST = array(
            '_fbm_nonce' => $_POST['_fbm_nonce'],
        );
        $_REQUEST = $_POST;
        try {
            DiagnosticsController::mail_test();
        } catch ( FbmDieException $e ) {
            $this->assertSame('redirect', $e->getMessage());
        }
        $this->assertStringContainsString('notice=sent', (string) $GLOBALS['__last_redirect']);
    }

    public function testMailTestFailure(): void {
        fbm_test_set_wp_mail_result(false);
        fbm_seed_nonce('unit-seed');
        fbm_test_set_request_nonce('fbm_diag_mail_test', '_fbm_nonce');
        $_POST = array(
            '_fbm_nonce' => $_POST['_fbm_nonce'],
        );
        $_REQUEST = $_POST;
        try {
            DiagnosticsController::mail_test();
        } catch ( FbmDieException $e ) {
            $this->assertSame('redirect', $e->getMessage());
        }
        $this->assertStringContainsString('notice=error', (string) $GLOBALS['__last_redirect']);
    }
}
}
