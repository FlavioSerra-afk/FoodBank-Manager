<?php
declare(strict_types=1);

namespace Tests\Unit\Admin;

use FoodBankManager\Http\DiagnosticsController;
use Tests\Support\Rbac;

final class AjaxErrorsTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        $_POST = $_GET = $_REQUEST = array();
        Rbac::revokeAll();
    }

    public function testMissingNonce(): void {
        fbm_grant_manager();
        fbm_test_trust_nonces(false);
        $res = DiagnosticsController::ajax_mail_test();
        $data = $res->get_data();
        $this->assertSame(401, $res->get_status());
        $this->assertSame('invalid_nonce', $data['data']['error']['code']);
    }

    public function testMissingCapability(): void {
        fbm_test_set_request_nonce('fbm_mail_test', '_ajax_nonce');
        $res = DiagnosticsController::ajax_mail_test();
        $data = $res->get_data();
        $this->assertSame(403, $res->get_status());
        $this->assertSame('forbidden', $data['data']['error']['code']);
    }

    public function testInvalidInput(): void {
        fbm_test_set_request_nonce('fbm_mail_test', '_ajax_nonce');
        fbm_grant_manager();
        $_POST['to'] = 'bad';
        $res = DiagnosticsController::ajax_mail_test();
        $data = $res->get_data();
        $this->assertSame(422, $res->get_status());
        $this->assertSame('invalid_param', $data['data']['error']['code']);
    }

    public function testSuccessPath(): void {
        fbm_test_set_request_nonce('fbm_mail_test', '_ajax_nonce');
        fbm_grant_manager();
        $_POST['to'] = 'good@example.com';
        $res = DiagnosticsController::ajax_mail_test();
        $data = $res->get_data();
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('data', $data);
    }
}
