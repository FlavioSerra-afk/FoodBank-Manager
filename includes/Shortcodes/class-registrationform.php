<?php
/**
 * Registration form shortcode handler.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Shortcodes;

use FoodBankManager\Diagnostics\MailFailureLog;
use FoodBankManager\Email\WelcomeMailer;
use FoodBankManager\Registration\MembersRepository;
use FoodBankManager\Registration\RegistrationService;
use FoodBankManager\Registration\RegistrationSettings;
use FoodBankManager\Token\TokenRepository;
use FoodBankManager\Token\TokenService;
use wpdb;
use function add_shortcode;
use function apply_filters;
use function esc_html__;
use function filter_input;
use function filter_var;
use function is_email;
use function is_readable;
use function is_string;
use function md5;
use function ob_get_clean;
use function ob_start;
use function preg_match;
use function sanitize_email;
use function sanitize_text_field;
use function wp_kses_post;
use function strtolower;
use function substr;
use function strtoupper;
use function time;
use function delete_transient;
use function get_option;
use function get_transient;
use function set_transient;
use function wp_nonce_field;
use function wp_verify_nonce;

use const FILTER_SANITIZE_EMAIL;
use const FILTER_SANITIZE_FULL_SPECIAL_CHARS;
use const FILTER_SANITIZE_NUMBER_INT;
use const FILTER_SANITIZE_URL;
use const FILTER_UNSAFE_RAW;
use const INPUT_POST;
use const INPUT_SERVER;

/**
 * Renders the registration shortcode.
 */
final class RegistrationForm {

	private const SHORTCODE                   = 'fbm_registration_form';
	private const NONCE_ACTION                = 'fbm_registration_submit';
	private const NONCE_FIELD                 = 'fbm_registration_nonce';
	private const HONEYPOT_FIELD              = 'fbm_registration_hp';
	private const TIME_TRAP_FIELD             = 'fbm_registration_time';
	private const SUBMIT_FIELD                = 'fbm_registration_submitted';
	private const FIELD_FIRST_NAME            = 'fbm_first_name';
	private const FIELD_LAST_INITIAL          = 'fbm_last_initial';
	private const FIELD_EMAIL                 = 'fbm_email';
	private const FIELD_HOUSEHOLD_SIZE        = 'fbm_household_size';
	private const FIELD_CONSENT               = 'fbm_registration_consent';
	private const DEFAULT_HOUSEHOLD_SIZE      = '1';
	private const MIN_TIME_TRAP_THRESHOLD     = 5;
	private const LAST_INITIAL_PATTERN        = '/^[A-Z]$/';
	private const SUBMISSION_COOLDOWN_DEFAULT = 120;
	private const SUBMISSION_COOLDOWN_FILTER  = 'fbm_registration_submission_cooldown';
	private const OPTION_HEADLINE             = 'fbm_reg_label_headline';
	private const OPTION_SUBMIT               = 'fbm_reg_label_submit';
	private const OPTION_SUCCESS_AUTO         = 'fbm_reg_copy_success_auto';
	private const OPTION_SUCCESS_PENDING      = 'fbm_reg_copy_success_pending';
	private const OPTION_HONEYPOT             = 'fbm_reg_enable_honeypot';

				/**
				 * Optional mailer factory override for testing.
				 *
				 * @var callable|null
				 */
	private static $mailer_factory = null;

		/**
		 * Override the welcome mailer dependency.
		 *
		 * @internal
		 *
		 * @param callable|null $factory Factory returning a mailer instance for test scenarios.
		 */
	public static function set_mailer_override( ?callable $factory ): void {
			self::$mailer_factory = $factory;
	}

		/**
		 * Register the shortcode handler.
		 */
	public static function register(): void {
					add_shortcode( self::SHORTCODE, array( self::class, 'render' ) );
	}

