<?php
/**
 * Registration form shortcode tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Registration;

use FoodBankManager\Shortcodes\RegistrationForm;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * Test double for capturing welcome email sends.
 */
final class SpyWelcomeMailer {
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
final class RegistrationFormTest extends TestCase {
        private \wpdb $wpdb;
        private SpyWelcomeMailer $mailer;

        protected function setUp(): void {
                parent::setUp();

                $this->wpdb        = new \wpdb();
                $GLOBALS['wpdb']   = $this->wpdb;
                $GLOBALS['fbm_test_nonces'] = array(
                        'fbm_registration_submit' => 'valid-nonce',
                );

                $_SERVER = array();
                $_POST   = array();

                $this->mailer = new SpyWelcomeMailer();
                RegistrationForm::set_mailer_override(
                        fn() => $this->mailer
                );
        }

        protected function tearDown(): void {
                RegistrationForm::set_mailer_override( null );

                unset( $GLOBALS['wpdb'], $GLOBALS['fbm_test_nonces'] );

                $_SERVER = array();
                $_POST   = array();

                parent::tearDown();
        }

        /**
         * Submitting without required fields should report validation errors.
         */
        public function test_submission_with_missing_fields_returns_errors(): void {
                $this->prepare_valid_submission(
                        array(
                                'fbm_first_name'   => '  ',
                                'fbm_last_initial' => '',
                                'fbm_email'        => 'invalid',
                        )
                );

                $result = $this->invoke_handle_submission();

                $this->assertFalse( $result['success'] );
                $this->assertContains( 'First name is required.', $result['errors'] );
                $this->assertContains( 'Last initial must be a single letter.', $result['errors'] );
                $this->assertContains( 'A valid email address is required.', $result['errors'] );
                $this->assertSame( 'Please correct the errors below and try again.', $result['message'] );
        }

        /**
         * Honeypot submissions must be rejected.
         */
        public function test_submission_rejects_honeypot_submission(): void {
                $this->prepare_valid_submission( array( 'fbm_registration_hp' => 'bot' ) );

                $result = $this->invoke_handle_submission();

                $this->assertFalse( $result['success'] );
                $this->assertContains( 'Invalid submission. Please try again.', $result['errors'] );
        }

        /**
         * Submissions faster than the minimum threshold must fail the time trap.
         */
        public function test_submission_rejects_fast_time_trap(): void {
                $this->prepare_valid_submission(
                        array( 'fbm_registration_time' => (string) time() )
                );

                $result = $this->invoke_handle_submission();

                $this->assertFalse( $result['success'] );
                $this->assertContains( 'Invalid submission. Please try again.', $result['errors'] );
        }

        /**
         * Valid submissions should persist data and send the welcome email.
         */
        public function test_successful_submission_inserts_member_and_sends_email(): void {
                $this->prepare_valid_submission(
                        array(
                                'fbm_first_name'        => '  Robin  ',
                                'fbm_last_initial'      => 'm',
                                'fbm_email'             => ' Robin@example.com ',
                                'fbm_household_size'    => '14',
                                'fbm_registration_time' => (string) ( time() - 120 ),
                        )
                );

                $result = $this->invoke_handle_submission();

                $this->assertTrue( $result['success'] );
                $this->assertSame(
                        'Thank you for registering. We have emailed your check-in QR code.',
                        $result['message']
                );
                $this->assertSame( '', $result['values']['first_name'] );
                $this->assertSame( '', $result['values']['last_initial'] );
                $this->assertSame( '', $result['values']['email'] );
                $this->assertSame( '1', $result['values']['household_size'] );
                $this->assertSame( '', $result['values']['consent'] );

                $this->assertCount( 1, $this->wpdb->members );
                $member = reset( $this->wpdb->members );

                $this->assertSame( 'Robin', $member['first_name'] );
                $this->assertSame( 'M', $member['last_initial'] );
                $this->assertSame( 'Robin@example.com', $member['email'] );
                $this->assertSame( 12, $member['household_size'] );
                $this->assertNull( $member['consent_recorded_at'] ?? null );

                $this->assertNotEmpty( $this->wpdb->tokens );

                $this->assertSame( 1, $this->mailer->send_calls );
                $this->assertNotNull( $this->mailer->last_args );
                $this->assertSame( 'Robin@example.com', $this->mailer->last_args[0] );

                $last_prepare = $this->wpdb->get_last_prepare();
                $this->assertIsArray( $last_prepare );
                $this->assertStringContainsString( 'member_reference = %s', $last_prepare['query'] );
                $this->assertMatchesRegularExpression( '/^FBM-/', (string) ( $last_prepare['args'][0] ?? '' ) );
        }

        /**
         * Optional consent should persist when provided.
         */
        public function test_successful_submission_with_consent_records_timestamp(): void {
                $this->prepare_valid_submission(
                        array(
                                'fbm_first_name'             => 'Jordan',
                                'fbm_last_initial'           => 'k',
                                'fbm_email'                  => 'jordan@example.com',
                                'fbm_registration_time'      => (string) ( time() - 60 ),
                                'fbm_registration_consent'   => '1',
                        )
                );

                $result = $this->invoke_handle_submission();

                $this->assertTrue( $result['success'] );
                $this->assertSame( '', $result['values']['consent'] );

                $this->assertCount( 1, $this->wpdb->members );
                $member = reset( $this->wpdb->members );

                $this->assertArrayHasKey( 'consent_recorded_at', $member );
                $this->assertNotSame( '', $member['consent_recorded_at'] );
                $this->assertNotFalse( strtotime( (string) $member['consent_recorded_at'] ) );
        }

        /**
         * Invoke the private handle_submission helper via reflection.
         */
        private function invoke_handle_submission(): array {
                $method = new ReflectionMethod( RegistrationForm::class, 'handle_submission' );
                $method->setAccessible( true );

                /** @var array{success:bool,errors:array<int,string>,message:string,values:array<string,string>} */
                return $method->invoke( null );
        }

        /**
         * Prime $_POST/$_SERVER with a baseline valid submission.
         *
         * @param array<string,string> $overrides Field overrides for the fixture.
         */
        private function prepare_valid_submission( array $overrides = array() ): void {
                $timestamp = time() - 30;

                $_SERVER['REQUEST_METHOD'] = 'POST';

                $_POST = array_merge(
                        array(
                                'fbm_registration_submitted' => '1',
                                'fbm_registration_nonce'     => 'valid-nonce',
                                'fbm_registration_hp'        => '',
                                'fbm_registration_time'      => (string) $timestamp,
                                'fbm_first_name'             => 'Taylor',
                                'fbm_last_initial'           => 'J',
                                'fbm_email'                  => 'taylor@example.com',
                                'fbm_household_size'         => '3',
                                'fbm_registration_consent'   => '',
                        ),
                        $overrides
                );
        }
}
