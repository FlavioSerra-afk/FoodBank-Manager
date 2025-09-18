<?php
/**
 * Members actions audit tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Admin\Fixtures;

/**
 * Mailer stub capturing sends for members actions tests.
 */
final class MembersActionsMailerStub {
        /**
         * Captured payloads.
         *
         * @var array<int,array{email:string,first_name:string,member_reference:string,token:string}>
         */
        public static array $sent = array();

        /**
         * Reset captured state between tests.
         */
        public static function reset(): void {
                self::$sent = array();
        }

        /**
         * Capture the outgoing message context.
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

use FBM\Tests\Admin\Fixtures\MembersActionsMailerStub;
use FoodBankManager\Admin\MembersPage;
use FoodBankManager\Registration\MembersRepository;
use FoodBankManager\Registration\RegistrationService;
use FoodBankManager\Token\TokenRepository;
use FoodBankManager\Token\TokenService;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * @covers \FoodBankManager\Admin\MembersPage
 */
final class MembersActionsTest extends TestCase {
        private MembersRepository $members;
        private TokenService $tokens;
        private RegistrationService $registration;

        protected function setUp(): void {
                parent::setUp();

                global $wpdb;
                $wpdb = new \wpdb();
                $_GET = array();
                $_REQUEST = array();
                $GLOBALS['fbm_users']           = array();
                $GLOBALS['fbm_roles']           = array();
                $GLOBALS['fbm_next_user_id']    = 1;
                $GLOBALS['fbm_current_caps']    = array( 'fbm_manage' => true );
                $GLOBALS['fbm_options']         = array();
                $GLOBALS['fbm_deleted_options'] = array();
                unset( $GLOBALS['fbm_last_redirect'], $GLOBALS['fbm_test_nonces'] );

                $this->members      = new MembersRepository( $wpdb );
                $token_repository   = new TokenRepository( $wpdb );
                $this->tokens       = new TokenService( $token_repository );
                $this->registration = new RegistrationService( $this->members, $this->tokens );

                MembersActionsMailerStub::reset();
                MembersPage::set_mailer_factory(
                        static fn() => new MembersActionsMailerStub()
                );
        }

        protected function tearDown(): void {
                MembersPage::set_mailer_factory( null );

                $_GET     = array();
                $_REQUEST = array();

                unset(
                        $GLOBALS['wpdb'],
                        $GLOBALS['fbm_users'],
                        $GLOBALS['fbm_roles'],
                        $GLOBALS['fbm_next_user_id'],
                        $GLOBALS['fbm_options'],
                        $GLOBALS['fbm_deleted_options'],
                        $GLOBALS['fbm_last_redirect'],
                        $GLOBALS['fbm_current_caps'],
                        $GLOBALS['fbm_test_nonces']
                );

                parent::tearDown();
        }

        /**
         * Approve action should record a successful audit entry.
         */
        public function test_approve_action_records_audit_entry(): void {
                $reference = 'FBM-AUDIT';
                $member_id = $this->members->insert_pending_member( $reference, 'Aria', 'L', 'aria@example.com', 2 );

                $this->assertIsInt( $member_id );

                $outcome = $this->invokeAction( 'process_approve', $member_id );

                $this->invokeAudit( 'approve', $member_id, $outcome );

                $entries = get_option( 'fbm_members_action_audit', array() );

                $this->assertNotEmpty( $entries );
                $entry = $entries[0];

                $this->assertSame( 1, $entry['actor'] );
                $this->assertSame( 'approve', $entry['action'] );
                $this->assertSame( $member_id, $entry['member_id'] );
                $this->assertTrue( $entry['status'] );
                $this->assertSame( 'approved', $entry['notice'] );
                $this->assertSame( '', $entry['error'] );
                $this->assertArrayHasKey( 'recorded_at', $entry );
                $this->assertArrayNotHasKey( 'member_reference', $entry );
        }

        /**
         * Resend action should log a successful audit row.
         */
        public function test_resend_action_records_audit_entry(): void {
                        $registration = $this->registration->register( 'Robin', 'M', 'robin@example.com', 3 );

                        $this->assertIsArray( $registration );

                        $member_id = $registration['member_id'];

                        $outcome = $this->invokeAction( 'process_resend', $member_id );

                        $this->invokeAudit( 'resend', $member_id, $outcome );

                        $entries = get_option( 'fbm_members_action_audit', array() );

                        $this->assertNotEmpty( $entries );
                        $entry = $entries[0];

                        $this->assertSame( 'resend', $entry['action'] );
                        $this->assertSame( $member_id, $entry['member_id'] );
                        $this->assertTrue( $entry['status'] );
                        $this->assertSame( 'resent', $entry['notice'] );
                        $this->assertSame( '', $entry['error'] );
        }

        /**
         * Revoke action should capture a successful audit event.
         */
        public function test_revoke_action_records_audit_entry(): void {
                        $registration = $this->registration->register( 'Jamie', 'R', 'jamie@example.com', 2 );

                        $this->assertIsArray( $registration );

                        $member_id = $registration['member_id'];

                        $outcome = $this->invokeAction( 'process_revoke', $member_id );

                        $this->invokeAudit( 'revoke', $member_id, $outcome );

                        $entries = get_option( 'fbm_members_action_audit', array() );

                        $this->assertNotEmpty( $entries );
                        $entry = $entries[0];

                        $this->assertSame( 'revoke', $entry['action'] );
                        $this->assertSame( $member_id, $entry['member_id'] );
                        $this->assertTrue( $entry['status'] );
                        $this->assertSame( 'revoked', $entry['notice'] );
        }

        /**
         * Action handling should require the manage capability.
         */
        public function test_handle_actions_requires_manage_capability(): void {
                $GLOBALS['fbm_current_caps']['fbm_manage'] = false;

                $_GET     = array( 'page' => 'fbm-members', 'fbm-action' => 'approve', 'member_id' => 99 );
                $_REQUEST = $_GET;

                MembersPage::handle_actions();

                $this->assertArrayNotHasKey( 'fbm_members_action_audit', $GLOBALS['fbm_options'] ?? array() );
                $this->assertArrayNotHasKey( 'fbm_last_redirect', $GLOBALS );
        }

        /**
         * Invoke a private MembersPage action helper.
         *
         * @param string $method    Method name to execute.
         * @param int    $member_id Target member identifier.
         *
         * @return array{notice:string,status:bool,member_reference?:string,error?:string,token_hash?:string}
         */
        private function invokeAction( string $method, int $member_id ): array {
                        $reflection = new ReflectionMethod( MembersPage::class, $method );
                        $reflection->setAccessible( true );

                        /** @var array{notice:string,status:bool,member_reference?:string,error?:string,token_hash?:string} */
                        return $reflection->invoke( null, $member_id );
        }

        /**
         * Invoke the audit logger with reflection.
         *
         * @param string                                         $action    Action key.
         * @param int                                            $member_id Target member identifier.
         * @param array{notice:string,status:bool,error?:string} $outcome   Action outcome payload.
         */
        private function invokeAudit( string $action, int $member_id, array $outcome ): void {
                        $reflection = new ReflectionMethod( MembersPage::class, 'record_audit_entry' );
                        $reflection->setAccessible( true );
                        $reflection->invoke( null, $action, $member_id, $outcome );
        }
}
