<?php
/**
 * Integration coverage for public registration flows.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Integration;

use FoodBankManager\Registration\MembersRepository;
use FoodBankManager\Shortcodes\RegistrationForm;
use FoodBankManager\Token\TokenRepository;
use FoodBankManager\Token\TokenService;
use PHPUnit\Framework\TestCase;

/**
 * Captures welcome email sends for assertions.
 */
final class RegistrationMailerSpy {
        public int $send_calls = 0;

        /**
         * @var array{0:string,1:string,2:string,3:string}|null
         */
        public ?array $last_args = null;

        public function send( string $email, string $first_name, string $member_reference, string $token ): bool {
                ++$this->send_calls;
                $this->last_args = array( $email, $first_name, $member_reference, $token );

                return true;
        }
}

/**
 * @covers \FoodBankManager\Shortcodes\RegistrationForm
 */
final class RegistrationFlowTest extends TestCase {
        private \wpdb $wpdb;

        private RegistrationMailerSpy $mailer;

        protected function setUp(): void {
                parent::setUp();

                $this->wpdb                = new \wpdb();
                $GLOBALS['wpdb']           = $this->wpdb;
                $GLOBALS['fbm_options']    = array();
                $GLOBALS['fbm_users']      = array();
                $GLOBALS['fbm_roles']      = array();
                $GLOBALS['fbm_next_user_id'] = 1;
                $GLOBALS['fbm_test_nonces']  = array(
                        'fbm_registration_submit' => 'valid-nonce',
                );
                $GLOBALS['fbm_transients'] = array();

                $_SERVER = array();
                $_POST   = array();

                $this->mailer = new RegistrationMailerSpy();
                RegistrationForm::set_mailer_override(
                        fn() => $this->mailer
                );
        }

        protected function tearDown(): void {
                RegistrationForm::set_mailer_override( null );

                unset(
                        $GLOBALS['wpdb'],
                        $GLOBALS['fbm_options'],
                        $GLOBALS['fbm_users'],
                        $GLOBALS['fbm_roles'],
                        $GLOBALS['fbm_next_user_id'],
                        $GLOBALS['fbm_test_nonces'],
                        $GLOBALS['fbm_transients']
                );

                $_SERVER = array();
                $_POST   = array();

                parent::tearDown();
        }

        public function test_auto_approved_registration_issues_token_and_sends_welcome_email(): void {
                $this->prepareSubmission();

                $html = RegistrationForm::render();

                $this->assertStringContainsString(
                        'Thank you for registering. We have emailed your check-in QR code.',
                        $html
                );

                $this->assertCount( 1, $this->wpdb->members );
                $member = reset( $this->wpdb->members );
                $this->assertSame( MembersRepository::STATUS_ACTIVE, $member['status'] );
                $this->assertNotSame( '', $member['member_reference'] );

                $this->assertNotEmpty( $this->wpdb->tokens );
                $token_record = reset( $this->wpdb->tokens );
                $this->assertSame( $member['id'], $token_record['member_id'] );

                $this->assertSame( 1, $this->mailer->send_calls );
                $this->assertNotNull( $this->mailer->last_args );
                $this->assertSame( $member['email'], $this->mailer->last_args[0] );
                $this->assertSame( $member['first_name'], $this->mailer->last_args[1] );

                $this->assertArrayHasKey( 'meta', $token_record );
                $meta = json_decode( (string) $token_record['meta'], true );
                $this->assertIsArray( $meta );
                $this->assertSame( 'registration', $meta['context'] ?? null );
                $this->assertArrayNotHasKey( 'email', $meta );

                $members = new MembersRepository( $this->wpdb );
                $tokens  = new TokenService( new TokenRepository( $this->wpdb ) );

                $found = $members->find_by_reference( (string) $member['member_reference'] );
                $this->assertNotNull( $found );

                $active = $tokens->find_active_for_member( (int) $member['id'] );
                $this->assertNotNull( $active );
                $this->assertSame( $token_record['token_hash'], $active['token_hash'] );
        }

        public function test_pending_registration_skips_token_and_mailer(): void {
                update_option(
                        'fbm_settings',
                        array(
                                'registration' => array(
                                        'auto_approve' => false,
                                ),
                        )
                );

                $this->prepareSubmission(
                        array(
                                'fbm_email' => 'pending@example.com',
                        )
                );

                $html = RegistrationForm::render();

                $this->assertStringContainsString(
                        'Thank you for registering. Our team will review your application and send your QR code once approved.',
                        $html
                );

                $this->assertCount( 1, $this->wpdb->members );
                $member = reset( $this->wpdb->members );
                $this->assertSame( MembersRepository::STATUS_PENDING, $member['status'] );

                $this->assertEmpty( $this->wpdb->tokens );
                $this->assertSame( 0, $this->mailer->send_calls );
        }

        /**
         * Prepare a baseline valid submission payload.
         *
         * @param array<string,string> $overrides Optional overrides.
         */
        private function prepareSubmission( array $overrides = array() ): void {
                $_SERVER['REQUEST_METHOD'] = 'POST';

                $defaults = array(
                        'fbm_registration_submitted' => '1',
                        'fbm_registration_nonce'     => 'valid-nonce',
                        'fbm_registration_hp'        => '',
                        'fbm_registration_time'      => (string) ( time() - 120 ),
                        'fbm_first_name'             => 'Morgan',
                        'fbm_last_initial'           => 'A',
                        'fbm_email'                  => 'morgan@example.com',
                        'fbm_household_size'         => '3',
                        'fbm_registration_consent'   => '',
                );

                $_POST = array_merge( $defaults, $overrides );
        }
}
