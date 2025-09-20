<?php
// phpcs:ignoreFile
/**
 * Registration form shortcode tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Registration;

use FoodBankManager\Registration\Editor\TemplateDefaults;
use FoodBankManager\Registration\MembersRepository;
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
 * Test double for capturing notification dispatches.
 */
final class SpyNotificationMailer {
	public int $send_calls = 0;

		/**
		 * @var array{0:string,1:string,2:string,3:string,4:string}|null
		 */
	public ?array $last_args = null;

	public function send( string $member_reference, string $first_name, string $last_initial, string $email, string $status ): void {
			++$this->send_calls;
			$this->last_args = array( $member_reference, $first_name, $last_initial, $email, $status );
	}
}

/**
 * @covers \FoodBankManager\Shortcodes\RegistrationForm
 */
final class RegistrationFormTest extends TestCase {
	private \wpdb $wpdb;
	private SpyWelcomeMailer $mailer;
	private SpyNotificationMailer $notification;

        protected function setUp(): void {
                        parent::setUp();

                        $this->wpdb                  = new \wpdb();
			$GLOBALS['wpdb']             = $this->wpdb;
			$GLOBALS['fbm_users']        = array();
			$GLOBALS['fbm_roles']        = array();
			$GLOBALS['fbm_next_user_id'] = 1;
			$GLOBALS['fbm_test_nonces']  = array(
				'fbm_registration_submit' => 'valid-nonce',
			);

                        $GLOBALS['fbm_options'] = array();
                        $GLOBALS['fbm_enqueued_styles']     = array();
                        $GLOBALS['fbm_registered_styles']   = array();
                        $GLOBALS['fbm_enqueued_scripts']    = array();
                        $GLOBALS['fbm_registered_scripts']  = array();
                        $GLOBALS['fbm_localized_scripts']   = array();

                        $_SERVER                   = array();
                        $_POST                     = array();
			$GLOBALS['fbm_transients'] = array();

			$this->mailer = new SpyWelcomeMailer();
			RegistrationForm::set_mailer_override(
				fn() => $this->mailer
			);

			$this->notification = new SpyNotificationMailer();
			RegistrationForm::set_notification_override(
				fn() => $this->notification
			);
	}

	protected function tearDown(): void {
			RegistrationForm::set_mailer_override( null );
                        RegistrationForm::set_notification_override( null );

                        unset( $GLOBALS['wpdb'], $GLOBALS['fbm_test_nonces'], $GLOBALS['fbm_users'], $GLOBALS['fbm_roles'], $GLOBALS['fbm_next_user_id'], $GLOBALS['fbm_options'] );
                        unset( $GLOBALS['fbm_enqueued_styles'], $GLOBALS['fbm_registered_styles'], $GLOBALS['fbm_enqueued_scripts'], $GLOBALS['fbm_registered_scripts'], $GLOBALS['fbm_localized_scripts'] );

                        $_SERVER                   = array();
                        $_POST                     = array();
			$GLOBALS['fbm_transients'] = array();

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
			$this->assertSame( '', $result['values']['fbm_first_name'] );
			$this->assertSame( '', $result['values']['fbm_last_initial'] );
			$this->assertSame( '', $result['values']['fbm_email'] );
			$this->assertSame( '', $result['values']['fbm_household_size'] );
			$this->assertSame( array(), $result['values']['fbm_registration_consent'] );

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

			$user = get_user_by( 'email', 'robin@example.com' );
			$this->assertInstanceOf( \WP_User::class, $user );
			$this->assertContains( 'foodbank_member', $user->roles );

			$last_prepare = $this->wpdb->get_last_prepare();
			$this->assertIsArray( $last_prepare );
			$this->assertStringContainsString( 'member_reference = %s', $last_prepare['query'] );
			$this->assertMatchesRegularExpression( '/^FBM-/', (string) ( $last_prepare['args'][0] ?? '' ) );
	}

