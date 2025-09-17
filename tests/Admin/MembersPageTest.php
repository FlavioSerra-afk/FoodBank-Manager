<?php
/**
 * Members admin page tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Admin\Fixtures;

/**
 * Spy mailer capturing admin page sends.
 */
final class MembersPageMailerStub {
		/**
		 * Captured send payloads.
		 *
		 * @var array<int,array{email:string,first_name:string,member_reference:string,token:string}>
		 */
	public static array $sent = array();

		/**
		 * Reset captured mail state.
		 */
	public static function reset(): void {
			self::$sent = array();
	}

		/**
		 * Capture the outgoing email parameters.
		 */
	public function send( string $email, string $first_name, string $member_reference, string $token ): bool {
			self::$sent[] = array(
				'email'            => $email,
				'first_name'       => $first_name,
				'member_reference' => $member_reference,
				'token'            => $token,
			);

			return true;
	}
}

namespace FBM\Tests\Admin;

use FBM\Tests\Admin\Fixtures\MembersPageMailerStub;
use FoodBankManager\Admin\MembersPage;
use FoodBankManager\Diagnostics\MailFailureLog;
use FoodBankManager\Registration\MembersRepository;
use FoodBankManager\Registration\RegistrationService;
use FoodBankManager\Token\TokenRepository;
use FoodBankManager\Token\TokenService;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * @covers \FoodBankManager\Admin\MembersPage
 */
final class MembersPageTest extends TestCase {
	private MembersRepository $members;
	private TokenService $tokens;
	private RegistrationService $registration;

        protected function setUp(): void {
                parent::setUp();

                global $wpdb;
                $wpdb = new \wpdb();
                $GLOBALS['fbm_users'] = array();
                $GLOBALS['fbm_roles'] = array();
                $GLOBALS['fbm_next_user_id'] = 1;
                unset( $GLOBALS['fbm_options'], $GLOBALS['fbm_deleted_options'] );

                $this->members      = new MembersRepository( $wpdb );
                $token_repository   = new TokenRepository( $wpdb );
                $this->tokens       = new TokenService( $token_repository );
                $this->registration = new RegistrationService( $this->members, $this->tokens );

			MembersPageMailerStub::reset();
			MembersPage::set_mailer_factory(
				static fn() => new MembersPageMailerStub()
			);
	}

        protected function tearDown(): void {
                MembersPage::set_mailer_factory( null );

                unset( $GLOBALS['wpdb'], $GLOBALS['fbm_users'], $GLOBALS['fbm_roles'], $GLOBALS['fbm_next_user_id'] );

                parent::tearDown();
        }

		/**
		 * Resend action should mint a fresh token and dispatch a welcome email.
		 */
	public function test_process_resend_regenerates_token_and_sends_email(): void {
			$registration = $this->registration->register( 'Robin', 'M', 'robin@example.com', 3 );

			$this->assertIsArray( $registration );

			$member_id      = $registration['member_id'];
			$original_token = $registration['token'];

			global $wpdb;
			$original_hash = $wpdb->tokens[ $member_id ]['token_hash'] ?? '';

			MembersPageMailerStub::reset();

			$outcome = $this->invokeAction( 'process_resend', $member_id );

			$this->assertTrue( $outcome['status'] );
			$this->assertSame( 'resent', $outcome['notice'] );

			$this->assertCount( 1, MembersPageMailerStub::$sent );

			$resent = MembersPageMailerStub::$sent[0];
			$this->assertSame( 'robin@example.com', $resent['email'] );
			$this->assertSame( 'Robin', $resent['first_name'] );

			$this->assertNotSame( $original_token, $resent['token'] );
			$this->assertNotSame( $original_hash, $wpdb->tokens[ $member_id ]['token_hash'] );

			$this->assertNull( $this->tokens->verify( $original_token ) );
			$this->assertSame( $member_id, $this->tokens->verify( $resent['token'] ) );
	}

