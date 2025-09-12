<?php
declare(strict_types=1);

namespace Tests\Support\Stubs {
    class MailLogRepo {
        public static array $records = array();
        public static array $audits = array();
        public static function get_by_id( int $id ): ?array {
            return self::$records[$id] ?? null;
        }
        public static function audit_resend( int $id, string $status, int $actor, string $msg ): bool {
            self::$audits[] = compact( 'id', 'status', 'actor', 'msg' );
            return true;
        }
    }
}

namespace Tests\Unit\Admin {

use FBM\Admin\MailReplay;
use Tests\Support\Stubs\MailLogRepo;
use function add_filter;
use function fbm_grant_caps;
use function fbm_seed_nonce;
use function fbm_nonce;

final class MailReplayTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        fbm_grant_caps( array( 'fbm_manage_diagnostics' ) );
        fbm_seed_nonce( 'unit' );
        MailLogRepo::$records = array();
        MailLogRepo::$audits = array();
        add_filter( 'fbm_mail_replay_repo', fn() => MailLogRepo::class );
    }

    public function testInvalidAddress(): void {
        MailLogRepo::$records[1] = array( 'id' => 1, 'to_email' => '', 'subject' => 's', 'headers' => '' );
        $_POST = array( 'id' => 1, '_ajax_nonce' => fbm_nonce( 'fbm_mail_replay' ) );
        $res = MailReplay::handle();
        $this->assertFalse( $res->get_data()['success'] );
    }

    public function testResendSuccess(): void {
        MailLogRepo::$records[2] = array( 'id' => 2, 'to_email' => 'a@example.com', 'subject' => 'Hi', 'headers' => '' );
        $_POST = array( 'id' => 2, '_ajax_nonce' => fbm_nonce( 'fbm_mail_replay' ) );
        $res = MailReplay::handle();
        $this->assertTrue( $res->get_data()['success'] );
        $audit = MailLogRepo::$audits[0] ?? array();
        $this->assertSame( 2, $audit['id'] ?? 0 );
    }
}

}