		/**
		 * Pending registrations should not trigger immediate welcome emails.
		 */
	public function test_pending_registration_requires_manual_approval(): void {
			update_option(
				'fbm_settings',
				array(
					'registration' => array(
						'auto_approve' => false,
					),
				)
			);

			$this->prepare_valid_submission(
				array(
					'fbm_first_name'        => 'Morgan',
					'fbm_last_initial'      => 'T',
					'fbm_email'             => 'morgan@example.com',
					'fbm_registration_time' => (string) ( time() - 120 ),
				)
			);

			$result = $this->invoke_handle_submission();

			$this->assertTrue( $result['success'] );
			$this->assertSame(
				'Thank you for registering. Our team will review your application and send your QR code once approved.',
				$result['message']
			);
			$this->assertSame( 0, $this->mailer->send_calls );
			$this->assertEmpty( $this->wpdb->tokens );
			$this->assertCount( 1, $this->wpdb->members );

			$member = reset( $this->wpdb->members );
			$this->assertSame( MembersRepository::STATUS_PENDING, $member['status'] ?? '' );
	}

		/**
		 * Re-registering a member should retain existing user roles while adding FoodBank Member.
		 */
	public function test_reregistration_preserves_existing_user_roles(): void {
			$this->prepare_valid_submission(
				array(
					'fbm_first_name'        => 'Sasha',
					'fbm_last_initial'      => 'L',
					'fbm_email'             => 'sasha@example.com',
					'fbm_registration_time' => (string) ( time() - 120 ),
				)
			);

			$first = $this->invoke_handle_submission();

			$this->assertTrue( $first['success'] );

			$user = get_user_by( 'email', 'sasha@example.com' );
			$this->assertInstanceOf( \WP_User::class, $user );
			$this->assertContains( 'foodbank_member', $user->roles );

			$user->add_role( 'subscriber' );

			$this->prepare_valid_submission(
				array(
					'fbm_first_name'        => 'Sasha',
					'fbm_last_initial'      => 'L',
					'fbm_email'             => 'sasha@example.com',
					'fbm_registration_time' => (string) ( time() - 90 ),
				)
			);

			$second = $this->invoke_handle_submission();

			$this->assertTrue( $second['success'] );

			$updated = get_user_by( 'email', 'sasha@example.com' );
			$this->assertInstanceOf( \WP_User::class, $updated );
			$this->assertContains( 'subscriber', $updated->roles );
			$this->assertContains( 'foodbank_member', $updated->roles );
	}

		/**
		 * Optional consent should persist when provided.
		 */
	public function test_successful_submission_with_consent_records_timestamp(): void {
			$this->prepare_valid_submission(
				array(
					'fbm_first_name'           => 'Jordan',
					'fbm_last_initial'         => 'k',
					'fbm_email'                => 'jordan@example.com',
					'fbm_registration_time'    => (string) ( time() - 60 ),
					'fbm_registration_consent' => $this->consent_option_value(),
				)
			);

			$result = $this->invoke_handle_submission();

			$this->assertTrue( $result['success'] );
			$this->assertSame( array(), $result['values']['fbm_registration_consent'] );

			$this->assertCount( 1, $this->wpdb->members );
			$member = reset( $this->wpdb->members );

			$this->assertArrayHasKey( 'consent_recorded_at', $member );
			$this->assertNotSame( '', $member['consent_recorded_at'] );
			$this->assertNotFalse( strtotime( (string) $member['consent_recorded_at'] ) );
	}

	public function test_notification_mailer_invoked_on_success(): void {
			$this->prepare_valid_submission(
				array(
					'fbm_registration_time' => (string) ( time() - 120 ),
				)
			);

			$result = $this->invoke_handle_submission();

			$this->assertTrue( $result['success'] );
			$this->assertSame( 1, $this->notification->send_calls );
			$this->assertNotNull( $this->notification->last_args );
			$this->assertSame( 'active', $this->notification->last_args[4] );
	}

