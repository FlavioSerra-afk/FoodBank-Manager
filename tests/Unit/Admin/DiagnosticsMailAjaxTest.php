<?php
declare(strict_types=1);

namespace Tests\Unit\Admin;

use FoodBankManager\Http\DiagnosticsController;

final class DiagnosticsMailAjaxTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        $_POST = array();
        $_REQUEST = array();
        fbm_test_set_request_nonce('fbm_mail_test', '_ajax_nonce');
        fbm_grant_manager();
    }

    public function testInvalidAddress(): void {
        $_POST['to'] = 'nope';
        $res = DiagnosticsController::ajax_mail_test();
        $this->assertFalse($res->get_data()['success']);
        $this->assertSame(400, $res->get_status());
    }

    public function testValidAddress(): void {
        $_POST['to'] = 'ok@example.com';
        $res = DiagnosticsController::ajax_mail_test();
        $data = $res->get_data();
        $this->assertTrue($data['success']);
        $this->assertTrue($data['data']['sent']);
    }
}
