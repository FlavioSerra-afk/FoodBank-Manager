<?php
/**
 * Registration submission handler tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Shortcodes;

use FoodBankManager\Registration\Editor\TemplateDefaults;
use FoodBankManager\Shortcodes\RegistrationForm;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * @covers \FoodBankManager\Shortcodes\RegistrationForm
 */
final class RegistrationSubmitTest extends TestCase {
        private \wpdb $wpdb;
        private SpyWelcomeMailer $welcomeMailer;
        private SpyNotificationMailer $notificationMailer;

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
                $_FILES  = array();

                $this->welcomeMailer      = new SpyWelcomeMailer();
                $this->notificationMailer = new SpyNotificationMailer();

                RegistrationForm::set_mailer_override(
                        fn() => $this->welcomeMailer
                );
                RegistrationForm::set_notification_override(
                        fn() => $this->notificationMailer
                );
        }

        protected function tearDown(): void {
                RegistrationForm::set_mailer_override( null );
                RegistrationForm::set_notification_override( null );

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
                $_FILES  = array();

                parent::tearDown();
        }

        public function test_missing_required_fields_returns_errors(): void {
                $schema = $this->schema();
                $settings = TemplateDefaults::settings();

                $_SERVER['REQUEST_METHOD'] = 'POST';
                $_POST = array(
                        'fbm_registration_submitted' => '1',
                        'fbm_registration_nonce'     => 'valid-nonce',
                        'fbm_registration_hp'        => '',
                        'fbm_registration_time'      => (string) ( time() - 10 ),
                        'fbm_first_name'             => '',
                        'fbm_last_initial'           => '',
                        'fbm_email'                  => 'invalid',
                );

                $result = $this->handle_submission( $schema['fields'], $settings );

                $this->assertFalse( $result['success'] );
                $this->assertContains( 'First name is required.', $result['errors'] );
                $this->assertContains( 'Last initial must be a single letter.', $result['errors'] );
                $this->assertContains( 'A valid email address is required.', $result['errors'] );
        }

        public function test_successful_submission_assigns_role_and_sends_emails(): void {
                $schema   = $this->schema();
                $settings = TemplateDefaults::settings();
                $consent  = $schema['fields']['fbm_registration_consent']['options'][0]['value'] ?? '1';

                $_SERVER['REQUEST_METHOD'] = 'POST';
                $_POST = array(
                        'fbm_registration_submitted' => '1',
                        'fbm_registration_nonce'     => 'valid-nonce',
                        'fbm_registration_hp'        => '',
                        'fbm_registration_time'      => (string) ( time() - 60 ),
                        'fbm_first_name'             => 'Jordan',
                        'fbm_last_initial'           => 'K',
                        'fbm_email'                  => 'jordan@example.com',
                        'fbm_household_size'         => '4',
                        'fbm_registration_consent'   => $consent,
                );

                $result = $this->handle_submission( $schema['fields'], $settings );

                $this->assertTrue( $result['success'] );
                $this->assertSame( 1, $this->welcomeMailer->send_calls );
                $this->assertSame( 1, $this->notificationMailer->send_calls );

                $user = get_user_by( 'email', 'jordan@example.com' );
                $this->assertInstanceOf( \WP_User::class, $user );
                $this->assertContains( 'foodbank_member', $user->roles );
        }

        public function test_file_upload_exceeding_limit_returns_field_error(): void {
                $schema   = $this->schema();
                $settings = TemplateDefaults::settings();

                $_SERVER['REQUEST_METHOD'] = 'POST';
                $_POST = array(
                        'fbm_registration_submitted' => '1',
                        'fbm_registration_nonce'     => 'valid-nonce',
                        'fbm_registration_hp'        => '',
                        'fbm_registration_time'      => (string) ( time() - 60 ),
                        'fbm_first_name'             => 'Taylor',
                        'fbm_last_initial'           => 'L',
                        'fbm_email'                  => 'taylor@example.com',
                        'fbm_household_size'         => '2',
                );

                $_FILES['fbm_proof_of_address'] = array(
                        'name'     => 'document.pdf',
                        'type'     => 'application/pdf',
                        'tmp_name' => '/tmp/fake',
                        'error'    => 0,
                        'size'     => 10485760,
                );

                $result = $this->handle_submission( $schema['fields'], $settings );

                $this->assertFalse( $result['success'] );
                $this->assertArrayHasKey( 'fbm_proof_of_address', $result['field_errors'] );
                $this->assertContains( 'Uploaded file exceeds the allowed size.', $result['field_errors']['fbm_proof_of_address'] );
        }

        /**
         * Resolve the template schema for tests.
         *
         * @return array{template:string,fields:array<string,array<string,mixed>>,warnings:array<int,string>}
         */
        private function schema(): array {
                $method = new ReflectionMethod( RegistrationForm::class, 'resolve_schema' );
                $method->setAccessible( true );

                return $method->invoke( null, TemplateDefaults::template() );
        }

        /**
         * Invoke the submission handler via reflection.
         *
         * @param array<string,array<string,mixed>> $fields Field schema.
         * @param array<string,mixed>               $settings Registration settings.
         *
         * @return array<string,mixed>
         */
        private function handle_submission( array $fields, array $settings ): array {
                $method = new ReflectionMethod( RegistrationForm::class, 'handle_submission' );
                $method->setAccessible( true );

                return $method->invoke( null, $fields, $settings );
        }
}

final class SpyWelcomeMailer {
        public int $send_calls = 0;

        /**
         * @var array<int,string>|null
         */
        public ?array $last_args = null;

        public function send( string $email, string $first_name, string $member_reference, string $token ): bool {
                ++$this->send_calls;
                $this->last_args = array( $email, $first_name, $member_reference, $token );

                return true;
        }
}

final class SpyNotificationMailer {
        public int $send_calls = 0;

        /**
         * @var array<int,string>|null
         */
        public ?array $last_args = null;

        public function send( string $member_reference, string $first_name, string $last_initial, string $email, string $status ): void {
                ++$this->send_calls;
                $this->last_args = array( $member_reference, $first_name, $last_initial, $email, $status );
        }
}
