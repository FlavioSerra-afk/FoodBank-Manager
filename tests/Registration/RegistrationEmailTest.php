<?php
/**
 * Registration email flow tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Registration\Fixtures;

/**
 * Spy mailer to capture welcome email sends.
 */
final class SpyWelcomeMailer {
        /**
         * Captured send payloads.
         *
         * @var array<int,array{email:string,first_name:string,member_reference:string,token:string}>
         */
        public static array $sent = array();

        /**
         * Flag controlling send results.
         *
         * @var bool
         */
        public static bool $should_send = true;

        /**
         * Reset captured sends to a clean state.
         */
        public static function reset(): void {
                self::$sent        = array();
                self::$should_send = true;
        }

        /**
         * Capture the outgoing email parameters.
         *
         * @param string $email            Recipient email address.
         * @param string $first_name       Recipient first name.
         * @param string $member_reference Canonical member reference string.
         * @param string $token            Raw token payload.
         */
        public function send( string $email, string $first_name, string $member_reference, string $token ): bool {
                self::$sent[] = array(
                        'email'            => $email,
                        'first_name'       => $first_name,
                        'member_reference' => $member_reference,
                        'token'            => $token,
                );

                return self::$should_send;
        }
}

namespace FBM\Tests\Registration;

use FBM\Tests\Registration\Fixtures\SpyWelcomeMailer;
use FoodBankManager\Admin\MembersPage;
use FoodBankManager\Registration\MembersRepository;
use FoodBankManager\Registration\RegistrationService;
use FoodBankManager\Token\TokenRepository;
use FoodBankManager\Token\TokenService;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

if ( ! class_exists( \FoodBankManager\Email\WelcomeMailer::class, false ) ) {
        class_alias( SpyWelcomeMailer::class, \FoodBankManager\Email\WelcomeMailer::class );
}

/**
 * Exercising registration and administrative email flows.
 *
 * @covers \FoodBankManager\Registration\RegistrationService
 * @covers \FoodBankManager\Token\TokenService
 * @covers \FoodBankManager\Admin\MembersPage
 */
final class RegistrationEmailTest extends TestCase {
        /**
         * Prepare isolated fixtures for each test.
         */
        protected function setUp(): void {
                parent::setUp();

                SpyWelcomeMailer::reset();

                global $wpdb;
                $wpdb = new \wpdb();
        }

        /**
         * Register a helper stack of services backed by the shared wpdb stub.
         *
         * @return array{0:RegistrationService,1:TokenService,2:MembersRepository}
         */
        private function createServices(): array {
                global $wpdb;

                $members      = new MembersRepository( $wpdb );
                $token_repo   = new TokenRepository( $wpdb );
                $tokens       = new TokenService( $token_repo );
                $registration = new RegistrationService( $members, $tokens );

                return array( $registration, $tokens, $members );
        }

        /**
         * Successful registrations should issue a token and deliver the welcome email.
         */
        public function test_registration_issues_token_and_sends_welcome_email(): void {
                list( $registration, $tokens ) = $this->createServices();

                $outcome = $registration->register( 'Alice', 'B', 'alice@example.com', 3 );

                $this->assertIsArray( $outcome );
                $this->assertArrayHasKey( 'member_id', $outcome );
                $this->assertArrayHasKey( 'token', $outcome );
                $this->assertArrayHasKey( 'member_reference', $outcome );

                $member_id = $outcome['member_id'];

                global $wpdb;
                $this->assertArrayHasKey( $member_id, $wpdb->members );

                $this->assertSame( $member_id, $tokens->verify( $outcome['token'] ) );

                $mailer = new \FoodBankManager\Email\WelcomeMailer();
                $this->assertTrue( $mailer->send( 'alice@example.com', 'Alice', $outcome['member_reference'], $outcome['token'] ) );

                $this->assertCount( 1, SpyWelcomeMailer::$sent );
                $captured = SpyWelcomeMailer::$sent[0];

                $this->assertSame( 'alice@example.com', $captured['email'] );
                $this->assertSame( 'Alice', $captured['first_name'] );
                $this->assertSame( $outcome['member_reference'], $captured['member_reference'] );
                $this->assertSame( $outcome['token'], $captured['token'] );
        }