        public function test_household_size_negative_values_clamped(): void {
                        $this->prepare_valid_submission(
                                array(
                                        'fbm_household_size'    => '-5',
                                        'fbm_registration_time' => (string) ( time() - 120 ),
				)
			);

			$result = $this->invoke_handle_submission();

			$this->assertTrue( $result['success'] );

			$member = reset( $this->wpdb->members );

			$this->assertSame( 1, $member['household_size'] );
	}

		/**
		 * Submissions within the cooldown window must be rejected.
		 */
	public function test_submission_is_throttled_when_cooldown_is_active(): void {
			$_SERVER['REMOTE_ADDR'] = '203.0.113.10';

			$this->prepare_valid_submission(
				array(
					'fbm_registration_time' => (string) ( time() - 120 ),
				)
			);

			$fingerprint = strtolower( 'taylor@example.com' ) . '|203.0.113.10';
			$transient   = 'fbm_registration_cooldown_' . md5( $fingerprint );

                        set_transient( $transient, time(), 120 );

                        $result = $this->invoke_handle_submission();

                        $this->assertFalse( $result['success'] );
                        $this->assertContains( 'Please wait before submitting again.', $result['errors'] );
                        $this->assertSame( 'Please wait before submitting again.', $result['message'] );
        }

        public function test_prepare_condition_groups_filters_invalid_entries(): void {
                        $method = new ReflectionMethod( RegistrationForm::class, 'prepare_condition_groups' );
                        $method->setAccessible( true );

                        $fields = array(
                                'controller'        => array( 'name' => 'controller', 'type' => 'text', 'required' => true ),
                                'target_visible'    => array( 'name' => 'target_visible', 'type' => 'text' ),
                                'target_required'   => array( 'name' => 'target_required', 'type' => 'number' ),
                                'fbm_submit_button' => array( 'name' => 'fbm_submit_button', 'type' => 'submit' ),
                        );

                        $conditions = array(
                                'enabled' => true,
                                'groups'  => array(
                                        array(
                                                'operator'   => 'invalid',
                                                'conditions' => array(
                                                        array(
                                                                'field'    => 'controller',
                                                                'operator' => 'equals',
                                                                'value'    => 'Yes',
                                                        ),
                                                        array(
                                                                'field'    => 'controller',
                                                                'operator' => 'equals',
                                                                'value'    => '',
                                                        ),
                                                        array(
                                                                'field'    => 'missing',
                                                                'operator' => 'equals',
                                                                'value'    => 'ignored',
                                                        ),
                                                        array(
                                                                'field'    => 'controller',
                                                                'operator' => 'empty',
                                                                'value'    => 'ignored',
                                                        ),
                                                ),
                                                'actions'    => array(
                                                        array(
                                                                'type'   => 'show',
                                                                'target' => 'target_visible',
                                                        ),
                                                        array(
                                                                'type'   => 'hide',
                                                                'target' => 'fbm_submit_button',
                                                        ),
                                                        array(
                                                                'type'   => 'optional',
                                                                'target' => 'unknown_target',
                                                        ),
                                                ),
                                        ),
                                ),
                        );

                        /** @var array<int,array<string,mixed>> $result */
                        $result = $method->invoke( null, $conditions, $fields );

                        $this->assertSame(
                                array(
                                        array(
                                                'operator'   => 'and',
                                                'conditions' => array(
                                                        array(
                                                                'field'      => 'controller',
                                                                'operator'   => 'equals',
                                                                'value'      => 'Yes',
                                                                'field_type' => 'text',
                                                        ),
                                                        array(
                                                                'field'      => 'controller',
                                                                'operator'   => 'empty',
                                                                'value'      => '',
                                                                'field_type' => 'text',
                                                        ),
                                                ),
                                                'actions'    => array(
                                                        array(
                                                                'type'        => 'show',
                                                                'target'      => 'target_visible',
                                                                'target_type' => 'text',
                                                        ),
                                                ),
                                        ),
                                ),
                                $result
                        );
        }