		/**
		 * Resolve stored form configuration.
		 *
		 * @return array{labels:array{headline:string,submit:string},copy:array{success_auto:string,success_pending:string},honeypot:bool}
		 */
	private static function form_options(): array {
			$defaults = self::default_options();

			$headline = get_option( self::OPTION_HEADLINE, $defaults['labels']['headline'] );
			$submit   = get_option( self::OPTION_SUBMIT, $defaults['labels']['submit'] );
			$auto     = get_option( self::OPTION_SUCCESS_AUTO, $defaults['copy']['success_auto'] );
			$pending  = get_option( self::OPTION_SUCCESS_PENDING, $defaults['copy']['success_pending'] );
			$honeypot = get_option( self::OPTION_HONEYPOT, $defaults['honeypot'] ? 1 : 0 );

			$labels = array(
				'headline' => is_string( $headline ) && '' !== $headline ? $headline : $defaults['labels']['headline'],
				'submit'   => is_string( $submit ) && '' !== $submit ? $submit : $defaults['labels']['submit'],
			);

			$copy = array(
				'success_auto'    => is_string( $auto ) && '' !== $auto ? wp_kses_post( $auto ) : $defaults['copy']['success_auto'],
				'success_pending' => is_string( $pending ) && '' !== $pending ? wp_kses_post( $pending ) : $defaults['copy']['success_pending'],
			);

			return array(
				'labels'   => $labels,
				'copy'     => $copy,
				'honeypot' => (bool) $honeypot,
			);
	}

		/**
		 * Default form option values.
		 *
		 * @return array{labels:array{headline:string,submit:string},copy:array{success_auto:string,success_pending:string},honeypot:bool}
		 */
	public static function default_options(): array {
			return array(
				'labels'   => array(
					'headline' => __( 'Register for weekly collection', 'foodbank-manager' ),
					'submit'   => __( 'Submit registration', 'foodbank-manager' ),
				),
				'copy'     => array(
					'success_auto'    => __( 'Thank you for registering. We have emailed your check-in QR code.', 'foodbank-manager' ),
					'success_pending' => __( 'Thank you for registering. Our team will review your application and send your QR code once approved.', 'foodbank-manager' ),
				),
				'honeypot' => true,
			);
	}

		/**
		 * Render the shortcode output.
		 *
		 * @param array<string, mixed> $atts Shortcode attributes (unused).
		 */
	public static function render( array $atts = array() ): string {
			unset( $atts );

						$state    = self::handle_submission();
						$options  = self::form_options();
						$settings = new RegistrationSettings();
						$auto     = $settings->auto_approve();

						$context = array(
							'success'        => $state['success'],
							'errors'         => $state['errors'],
							'message'        => $state['message'],
							'values'         => $state['values'],
							'nonce_field'    => wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD, false, false ),
							'honeypot_field' => self::HONEYPOT_FIELD,
							'time_field'     => self::TIME_TRAP_FIELD,
							'timestamp'      => time(),
							'action'         => self::current_action_url(),
							'fields'         => array(
								'first_name'     => self::FIELD_FIRST_NAME,
								'last_initial'   => self::FIELD_LAST_INITIAL,
								'email'          => self::FIELD_EMAIL,
								'household_size' => self::FIELD_HOUSEHOLD_SIZE,
								'consent'        => self::FIELD_CONSENT,
								'submit'         => self::SUBMIT_FIELD,
							),
							'labels'         => $options['labels'],
							'copy'           => $options['copy'],
							'settings'       => array(
								'honeypot'     => $options['honeypot'],
								'auto_approve' => $auto,
							),
							'variant'        => $auto ? 'auto' : 'pending',
						);

