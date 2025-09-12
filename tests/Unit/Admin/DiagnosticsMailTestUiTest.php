<?php
declare(strict_types=1);

namespace Tests\Unit\Admin;

use FoodBankManager\Http\DiagnosticsController;
use Tests\Support\Rbac;

final class DiagnosticsMailTestUiTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        $_POST = array();
        $_REQUEST = array();
    }

    protected function tearDown(): void {
        fbm_test_trust_nonces(true);
        parent::tearDown();
    }

    public function testNonceRequired(): void {
        fbm_test_trust_nonces(false);
        $res = DiagnosticsController::ajax_mail_test();
        $this->assertFalse($res->get_data()['success']);
        $this->assertSame(401, $res->get_status());
    }

    public function testCapabilityCheck(): void {
        fbm_test_set_request_nonce('fbm_mail_test', '_ajax_nonce');
        $_POST['to'] = 'a@example.com';
        Rbac::revokeAll();
        $res = DiagnosticsController::ajax_mail_test();
        $this->assertFalse($res->get_data()['success']);
        $this->assertSame(403, $res->get_status());
    }

    public function testHappyPath(): void {
        fbm_test_set_request_nonce('fbm_mail_test', '_ajax_nonce');
        $_POST['to'] = 'ok@example.com';
        fbm_grant_manager();
        $res = DiagnosticsController::ajax_mail_test();
        $data = $res->get_data();
        $this->assertTrue($data['success']);
        $this->assertTrue($data['data']['sent']);
    }
}
