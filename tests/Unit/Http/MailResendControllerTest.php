<?php
declare(strict_types=1);

namespace FBM\Mail {
    class LogRepo {
        public static array $records = array();
        public static array $appended = array();

        public static function get_by_id(int $id): ?array {
            return self::$records[$id] ?? null;
        }

        public static function find_by_application_id(int $id): array {
            return array();
        }

        public static function append(array $row): bool {
            self::$appended[] = $row;
            return true;
        }
    }
}

namespace FoodBankManager\Tests\Unit\Http {

use Tests\Support\Rbac;
use Tests\Support\Exceptions\FbmDieException;
use FBM\Http\MailResendController;
use FBM\Mail\LogRepo;

final class MailResendControllerTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        Rbac::grantManager();
        fbm_seed_nonce('unit-seed');
        LogRepo::$records = array();
        LogRepo::$appended = array();
    }

    public function testSuccess(): void {
        LogRepo::$records[42] = array(
            'id'       => 42,
            'to_email' => 'a@example.com',
            'subject'  => 'Hello',
            'headers'  => '',
            'body_vars'=> array('body' => 'Hi'),
        );
        $_POST = array(
            'id'       => 42,
            '_wpnonce' => fbm_nonce('fbm_mail_resend'),
        );
        $this->expectException(FbmDieException::class);
        MailResendController::handle();
        $this->assertStringContainsString('notice=resent', (string) $GLOBALS['__last_redirect']);
        $audit = LogRepo::$appended[0] ?? array();
        $this->assertSame('resend', $audit['type'] ?? '');
        $this->assertSame(42, $audit['original_id'] ?? 0);
        $this->assertSame('sent', $audit['result'] ?? '');
    }

    public function testTransportError(): void {
        fbm_test_set_wp_mail_result(false);
        LogRepo::$records[42] = array(
            'id'       => 42,
            'to_email' => 'a@example.com',
            'subject'  => 'Hello',
            'headers'  => '',
            'body_vars'=> array('body' => 'Hi'),
        );
        $_POST = array(
            'id'       => 42,
            '_wpnonce' => fbm_nonce('fbm_mail_resend'),
        );
        $this->expectException(FbmDieException::class);
        MailResendController::handle();
        $this->assertStringContainsString('notice=error', (string) $GLOBALS['__last_redirect']);
        $audit = LogRepo::$appended[0] ?? array();
        $this->assertSame('error', $audit['result'] ?? '');
    }

    public function testDenied(): void {
        Rbac::revokeAll();
        $_POST = array(
            'id'       => 42,
            '_wpnonce' => fbm_nonce('fbm_mail_resend'),
        );
        $this->expectException(FbmDieException::class);
        MailResendController::handle();
        $this->assertSame(array(), LogRepo::$appended);
    }
}

}

