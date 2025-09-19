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

        public function test_consent_timestamp_recorded_when_checkbox_checked(): void {
                $schema   = $this->schema();
                $settings = TemplateDefaults::settings();
                $consent  = $this->consent_value( $schema );

                $_SERVER['REQUEST_METHOD'] = 'POST';
                $_POST = array(
                        'fbm_registration_submitted' => '1',
                        'fbm_registration_nonce'     => 'valid-nonce',
                        'fbm_registration_hp'        => '',
                        'fbm_registration_time'      => (string) ( time() - 60 ),
                        'fbm_first_name'             => 'Morgan',
                        'fbm_last_initial'           => 'T',
                        'fbm_email'                  => 'morgan@example.com',
                        'fbm_household_size'         => '2',
                        'fbm_registration_consent'   => $consent,
                );

                $result = $this->handle_submission( $schema['fields'], $settings );

                $this->assertTrue( $result['success'] );

                $member = $this->last_member_row();
                $this->assertArrayHasKey( 'consent_recorded_at', $member );
                $this->assertNotSame( '', $member['consent_recorded_at'] );
        }

        public function test_consent_timestamp_not_recorded_when_unchecked(): void {
                $schema   = $this->schema();
                $settings = TemplateDefaults::settings();

                $_SERVER['REQUEST_METHOD'] = 'POST';
                $_POST = array(
                        'fbm_registration_submitted' => '1',
                        'fbm_registration_nonce'     => 'valid-nonce',
                        'fbm_registration_hp'        => '',
                        'fbm_registration_time'      => (string) ( time() - 60 ),
                        'fbm_first_name'             => 'Jamie',
                        'fbm_last_initial'           => 'Q',
                        'fbm_email'                  => 'jamie@example.com',
                        'fbm_household_size'         => '3',
                );

                $result = $this->handle_submission( $schema['fields'], $settings );

                $this->assertTrue( $result['success'] );

                $member = $this->last_member_row();
                $this->assertFalse( array_key_exists( 'consent_recorded_at', $member ) );
        }

        public function test_consent_timestamp_updates_on_resubmission(): void {
                $schema   = $this->schema();
                $settings = TemplateDefaults::settings();
                $consent  = $this->consent_value( $schema );

                $_SERVER['REQUEST_METHOD'] = 'POST';
                $_POST = array(
                        'fbm_registration_submitted' => '1',
                        'fbm_registration_nonce'     => 'valid-nonce',
                        'fbm_registration_hp'        => '',
                        'fbm_registration_time'      => (string) ( time() - 60 ),
                        'fbm_first_name'             => 'River',
                        'fbm_last_initial'           => 'S',
                        'fbm_email'                  => 'river@example.com',
                        'fbm_household_size'         => '4',
                );

                $initial = $this->handle_submission( $schema['fields'], $settings );
                $this->assertTrue( $initial['success'] );

                $first_member = $this->last_member_row();
                $this->assertFalse( array_key_exists( 'consent_recorded_at', $first_member ) );

                $_POST = array(
                        'fbm_registration_submitted' => '1',
                        'fbm_registration_nonce'     => 'valid-nonce',
                        'fbm_registration_hp'        => '',
                        'fbm_registration_time'      => (string) ( time() - 60 ),
                        'fbm_first_name'             => 'River',
                        'fbm_last_initial'           => 'S',
                        'fbm_email'                  => 'river@example.com',
                        'fbm_household_size'         => '4',
                        'fbm_registration_consent'   => $consent,
                );

                $resubmission = $this->handle_submission( $schema['fields'], $settings );

                $this->assertTrue( $resubmission['success'] );

                $updated_member = $this->last_member_row();
                $this->assertArrayHasKey( 'consent_recorded_at', $updated_member );
                $this->assertNotSame( '', $updated_member['consent_recorded_at'] );
        }

        public function test_consent_checkbox_custom_label_array_counts_as_truthy(): void {
                $schema   = $this->schema();
                $settings = TemplateDefaults::settings();

                $schema['fields']['fbm_registration_consent']['options'] = array(
                        array(
                                'value' => 'custom-label',
                                'label' => 'Custom label',
                        ),
                );

                $_SERVER['REQUEST_METHOD'] = 'POST';
                $_POST = array(
                        'fbm_registration_submitted' => '1',
                        'fbm_registration_nonce'     => 'valid-nonce',
                        'fbm_registration_hp'        => '',
                        'fbm_registration_time'      => (string) ( time() - 60 ),
                        'fbm_first_name'             => 'Lee',
                        'fbm_last_initial'           => 'N',
                        'fbm_email'                  => 'lee@example.com',
                        'fbm_household_size'         => '3',
                        'fbm_registration_consent'   => array( 'custom-label' ),
                );

                $result = $this->handle_submission( $schema['fields'], $settings );

                $this->assertTrue( $result['success'] );

                $member = $this->last_member_row();
                $this->assertArrayHasKey( 'consent_recorded_at', $member );
                $this->assertNotSame( '', $member['consent_recorded_at'] );
        }

        public function test_household_size_clamped_to_minimum(): void {
                $schema   = $this->schema();
                $settings = TemplateDefaults::settings();
                $consent  = $this->consent_value( $schema );

                $_SERVER['REQUEST_METHOD'] = 'POST';
                $_POST = array(
                        'fbm_registration_submitted' => '1',
                        'fbm_registration_nonce'     => 'valid-nonce',
                        'fbm_registration_hp'        => '',
                        'fbm_registration_time'      => (string) ( time() - 60 ),
                        'fbm_first_name'             => 'Devon',
                        'fbm_last_initial'           => 'A',
                        'fbm_email'                  => 'devon@example.com',
                        'fbm_household_size'         => '-5',
                        'fbm_registration_consent'   => $consent,
                );

                $result = $this->handle_submission( $schema['fields'], $settings );

                $this->assertTrue( $result['success'] );

                $member = $this->last_member_row();
                $this->assertSame( 1, $member['household_size'] );
        }

        public function test_household_size_clamped_to_maximum(): void {
                $schema   = $this->schema();
                $settings = TemplateDefaults::settings();
                $consent  = $this->consent_value( $schema );
                $max      = (int) ( $schema['fields']['fbm_household_size']['range']['max'] ?? 12 );

                $_SERVER['REQUEST_METHOD'] = 'POST';
                $_POST = array(
                        'fbm_registration_submitted' => '1',
                        'fbm_registration_nonce'     => 'valid-nonce',
                        'fbm_registration_hp'        => '',
                        'fbm_registration_time'      => (string) ( time() - 60 ),
                        'fbm_first_name'             => 'Robin',
                        'fbm_last_initial'           => 'C',
                        'fbm_email'                  => 'robin@example.com',
                        'fbm_household_size'         => '99',
                        'fbm_registration_consent'   => $consent,
                );

                $result = $this->handle_submission( $schema['fields'], $settings );

                $this->assertTrue( $result['success'] );

                $member = $this->last_member_row();
                $this->assertSame( $max, $member['household_size'] );
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
         * Retrieve the most recently stored member row.
         *
         * @return array<string,mixed>
         */
        private function last_member_row(): array {
                $members = $this->wpdb->members;
                $this->assertNotEmpty( $members, 'Expected at least one stored member.' );

                $last = end( $members );
                $this->assertIsArray( $last );

                return $last;
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

        /**
         * Resolve the sanitized consent option value for tests.
         *
         * @param array<string,array<string,mixed>> $schema_fields Schema fields array.
         */
        private function consent_value( array $schema_fields ): string {
                if ( isset( $schema_fields['fields'] ) && is_array( $schema_fields['fields'] ) ) {
                        $schema_fields = $schema_fields['fields'];
                }

                $options = $schema_fields['fbm_registration_consent']['options'] ?? array();

                if ( ! empty( $options ) && isset( $options[0]['value'] ) ) {
                        return (string) $options[0]['value'];
                }

                return '1';
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