        public function test_evaluate_condition_state_handles_operators(): void {
                        $prepare = new ReflectionMethod( RegistrationForm::class, 'prepare_condition_groups' );
                        $prepare->setAccessible( true );

                        $evaluate = new ReflectionMethod( RegistrationForm::class, 'evaluate_condition_state' );
                        $evaluate->setAccessible( true );

                        $fields = array(
                                'controller'        => array( 'name' => 'controller', 'type' => 'text' ),
                                'toggle_show'       => array( 'name' => 'toggle_show', 'type' => 'text' ),
                                'toggle_hide'       => array( 'name' => 'toggle_hide', 'type' => 'text' ),
                                'color_target'      => array( 'name' => 'color_target', 'type' => 'text' ),
                                'optional_hidden'   => array( 'name' => 'optional_hidden', 'type' => 'text' ),
                                'optional_visible'  => array( 'name' => 'optional_visible', 'type' => 'text' ),
                                'multi_field'       => array( 'name' => 'multi_field', 'type' => 'checkbox' ),
                                'optional_field'    => array( 'name' => 'optional_field', 'type' => 'text' ),
                        );

                        $conditions = array(
                                'enabled' => true,
                                'groups'  => array(
                                        array(
                                                'operator'   => 'and',
                                                'conditions' => array(
                                                        array(
                                                                'field'    => 'controller',
                                                                'operator' => 'equals',
                                                                'value'    => 'ready',
                                                        ),
                                                ),
                                                'actions'    => array(
                                                        array(
                                                                'type'   => 'show',
                                                                'target' => 'toggle_show',
                                                        ),
                                                ),
                                        ),
                                        array(
                                                'operator'   => 'and',
                                                'conditions' => array(
                                                        array(
                                                                'field'    => 'controller',
                                                                'operator' => 'not_equals',
                                                                'value'    => 'blocked',
                                                        ),
                                                ),
                                                'actions'    => array(
                                                        array(
                                                                'type'   => 'hide',
                                                                'target' => 'toggle_hide',
                                                        ),
                                                ),
                                        ),
                                        array(
                                                'operator'   => 'and',
                                                'conditions' => array(
                                                        array(
                                                                'field'    => 'multi_field',
                                                                'operator' => 'contains',
                                                                'value'    => 'blue',
                                                        ),
                                                ),
                                                'actions'    => array(
                                                        array(
                                                                'type'   => 'hide',
                                                                'target' => 'color_target',
                                                        ),
                                                ),
                                        ),
                                        array(
                                                'operator'   => 'and',
                                                'conditions' => array(
                                                        array(
                                                                'field'    => 'optional_field',
                                                                'operator' => 'empty',
                                                        ),
                                                ),
                                                'actions'    => array(
                                                        array(
                                                                'type'   => 'hide',
                                                                'target' => 'optional_hidden',
                                                        ),
                                                ),
                                        ),
                                        array(
                                                'operator'   => 'and',
                                                'conditions' => array(
                                                        array(
                                                                'field'    => 'optional_field',
                                                                'operator' => 'not_empty',
                                                        ),
                                                ),
                                                'actions'    => array(
                                                        array(
                                                                'type'   => 'show',
                                                                'target' => 'optional_visible',
                                                        ),
                                                ),
                                        ),
                                ),
                        );

                        $groups = $prepare->invoke( null, $conditions, $fields );

                        $values = array(
                                'controller'      => 'READY',
                                'multi_field'     => array( 'green', 'blue' ),
                                'optional_field'  => '',
                        );

                        /** @var array<string,array<string,bool>> $visibility */
                        $visibility = $evaluate->invoke( null, $groups, $fields, $values );

                        $this->assertTrue( $visibility['toggle_show']['visible'] );
                        $this->assertFalse( $visibility['toggle_hide']['visible'] );
                        $this->assertFalse( $visibility['color_target']['visible'] );
                        $this->assertFalse( $visibility['optional_hidden']['visible'] );
                        $this->assertFalse( $visibility['optional_visible']['visible'] );

                        $values['multi_field']    = array( 'red' );
                        $values['optional_field'] = 'filled';

                        /** @var array<string,array<string,bool>> $updated */
                        $updated = $evaluate->invoke( null, $groups, $fields, $values );

                        $this->assertTrue( $updated['toggle_show']['visible'] );
                        $this->assertFalse( $updated['toggle_hide']['visible'] );
                        $this->assertTrue( $updated['color_target']['visible'] );
                        $this->assertTrue( $updated['optional_visible']['visible'] );
                        $this->assertTrue( $updated['optional_hidden']['visible'] );
        }