        /**
         * Providing consent should capture the recorded timestamp.
         */
        public function test_registration_records_consent_timestamp(): void {
                list( $registration ) = $this->createServices();

                $now     = time();
                $outcome = $registration->register( 'Devon', 'H', 'devon@example.com', 2, $now );

                $this->assertNotNull( $outcome );

                global $wpdb;

                $this->assertArrayHasKey( $outcome['member_id'], $wpdb->members );
                $member = $wpdb->members[ $outcome['member_id'] ];

                $this->assertArrayHasKey( 'consent_recorded_at', $member );
                $this->assertNotFalse( strtotime( (string) $member['consent_recorded_at'] ) );
        }

        /**
         * Reactivating an existing member honours new consent submissions.
         */
        public function test_reactivation_updates_consent_timestamp_when_present(): void {
                list( $registration ) = $this->createServices();

                $first = $registration->register( 'Emery', 'Q', 'emery@example.com', 4 );

                $this->assertNotNull( $first );

                global $wpdb;
                $member_id = $first['member_id'];

                $this->assertArrayHasKey( $member_id, $wpdb->members );
                $this->assertArrayNotHasKey( 'consent_recorded_at', $wpdb->members[ $member_id ] );

                $reactivated = $registration->register( 'Emery', 'Q', 'emery@example.com', 4, time() );

                $this->assertNotNull( $reactivated );
                $this->assertTrue( $reactivated['reactivated'] );
                $this->assertArrayHasKey( 'consent_recorded_at', $wpdb->members[ $member_id ] );
                $this->assertNotFalse( strtotime( (string) $wpdb->members[ $member_id ]['consent_recorded_at'] ) );
        }

        /**
         * Resending credentials should mint a fresh token and invalidate the prior one.
         */
        public function test_resend_regenerates_tokens(): void {
                list( $registration, $tokens ) = $this->createServices();

                $outcome        = $registration->register( 'Bob', 'C', 'bob@example.com', 2 );
                $member_id      = $outcome['member_id'];
                $original_token = $outcome['token'];

                $this->assertSame( $member_id, $tokens->verify( $original_token ) );

                SpyWelcomeMailer::reset();

                $method = new ReflectionMethod( MembersPage::class, 'process_resend' );
                $method->setAccessible( true );
                $result = $method->invoke( null, $member_id );

                $this->assertTrue( $result['status'] );
                $this->assertSame( 'resent', $result['notice'] );

                $this->assertCount( 1, SpyWelcomeMailer::$sent );
                $resent_token = SpyWelcomeMailer::$sent[0]['token'];

                $this->assertNotSame( $original_token, $resent_token );
                $this->assertNull( $tokens->verify( $original_token ) );
                $this->assertSame( $member_id, $tokens->verify( $resent_token ) );
        }

        /**
         * Revoked members should no longer verify with their previous token value.
         */
        public function test_revoke_prevents_future_verification(): void {
                list( $registration, $tokens ) = $this->createServices();

                $outcome   = $registration->register( 'Cara', 'D', 'cara@example.com', 4 );
                $member_id = $outcome['member_id'];
                $token     = $outcome['token'];

                $this->assertSame( $member_id, $tokens->verify( $token ) );

                $method = new ReflectionMethod( MembersPage::class, 'process_revoke' );
                $method->setAccessible( true );
                $result = $method->invoke( null, $member_id );

                $this->assertTrue( $result['status'] );
                $this->assertSame( 'revoked', $result['notice'] );
                $this->assertNull( $tokens->verify( $token ) );
        }
}