						return self::render_template( $context );
	}

		/**
		 * Handle registration submission.
		 *
		 * @return array{success:bool,errors:array<int,string>,message:string,values:array<string,string>}
		 */
	private static function handle_submission(): array {
						$values = array(
							'first_name'     => '',
							'last_initial'   => '',
							'email'          => '',
							'household_size' => self::DEFAULT_HOUSEHOLD_SIZE,
							'consent'        => '',
						);

						$result = array(
							'success' => false,
							'errors'  => array(),
							'message' => '',
							'values'  => $values,
						);

						$options         = self::form_options();
						$honeypot_active = $options['honeypot'];
						$copy_messages   = $options['copy'];

						$method = self::read_input( INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
						if ( ! is_string( $method ) || 'POST' !== strtoupper( $method ) ) {
									return $result;
						}

						$submitted = self::read_input( INPUT_POST, self::SUBMIT_FIELD, FILTER_SANITIZE_NUMBER_INT );
						if ( null === $submitted ) {
								return $result;
						}

						$nonce = self::read_input( INPUT_POST, self::NONCE_FIELD, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
						if ( ! is_string( $nonce ) || ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
								$result['errors'][] = esc_html__( 'Security check failed. Please try again.', 'foodbank-manager' );

								return $result;
						}

						if ( $honeypot_active ) {
								$honeypot = self::read_input( INPUT_POST, self::HONEYPOT_FIELD, FILTER_UNSAFE_RAW );
							if ( is_string( $honeypot ) && '' !== trim( $honeypot ) ) {
									$result['errors'][] = esc_html__( 'Invalid submission. Please try again.', 'foodbank-manager' );

									return $result;
							}
						}

						$submitted_at_raw = self::read_input( INPUT_POST, self::TIME_TRAP_FIELD, FILTER_SANITIZE_NUMBER_INT );
						$submitted_at     = is_string( $submitted_at_raw ) ? (int) $submitted_at_raw : 0;

						if ( $submitted_at <= 0 || ( time() - $submitted_at ) < self::MIN_TIME_TRAP_THRESHOLD ) {
								$result['errors'][] = esc_html__( 'Invalid submission. Please try again.', 'foodbank-manager' );

								return $result;
						}

						$first_name = self::read_input( INPUT_POST, self::FIELD_FIRST_NAME, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
						$first_name = is_string( $first_name ) ? sanitize_text_field( $first_name ) : '';

						if ( '' === $first_name ) {
								$result['errors'][] = esc_html__( 'First name is required.', 'foodbank-manager' );
						}

						$last_initial = self::read_input( INPUT_POST, self::FIELD_LAST_INITIAL, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
						$last_initial = is_string( $last_initial ) ? strtoupper( sanitize_text_field( $last_initial ) ) : '';
						$last_initial = substr( $last_initial, 0, 1 );

						if ( '' === $last_initial || 1 !== preg_match( self::LAST_INITIAL_PATTERN, $last_initial ) ) {
								$result['errors'][] = esc_html__( 'Last initial must be a single letter.', 'foodbank-manager' );
						}

						$email = self::read_input( INPUT_POST, self::FIELD_EMAIL, FILTER_SANITIZE_EMAIL );
						$email = is_string( $email ) ? sanitize_email( $email ) : '';

						if ( '' === $email || ! is_email( $email ) ) {
								$result['errors'][] = esc_html__( 'A valid email address is required.', 'foodbank-manager' );
						}

						$household_raw   = self::read_input( INPUT_POST, self::FIELD_HOUSEHOLD_SIZE, FILTER_SANITIZE_NUMBER_INT );
						$household_value = is_string( $household_raw ) && '' !== $household_raw ? (int) $household_raw : (int) self::DEFAULT_HOUSEHOLD_SIZE;
						if ( $household_value < 1 ) {
								$household_value = 1;
						}

						if ( $household_value > 12 ) {
										$household_value = 12;
						}

						$consent_raw = self::read_input( INPUT_POST, self::FIELD_CONSENT, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
						$consent     = is_string( $consent_raw ) ? sanitize_text_field( $consent_raw ) : '';
						$consented   = '1' === $consent;

						$values           = array(
							'first_name'     => $first_name,
							'last_initial'   => $last_initial,
							'email'          => $email,
							'household_size' => (string) $household_value,
							'consent'        => $consented ? '1' : '',
						);
						$result['values'] = $values;

						if ( ! empty( $result['errors'] ) ) {
										$result['message'] = esc_html__( 'Please correct the errors below and try again.', 'foodbank-manager' );

										return $result;
						}

						$throttle_key = null;
						$cooldown     = self::get_submission_cooldown();
						$fingerprint  = self::build_submission_fingerprint( $email );

						if ( $cooldown > 0 && null !== $fingerprint ) {
								$throttle_key    = 'fbm_registration_cooldown_' . md5( $fingerprint );
								$last_submission = get_transient( $throttle_key );

							if ( is_numeric( $last_submission ) ) {
									$last_submission = (int) $last_submission;
							} else {
									$last_submission = null;
							}

							if ( null !== $last_submission && ( time() - $last_submission ) < $cooldown ) {
									$result['errors'][] = esc_html__( 'Please wait before submitting again.', 'foodbank-manager' );
									$result['message']  = esc_html__( 'Please wait before submitting again.', 'foodbank-manager' );

									return $result;
							}

								set_transient( $throttle_key, time(), $cooldown );
						}

						global $wpdb;

						if ( ! $wpdb instanceof wpdb ) {
							$result['errors'][] = esc_html__( 'Service temporarily unavailable. Please try again later.', 'foodbank-manager' );
							$result['message']  = esc_html__( 'We could not save your registration. Please try again later.', 'foodbank-manager' );

							return $result;
						}

						$repository       = new MembersRepository( $wpdb );
						$token_repository = new TokenRepository( $wpdb );
						$token_service    = new TokenService( $token_repository );
						$service          = new RegistrationService( $repository, $token_service );

						$outcome = $service->register(
							$first_name,
							$last_initial,
							$email,
							(int) $household_value,
							$consented ? time() : null
						);

		if ( null === $outcome ) {
				$result['errors'][] = esc_html__( 'We could not save your registration. Please try again later.', 'foodbank-manager' );
				$result['message']  = esc_html__( 'We could not save your registration. Please try again later.', 'foodbank-manager' );

				return $result;
		}

						$status    = (string) ( $outcome['status'] ?? MembersRepository::STATUS_ACTIVE );
						$token_raw = $outcome['token'] ?? null;
						$token     = is_string( $token_raw ) ? $token_raw : '';

		if ( MembersRepository::STATUS_ACTIVE === $status ) {
				$service->ensure_foodbank_member_user( $email, $first_name, $last_initial );

			if ( '' !== $token ) {
								$mailer = is_callable( self::$mailer_factory ) ? call_user_func( self::$mailer_factory ) : new WelcomeMailer();

				if ( ! $mailer->send( $email, $first_name, $outcome['member_reference'], $token ) ) {
					$log = new MailFailureLog();
					$log->record_failure(
						(int) $outcome['member_id'],
						$outcome['member_reference'],
						$email,
						MailFailureLog::CONTEXT_REGISTRATION,
						MailFailureLog::ERROR_MAIL
					);
				}
			}

				$result['message'] = $copy_messages['success_auto'];
		} else {
				$result['message'] = $copy_messages['success_pending'];
		}

						$result['success'] = true;
						$result['values']  = array(
							'first_name'     => '',
							'last_initial'   => '',
							'email'          => '',
							'household_size' => self::DEFAULT_HOUSEHOLD_SIZE,
							'consent'        => '',
						);

						if ( null !== $throttle_key ) {
								delete_transient( $throttle_key );
						}

												return $result;
	}

				/**
				 * Read filtered input values with CLI-safe fallback.
				 *
				 * @param int    $type     Input type constant.
				 * @param string $variable Variable name to resolve.
				 * @param int    $filter   Filter identifier.
				 *
				 * @return mixed Filtered value or null when unavailable.
				 */
	private static function read_input( int $type, string $variable, int $filter ) {
			$value = filter_input( $type, $variable, $filter );

		if ( null !== $value && false !== $value ) {
				return $value;
		}

		switch ( $type ) {
			case INPUT_POST:
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- CLI fallback mirrors filter_input() for tests.
				$source = (array) $_POST;
				break;
			case INPUT_SERVER:
					$source = (array) $_SERVER;
				break;
			default:
					$source = array();
		}

		if ( ! array_key_exists( $variable, $source ) ) {
				return null;
		}

			$raw_value = $source[ $variable ];

		if ( FILTER_UNSAFE_RAW === $filter ) {
				return $raw_value;
		}

			$filtered = filter_var( $raw_value, $filter );

		if ( false === $filtered || null === $filtered ) {
				return null;
		}

			return $filtered;
	}

				/**
				 * Resolve the current page URL for the form action.
				 */
	private static function current_action_url(): string {
									$uri = self::read_input( INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL );

									return is_string( $uri ) ? $uri : '';
	}

				/**
				 * Resolve the submission cooldown duration.
				 */
	private static function get_submission_cooldown(): int {
					$cooldown = (int) apply_filters( self::SUBMISSION_COOLDOWN_FILTER, self::SUBMISSION_COOLDOWN_DEFAULT );

					return $cooldown > 0 ? $cooldown : 0;
	}

				/**
				 * Build a repeatable fingerprint for throttling submissions.
				 *
				 * @param string $email Email address used for the submission.
				 */
	private static function build_submission_fingerprint( string $email ): ?string {
					$email = strtolower( trim( $email ) );

		if ( '' === $email ) {
			return null;
		}

					$remote = self::read_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
					$remote = is_string( $remote ) ? trim( sanitize_text_field( $remote ) ) : '';

		if ( '' === $remote ) {
			return null;
		}

					return $email . '|' . $remote;
	}

				/**
				 * Render the view template safely.
				 *
				 * @param array<string, mixed> $context Template context.
				 */
	private static function render_template( array $context ): string {
			$template = FBM_PATH . 'templates/public/registration-form.php';

		if ( ! is_readable( $template ) ) {
				return '';
		}

			ob_start();

			$data = $context;
			include $template;

			$output = ob_get_clean();

			return is_string( $output ) ? $output : '';
	}
}