        public function test_evaluate_condition_state_supports_numeric_and_date_comparisons(): void {
                        $prepare = new ReflectionMethod( RegistrationForm::class, 'prepare_condition_groups' );
                        $prepare->setAccessible( true );

                        $evaluate = new ReflectionMethod( RegistrationForm::class, 'evaluate_condition_state' );
                        $evaluate->setAccessible( true );

                        $fields = array(
                                'numeric_field'   => array( 'name' => 'numeric_field', 'type' => 'number' ),
                                'date_field'      => array( 'name' => 'date_field', 'type' => 'date' ),
                                'target_numeric'  => array( 'name' => 'target_numeric', 'type' => 'text' ),
                                'target_date'     => array( 'name' => 'target_date', 'type' => 'text' ),
                        );

                        $conditions = array(
                                'enabled' => true,
                                'groups'  => array(
                                        array(
                                                'operator'   => 'and',
                                                'conditions' => array(
                                                        array(
                                                                'field'    => 'numeric_field',
                                                                'operator' => 'gt',
                                                                'value'    => '10',
                                                        ),
                                                ),
                                                'actions'    => array(
                                                        array(
                                                                'type'   => 'show',
                                                                'target' => 'target_numeric',
                                                        ),
                                                ),
                                        ),
                                        array(
                                                'operator'   => 'and',
                                                'conditions' => array(
                                                        array(
                                                                'field'    => 'date_field',
                                                                'operator' => 'lte',
                                                                'value'    => '2024-12-31',
                                                        ),
                                                ),
                                                'actions'    => array(
                                                        array(
                                                                'type'   => 'show',
                                                                'target' => 'target_date',
                                                        ),
                                                ),
                                        ),
                                ),
                        );

                        $groups = $prepare->invoke( null, $conditions, $fields );

                        $values = array(
                                'numeric_field' => '9',
                                'date_field'    => '2024-10-10',
                        );

                        /** @var array<string,array<string,bool>> $state */
                        $state = $evaluate->invoke( null, $groups, $fields, $values );

                        $this->assertFalse( $state['target_numeric']['visible'] );
                        $this->assertTrue( $state['target_date']['visible'] );

                        $values['numeric_field'] = '12';
                        $values['date_field']    = '2025-01-01';

                        /** @var array<string,array<string,bool>> $updated */
                        $updated = $evaluate->invoke( null, $groups, $fields, $values );

                        $this->assertTrue( $updated['target_numeric']['visible'] );
                        $this->assertFalse( $updated['target_date']['visible'] );
        }

