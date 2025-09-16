<?php
/**
 * Registration form shortcode handler.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Shortcodes;

use FoodBankManager\Email\WelcomeMailer;
use FoodBankManager\Registration\MembersRepository;
use FoodBankManager\Registration\RegistrationService;
use FoodBankManager\Token\TokenRepository;
use FoodBankManager\Token\TokenService;
use wpdb;
use function add_shortcode;
use function esc_html__;
use function filter_input;
use function is_email;
use function is_readable;
use function is_string;
use function ob_get_clean;
use function ob_start;
use function preg_match;
use function sanitize_email;
use function sanitize_text_field;
use function substr;
use function strtoupper;
use function time;
use function wp_nonce_field;
use function wp_verify_nonce;

use const FILTER_SANITIZE_EMAIL;
use const FILTER_SANITIZE_FULL_SPECIAL_CHARS;
use const FILTER_SANITIZE_NUMBER_INT;
use const FILTER_UNSAFE_RAW;
use const INPUT_POST;
use const INPUT_SERVER;

/**
 * Renders the registration shortcode.
 */
final class RegistrationForm {

	private const SHORTCODE               = 'fbm_registration_form';
	private const NONCE_ACTION            = 'fbm_registration_submit';
	private const NONCE_FIELD             = 'fbm_registration_nonce';
	private const HONEYPOT_FIELD          = 'fbm_registration_hp';
	private const TIME_TRAP_FIELD         = 'fbm_registration_time';
	private const SUBMIT_FIELD            = 'fbm_registration_submitted';
	private const FIELD_FIRST_NAME        = 'fbm_first_name';
	private const FIELD_LAST_INITIAL      = 'fbm_last_initial';
	private const FIELD_EMAIL             = 'fbm_email';
	private const FIELD_HOUSEHOLD_SIZE    = 'fbm_household_size';
	private const DEFAULT_HOUSEHOLD_SIZE  = '1';
	private const MIN_TIME_TRAP_THRESHOLD = 5;
	private const LAST_INITIAL_PATTERN    = '/^[A-Z]$/';

		/**
		 * Register the shortcode handler.
		 */
	public static function register(): void {
			add_shortcode( self::SHORTCODE, array( self::class, 'render' ) );
	}

		/**
		 * Render the shortcode output.
		 *
		 * @param array<string, mixed> $atts Shortcode attributes (unused).
		 */
	public static function render( array $atts = array() ): string {
			unset( $atts );

			$state = self::handle_submission();

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
					'submit'         => self::SUBMIT_FIELD,
				),
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
			);

			$result = array(
				'success' => false,
				'errors'  => array(),
				'message' => '',
				'values'  => $values,
			);

			$method = filter_input( INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
			if ( ! is_string( $method ) || 'POST' !== strtoupper( $method ) ) {
					return $result;
			}

			$submitted = filter_input( INPUT_POST, self::SUBMIT_FIELD, FILTER_SANITIZE_NUMBER_INT );
			if ( null === $submitted ) {
					return $result;
			}

			$nonce = filter_input( INPUT_POST, self::NONCE_FIELD, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
			if ( ! is_string( $nonce ) || ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
					$result['errors'][] = esc_html__( 'Security check failed. Please try again.', 'foodbank-manager' );

					return $result;
			}

			$honeypot = filter_input( INPUT_POST, self::HONEYPOT_FIELD, FILTER_UNSAFE_RAW );
			if ( is_string( $honeypot ) && '' !== trim( $honeypot ) ) {
					$result['errors'][] = esc_html__( 'Invalid submission. Please try again.', 'foodbank-manager' );

					return $result;
			}

			$submitted_at_raw = filter_input( INPUT_POST, self::TIME_TRAP_FIELD, FILTER_SANITIZE_NUMBER_INT );
			$submitted_at     = is_string( $submitted_at_raw ) ? (int) $submitted_at_raw : 0;

			if ( $submitted_at <= 0 || ( time() - $submitted_at ) < self::MIN_TIME_TRAP_THRESHOLD ) {
					$result['errors'][] = esc_html__( 'Invalid submission. Please try again.', 'foodbank-manager' );

					return $result;
			}

			$first_name = filter_input( INPUT_POST, self::FIELD_FIRST_NAME, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
			$first_name = is_string( $first_name ) ? sanitize_text_field( $first_name ) : '';

			if ( '' === $first_name ) {
					$result['errors'][] = esc_html__( 'First name is required.', 'foodbank-manager' );
			}

			$last_initial = filter_input( INPUT_POST, self::FIELD_LAST_INITIAL, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
			$last_initial = is_string( $last_initial ) ? strtoupper( sanitize_text_field( $last_initial ) ) : '';
			$last_initial = substr( $last_initial, 0, 1 );

			if ( '' === $last_initial || 1 !== preg_match( self::LAST_INITIAL_PATTERN, $last_initial ) ) {
					$result['errors'][] = esc_html__( 'Last initial must be a single letter.', 'foodbank-manager' );
			}

			$email = filter_input( INPUT_POST, self::FIELD_EMAIL, FILTER_SANITIZE_EMAIL );
			$email = is_string( $email ) ? sanitize_email( $email ) : '';

			if ( '' === $email || ! is_email( $email ) ) {
					$result['errors'][] = esc_html__( 'A valid email address is required.', 'foodbank-manager' );
			}

			$household_raw   = filter_input( INPUT_POST, self::FIELD_HOUSEHOLD_SIZE, FILTER_SANITIZE_NUMBER_INT );
			$household_value = is_string( $household_raw ) && '' !== $household_raw ? (int) $household_raw : (int) self::DEFAULT_HOUSEHOLD_SIZE;
			if ( $household_value < 1 ) {
					$household_value = 1;
			}

			if ( $household_value > 12 ) {
					$household_value = 12;
			}

			$values           = array(
				'first_name'     => $first_name,
				'last_initial'   => $last_initial,
				'email'          => $email,
				'household_size' => (string) $household_value,
			);
			$result['values'] = $values;

			if ( ! empty( $result['errors'] ) ) {
					$result['message'] = esc_html__( 'Please correct the errors below and try again.', 'foodbank-manager' );

					return $result;
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

			$outcome = $service->register( $first_name, $last_initial, $email, (int) $household_value );

			if ( null === $outcome ) {
				$result['errors'][] = esc_html__( 'We could not save your registration. Please try again later.', 'foodbank-manager' );
				$result['message']  = esc_html__( 'We could not save your registration. Please try again later.', 'foodbank-manager' );

				return $result;
			}

			$mailer = new WelcomeMailer();
			$mailer->send( $email, $first_name, $outcome['member_reference'], $outcome['token'] );

			$result['success'] = true;
			$result['message'] = esc_html__( 'Thank you for registering. We have emailed your check-in QR code.', 'foodbank-manager' );
			$result['values']  = array(
				'first_name'     => '',
				'last_initial'   => '',
				'email'          => '',
				'household_size' => self::DEFAULT_HOUSEHOLD_SIZE,
			);

			return $result;
	}

		/**
		 * Resolve the current page URL for the form action.
		 */
	private static function current_action_url(): string {
			$uri = filter_input( INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL );

			return is_string( $uri ) ? $uri : '';
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
