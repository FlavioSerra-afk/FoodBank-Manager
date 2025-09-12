<?php
declare(strict_types=1);

namespace Tests\Unit\Admin {

use FBM\Admin\MailReplay;
use Tests\Support\Rbac;
use function fbm_nonce;

final class AjaxNonceCapTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        Rbac::revokeAll();
    }

    public function testNonceAndCapabilityRequired(): void {
        Rbac::grantAdmin();
        fbm_test_trust_nonces(false);
        $_POST = array('id' => 1); // missing nonce
        $res = MailReplay::handle();
        $this->assertSame(403, $res->get_status());

        fbm_test_trust_nonces(true);
        Rbac::revokeAll();
        $_POST = array('id' => 1, '_ajax_nonce' => fbm_nonce('fbm_mail_replay'));
        $res2 = MailReplay::handle();
        $this->assertSame(403, $res2->get_status());
    }
}

}