        public function test_evaluate_condition_state_handles_or_groups(): void {
                        $prepare = new ReflectionMethod( RegistrationForm::class, 'prepare_condition_groups' );
                        $prepare->setAccessible( true );

                        $evaluate = new ReflectionMethod( RegistrationForm::class, 'evaluate_condition_state' );
                        $evaluate->setAccessible( true );

                        $fields = array(
                                'switch'       => array( 'name' => 'switch', 'type' => 'text' ),
                                'or_target'    => array( 'name' => 'or_target', 'type' => 'text' ),
                        );

                        $conditions = array(
                                'enabled' => true,
                                'groups'  => array(
                                        array(
                                                'operator'   => 'or',
                                                'conditions' => array(
                                                        array(
                                                                'field'    => 'switch',
                                                                'operator' => 'equals',
                                                                'value'    => 'yes',
                                                        ),
                                                        array(
                                                                'field'    => 'switch',
                                                                'operator' => 'equals',
                                                                'value'    => 'maybe',
                                                        ),
                                                ),
                                                'actions'    => array(
                                                        array(
                                                                'type'   => 'show',
                                                                'target' => 'or_target',
                                                        ),
                                                ),
                                        ),
                                ),
                        );

                        $groups = $prepare->invoke( null, $conditions, $fields );

                        /** @var array<string,array<string,bool>> $state */
                        $state = $evaluate->invoke( null, $groups, $fields, array( 'switch' => 'no' ) );
                        $this->assertFalse( $state['or_target']['visible'] );

                        /** @var array<string,array<string,bool>> $updated */
                        $updated = $evaluate->invoke( null, $groups, $fields, array( 'switch' => 'maybe' ) );
                        $this->assertTrue( $updated['or_target']['visible'] );
        }

        public function test_evaluate_condition_state_applies_conflict_resolution(): void {
                        $prepare = new ReflectionMethod( RegistrationForm::class, 'prepare_condition_groups' );
                        $prepare->setAccessible( true );

                        $evaluate = new ReflectionMethod( RegistrationForm::class, 'evaluate_condition_state' );
                        $evaluate->setAccessible( true );

                        $fields = array(
                                'state'       => array( 'name' => 'state', 'type' => 'text' ),
                                'mode'        => array( 'name' => 'mode', 'type' => 'text' ),
                                'target'      => array( 'name' => 'target', 'type' => 'text', 'required' => true ),
                        );

                        $conditions = array(
                                'enabled' => true,
                                'groups'  => array(
                                        array(
                                                'operator'   => 'and',
                                                'conditions' => array(
                                                        array(
                                                                'field'    => 'state',
                                                                'operator' => 'equals',
                                                                'value'    => 'on',
                                                        ),
                                                ),
                                                'actions'    => array(
                                                        array(
                                                                'type'   => 'show',
                                                                'target' => 'target',
                                                        ),
                                                ),
                                        ),
                                        array(
                                                'operator'   => 'and',
                                                'conditions' => array(
                                                        array(
                                                                'field'    => 'state',
                                                                'operator' => 'equals',
                                                                'value'    => 'on',
                                                        ),
                                                ),
                                                'actions'    => array(
                                                        array(
                                                                'type'   => 'hide',
                                                                'target' => 'target',
                                                        ),
                                                ),
                                        ),
                                        array(
                                                'operator'   => 'and',
                                                'conditions' => array(
                                                        array(
                                                                'field'    => 'mode',
                                                                'operator' => 'equals',
                                                                'value'    => 'require',
                                                        ),
                                                ),
                                                'actions'    => array(
                                                        array(
                                                                'type'   => 'require',
                                                                'target' => 'target',
                                                        ),
                                                ),
                                        ),
                                        array(
                                                'operator'   => 'and',
                                                'conditions' => array(
                                                        array(
                                                                'field'    => 'mode',
                                                                'operator' => 'equals',
                                                                'value'    => 'require',
                                                        ),
                                                ),
                                                'actions'    => array(
                                                        array(
                                                                'type'   => 'optional',
                                                                'target' => 'target',
                                                        ),
                                                ),
                                        ),
                                ),
                        );

                        $groups = $prepare->invoke( null, $conditions, $fields );

                        /** @var array<string,array<string,bool>> $state */
                        $state = $evaluate->invoke( null, $groups, $fields, array( 'state' => 'on', 'mode' => 'require' ) );

                        $this->assertFalse( $state['target']['visible'] );
                        $this->assertFalse( $state['target']['required'] );
        }

