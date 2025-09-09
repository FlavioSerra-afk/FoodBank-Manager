<?php
declare(strict_types=1);

namespace FoodBankManager\Tests\Unit\Http {

use \BaseTestCase;
use Tests\Support\Rbac;
use Tests\Support\Exceptions\FbmDieException;
use FBM\Http\DiagnosticsController;

final class DiagnosticsControllerTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        Rbac::grantManager();
    }

    public function testMailTestSuccess(): void {
        fbm_seed_nonce('unit-seed');
        $_POST = array(
            'fbm_action' => 'mail_test',
            '_fbm_nonce' => fbm_nonce('fbm_diag_mail_test'),
        );
        $this->expectException(FbmDieException::class);
        (new DiagnosticsController())->handle();
        $this->assertStringContainsString('notice=sent', (string) $GLOBALS['__last_redirect']);
    }

    public function testMailTestFailure(): void {
        fbm_test_set_wp_mail_result(false);
        fbm_seed_nonce('unit-seed');
        $_POST = array(
            'fbm_action' => 'mail_test',
            '_fbm_nonce' => fbm_nonce('fbm_diag_mail_test'),
        );
        $this->expectException(FbmDieException::class);
        (new DiagnosticsController())->handle();
        $this->assertStringContainsString('notice=error', (string) $GLOBALS['__last_redirect']);
    }
}

}