		/**
		 * Regenerate action should rotate the active token without sending email.
		 */
	public function test_process_regenerate_replaces_active_token(): void {
			$registration = $this->registration->register( 'Casey', 'N', 'casey@example.com', 2 );

			$member_id      = $registration['member_id'];
			$original_token = $registration['token'];

			global $wpdb;
			$original_hash = $wpdb->tokens[ $member_id ]['token_hash'] ?? '';

			MembersPageMailerStub::reset();

			$outcome = $this->invokeAction( 'process_regenerate', $member_id );

			$this->assertTrue( $outcome['status'] );
			$this->assertSame( 'regenerated', $outcome['notice'] );
			$this->assertCount( 0, MembersPageMailerStub::$sent );
			$this->assertNotSame( $original_hash, $wpdb->tokens[ $member_id ]['token_hash'] );
			$this->assertNull( $this->tokens->verify( $original_token ) );
	}

		                /**
                 * Approve action should activate pending members and send the welcome email.
                 */
        public function test_process_approve_issues_token_for_pending_member(): void {
                $reference = 'FBM-PENDING';
                $member_id = $this->members->insert_active_member( $reference, 'Taylor', 'Q', 'pending@example.com', 1 );

                $this->assertIsInt( $member_id );

                global $wpdb;
                $wpdb->members[ $member_id ]['status']       = MembersRepository::STATUS_PENDING;
                $wpdb->members[ $member_id ]['activated_at'] = null;

                MembersPageMailerStub::reset();

                $outcome = $this->invokeAction( 'process_approve', $member_id );

                $this->assertTrue( $outcome['status'] );
                $this->assertSame( 'approved', $outcome['notice'] );
                $this->assertSame( 'active', $wpdb->members[ $member_id ]['status'] );

                $this->assertCount( 1, MembersPageMailerStub::$sent );
                $approval = MembersPageMailerStub::$sent[0];

                $this->assertSame( 'pending@example.com', $approval['email'] );
                $this->assertSame( 'Taylor', $approval['first_name'] );
                $this->assertSame( $reference, $approval['member_reference'] );
                $this->assertSame( $member_id, $this->tokens->verify( $approval['token'] ) );
        }

                                /**
                 * Revoke action should mark members as revoked and resolve outstanding mail failures.
                 */
        public function test_process_revoke_marks_member_revoked_and_resolves_failures(): void {
                $registration = $this->registration->register( 'Jamie', 'R', 'jamie@example.com', 2 );

                $this->assertIsArray( $registration );

                $member_id = $registration['member_id'];

                global $wpdb;
                $original_updated_at                   = $wpdb->members[ $member_id ]['updated_at'];
                $wpdb->members[ $member_id ]['updated_at'] = '2000-01-01 00:00:00';

                $log = new MailFailureLog();
                $log->record_failure(
                        $member_id,
                        $registration['member_reference'],
                        'jamie@example.com',
                        MailFailureLog::CONTEXT_ADMIN_RESEND,
                        MailFailureLog::ERROR_MAIL
                );

                $this->assertNotSame( array(), \get_option( 'fbm_mail_failures', array() ) );

                $outcome = $this->invokeAction( 'process_revoke', $member_id );

                $this->assertTrue( $outcome['status'] );
                $this->assertSame( 'revoked', $outcome['notice'] );
                $this->assertSame( MembersRepository::STATUS_REVOKED, $wpdb->members[ $member_id ]['status'] );
                $this->assertNull( $wpdb->members[ $member_id ]['activated_at'] );
                $this->assertNotSame( '2000-01-01 00:00:00', $wpdb->members[ $member_id ]['updated_at'] );
                $this->assertNotSame( $original_updated_at, $wpdb->members[ $member_id ]['updated_at'] );
                $this->assertSame( array(), \get_option( 'fbm_mail_failures', array() ) );
        }


		/**
		 * Invoke a private MembersPage action helper.
		 *
		 * @param string $method     Action handler name.
		 * @param int    $member_id  Target member identifier.
		 *
		 * @return array{notice:string,status:bool,member_reference?:string,error?:string}
		 */
	private function invokeAction( string $method, int $member_id ): array {
			$reflection = new ReflectionMethod( MembersPage::class, $method );
			$reflection->setAccessible( true );

			/** @var array{notice:string,status:bool,member_reference?:string,error?:string} */
			return $reflection->invoke( null, $member_id );
	}
}