        public function test_optional_action_makes_field_optional(): void {
                        $schema = $this->schema();
                        $fields = $schema['fields'];
                        $fields['fbm_registration_consent']['required'] = true;
                        $fields['fbm_registration_consent']['type']      = 'checkbox';

                        $settings = TemplateDefaults::settings();
                        $settings['conditions'] = array(
                                'enabled' => true,
                                'groups'  => array(
                                        array(
                                                'operator'   => 'and',
                                                'conditions' => array(
                                                        array(
                                                                'field'    => 'fbm_first_name',
                                                                'operator' => 'equals',
                                                                'value'    => 'optional',
                                                        ),
                                                ),
                                                'actions'    => array(
                                                        array(
                                                                'type'   => 'show',
                                                                'target' => 'fbm_registration_consent',
                                                        ),
                                                ),
                                        ),
                                        array(
                                                'operator'   => 'and',
                                                'conditions' => array(
                                                        array(
                                                                'field'    => 'fbm_first_name',
                                                                'operator' => 'equals',
                                                                'value'    => 'optional',
                                                        ),
                                                ),
                                                'actions'    => array(
                                                        array(
                                                                'type'   => 'optional',
                                                                'target' => 'fbm_registration_consent',
                                                        ),
                                                ),
                                        ),
                                ),
                        );

                        $this->prepare_valid_submission(
                                array(
                                        'fbm_first_name'        => 'optional',
                                        'fbm_registration_time' => (string) ( time() - 120 ),
                                )
                        );
                        unset( $_POST['fbm_registration_consent'] );

                        $result = $this->invoke_handle_submission( $fields, $settings );

                        $this->assertTrue( $result['success'] );
                        $this->assertArrayNotHasKey( 'fbm_registration_consent', array_filter( $result['field_errors'] ) );
        }

        public function test_hidden_field_submission_ignored_when_rule_hides_target(): void {
                        $settings = TemplateDefaults::settings();
                        $settings['conditions'] = array(
                                'enabled' => true,
                                'groups'  => array(
                                        array(
                                                'operator'   => 'and',
                                                'conditions' => array(
                                                        array(
                                                                'field'    => 'fbm_first_name',
                                                                'operator' => 'equals',
                                                                'value'    => 'hide',
                                                        ),
                                                ),
                                                'actions'    => array(
                                                        array(
                                                                'type'   => 'hide',
                                                                'target' => 'fbm_household_size',
                                                        ),
                                                ),
                                        ),
                                ),
                        );

                        $this->prepare_valid_submission(
                                array(
                                        'fbm_first_name'        => 'Hide',
                                        'fbm_household_size'    => '9',
                                        'fbm_registration_time' => (string) ( time() - 120 ),
                                )
                        );

                        $result = $this->invoke_handle_submission( null, $settings );

                        $this->assertTrue( $result['success'] );
                        $member = reset( $this->wpdb->members );
                        $this->assertSame( 1, $member['household_size'] );
        }

        public function test_required_field_shown_by_rule_requires_value(): void {
                        $schema = $this->schema();
                        $fields = $schema['fields'];
                        $fields['fbm_registration_consent']['required'] = true;
                        $fields['fbm_registration_consent']['type']      = 'checkbox';

                        $settings = TemplateDefaults::settings();
                        $settings['conditions'] = array(
                                'enabled' => true,
                                'groups'  => array(
                                        array(
                                                'operator'   => 'and',
                                                'conditions' => array(
                                                        array(
                                                                'field'    => 'fbm_first_name',
                                                                'operator' => 'equals',
                                                                'value'    => 'show',
                                                        ),
                                                ),
                                                'actions'    => array(
                                                        array(
                                                                'type'   => 'show',
                                                                'target' => 'fbm_registration_consent',
                                                        ),
                                                ),
                                        ),
                                ),
                        );

                        $this->prepare_valid_submission(
                                array(
                                        'fbm_first_name'        => 'show',
                                        'fbm_registration_time' => (string) ( time() - 120 ),
                                )
                        );
                        unset( $_POST['fbm_registration_consent'] );

                        $result = $this->invoke_handle_submission( $fields, $settings );

                        $this->assertFalse( $result['success'] );
                        $this->assertArrayHasKey( 'fbm_registration_consent', $result['field_errors'] );
                        $this->assertContains( 'This field is required.', $result['field_errors']['fbm_registration_consent'] );
        }

        public function test_enqueue_assets_for_form_localizes_conditions(): void {
                        $method = new ReflectionMethod( RegistrationForm::class, 'enqueue_assets_for_form' );
                        $method->setAccessible( true );

                        $schema = array(
                                'fields' => array(
                                        'fbm_first_name' => array( 'name' => 'fbm_first_name', 'type' => 'text' ),
                                        'fbm_extra'      => array( 'name' => 'fbm_extra', 'type' => 'text' ),
                                ),
                        );

                        $settings = TemplateDefaults::settings();
                        $settings['conditions'] = array(
                                'enabled' => true,
                                'groups'  => array(
                                        array(
                                                'operator'   => 'and',
                                                'conditions' => array(
                                                        array(
                                                                'field'    => 'fbm_first_name',
                                                                'operator' => 'equals',
                                                                'value'    => 'trigger',
                                                        ),
                                                ),
                                                'actions'    => array(
                                                        array(
                                                                'type'   => 'hide',
                                                                'target' => 'fbm_extra',
                                                        ),
                                                ),
                                        ),
                                ),
                        );

                        $method->invoke( null, $schema, $settings );

                        $this->assertContains( 'fbm-registration-form', $GLOBALS['fbm_enqueued_scripts'] );
                        $this->assertContains( 'fbm-registration-form', $GLOBALS['fbm_enqueued_styles'] );
                        $this->assertArrayHasKey( 'fbm-registration-form', $GLOBALS['fbm_localized_scripts'] );

                        $localized = $GLOBALS['fbm_localized_scripts']['fbm-registration-form'];
                        $this->assertSame( 'fbmRegistrationForm', $localized['name'] );
                        $this->assertTrue( $localized['data']['conditions']['enabled'] );
                        $this->assertSame( 'fbm_extra', $localized['data']['conditions']['groups'][0]['actions'][0]['target'] );
        }

		/**
		 * Invoke the private handle_submission helper via reflection.
		 */
        private function invoke_handle_submission( ?array $fields = null, ?array $settings = null ): array {
                        $method = new ReflectionMethod( RegistrationForm::class, 'handle_submission' );
                        $method->setAccessible( true );

                        $schema = $this->schema();
                        $fields = $fields ?? $schema['fields'];
                        $settings = $settings ?? TemplateDefaults::settings();

                        /** @var array{success:bool,errors:array<int,string>,message:string,values:array<string,string>} */
                        return $method->invoke( null, $fields, $settings );
        }

		/**
		 * Resolve the default schema for tests.
		 *
		 * @return array{template:string,fields:array<string,array<string,mixed>>,warnings:array<int,string>}
		 */
	private function schema(): array {
			$method = new ReflectionMethod( RegistrationForm::class, 'resolve_schema' );
			$method->setAccessible( true );

			/** @var array{template:string,fields:array<string,array<string,mixed>>,warnings:array<int,string>} $schema */
			$schema = $method->invoke( null, TemplateDefaults::template() );

			return $schema;
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

		/**
		 * Resolve the canonical consent value from the default template.
		 */
	private function consent_option_value(): string {
			$schema = $this->schema();

		if (
					isset( $schema['fields']['fbm_registration_consent'] )
					&& is_array( $schema['fields']['fbm_registration_consent'] )
					&& isset( $schema['fields']['fbm_registration_consent']['options'][0]['value'] )
					&& is_string( $schema['fields']['fbm_registration_consent']['options'][0]['value'] )
			) {
				return $schema['fields']['fbm_registration_consent']['options'][0]['value'];
		}

			return '1';
	}
}
