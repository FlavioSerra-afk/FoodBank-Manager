<?php
/**
 * Registration form admin settings page.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FoodBankManager\Registration\RegistrationSettings;
use FoodBankManager\Shortcodes\RegistrationForm as RegistrationFormShortcode;
use function __;
use function add_action;
use function add_query_arg;
use function add_submenu_page;
use function admin_url;
use function check_admin_referer;
use function current_user_can;
use function esc_html__;
use function filter_input;
use function get_option;
use function is_array;
use function is_readable;
use function is_string;
use function sanitize_key;
use function sanitize_text_field;
use function update_option;
use function wp_die;
use function wp_safe_redirect;
use function wp_unslash;
use function wp_kses_post;
use const FILTER_UNSAFE_RAW;
use const INPUT_GET;

/**
 * Surface registration form behaviour and labels in the admin UI.
 */
final class RegistrationFormPage {
	private const MENU_SLUG              = 'fbm-registration-form';
	private const TEMPLATE               = 'templates/admin/registration-form.php';
	private const FORM_ACTION            = 'fbm_registration_form_save';
	private const NONCE_NAME             = 'fbm_registration_form_nonce';
	private const STATUS_PARAM           = 'fbm_registration_form_status';
	private const MESSAGE_PARAM          = 'fbm_registration_form_message';
	private const OPTION_HEADLINE        = 'fbm_reg_label_headline';
	private const OPTION_SUBMIT          = 'fbm_reg_label_submit';
	private const OPTION_SUCCESS_AUTO    = 'fbm_reg_copy_success_auto';
	private const OPTION_SUCCESS_PENDING = 'fbm_reg_copy_success_pending';
	private const OPTION_HONEYPOT        = 'fbm_reg_enable_honeypot';

		/**
		 * Attach hooks for the admin page.
		 */
	public static function register(): void {
			add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
			add_action( 'admin_post_' . self::FORM_ACTION, array( __CLASS__, 'handle_save' ) );
	}

		/**
		 * Register the submenu entry.
		 */
	public static function register_menu(): void {
			add_submenu_page(
				Menu::SLUG,
				__( 'Registration Form', 'foodbank-manager' ),
				__( 'Registration Form', 'foodbank-manager' ),
				'fbm_manage', // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability registered on activation.
				self::MENU_SLUG,
				array( __CLASS__, 'render' )
			);
	}

		/**
		 * Render the settings form.
		 */
	public static function render(): void {
		if ( ! current_user_can( 'fbm_manage' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability registered on activation.
				wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ) );
		}

			$template = FBM_PATH . self::TEMPLATE;

		if ( ! is_readable( $template ) ) {
				wp_die( esc_html__( 'Registration form template is missing.', 'foodbank-manager' ) );
		}

			$status_input  = filter_input( INPUT_GET, self::STATUS_PARAM, FILTER_UNSAFE_RAW );
			$message_input = filter_input( INPUT_GET, self::MESSAGE_PARAM, FILTER_UNSAFE_RAW );

			$status  = is_string( $status_input ) ? sanitize_key( $status_input ) : '';
			$message = is_string( $message_input ) ? sanitize_text_field( $message_input ) : '';

			$stored_settings = get_option( 'fbm_settings', array() );
		if ( ! is_array( $stored_settings ) ) {
				$stored_settings = array();
		}

			$registration_settings = array();
		if ( isset( $stored_settings['registration'] ) && is_array( $stored_settings['registration'] ) ) {
				$registration_settings = $stored_settings['registration'];
		}

			$normalized_settings = RegistrationSettings::normalize_registration_settings( $registration_settings );
			$options             = self::current_options();

			$data = array(
				'status'       => $status,
				'message'      => $message,
				'form_action'  => self::FORM_ACTION,
				'nonce_action' => self::FORM_ACTION,
				'nonce_name'   => self::NONCE_NAME,
				'menu_slug'    => self::MENU_SLUG,
				'settings'     => array(
					'auto_approve' => (bool) $normalized_settings['auto_approve'],
					'honeypot'     => $options['honeypot'],
				),
				'labels'       => array(
					'headline' => $options['headline'],
					'submit'   => $options['submit'],
				),
				'copy'         => array(
					'success_auto'    => $options['success_auto'],
					'success_pending' => $options['success_pending'],
				),
			);

			$context = $data;
			include $template;
	}

		/**
		 * Handle saving registration form preferences.
		 */
	public static function handle_save(): void {
		if ( ! current_user_can( 'fbm_manage' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability registered on activation.
				wp_die( esc_html__( 'You do not have permission to modify the registration form.', 'foodbank-manager' ) );
		}

			check_admin_referer( self::FORM_ACTION, self::NONCE_NAME );

			$payload = array();
		if ( isset( $_POST['fbm_registration_form'] ) && is_array( $_POST['fbm_registration_form'] ) ) {
				$payload = wp_unslash( $_POST['fbm_registration_form'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized below.
		}

			$sanitized = self::sanitize_payload( $payload );
			self::store_settings( $sanitized );

			$redirect = add_query_arg(
				array(
					'page'              => self::MENU_SLUG,
					self::STATUS_PARAM  => 'success',
					self::MESSAGE_PARAM => sanitize_text_field( __( 'Registration form settings saved.', 'foodbank-manager' ) ),
				),
				admin_url( 'admin.php' )
			);

			wp_safe_redirect( $redirect );

		if ( defined( 'FBM_TESTING' ) && FBM_TESTING ) {
				return;
		}

			exit;
	}

		/**
		 * Retrieve the stored registration form options.
		 *
		 * @return array{headline:string,submit:string,success_auto:string,success_pending:string,honeypot:bool}
		 */
	private static function current_options(): array {
			$defaults = self::defaults();

			$headline = get_option( self::OPTION_HEADLINE, $defaults['headline'] );
			$submit   = get_option( self::OPTION_SUBMIT, $defaults['submit'] );
			$auto     = get_option( self::OPTION_SUCCESS_AUTO, $defaults['success_auto'] );
			$pending  = get_option( self::OPTION_SUCCESS_PENDING, $defaults['success_pending'] );
			$honeypot = get_option( self::OPTION_HONEYPOT, $defaults['honeypot'] ? 1 : 0 );

			return array(
				'headline'        => is_string( $headline ) ? $headline : $defaults['headline'],
				'submit'          => is_string( $submit ) ? $submit : $defaults['submit'],
				'success_auto'    => is_string( $auto ) ? $auto : $defaults['success_auto'],
				'success_pending' => is_string( $pending ) ? $pending : $defaults['success_pending'],
				'honeypot'        => (bool) $honeypot,
			);
	}

		/**
		 * Default registration form strings.
		 *
		 * @return array{headline:string,submit:string,success_auto:string,success_pending:string,honeypot:bool}
		 */
	private static function defaults(): array {
			$defaults = RegistrationFormShortcode::default_options();

			return array(
				'headline'        => $defaults['labels']['headline'],
				'submit'          => $defaults['labels']['submit'],
				'success_auto'    => $defaults['copy']['success_auto'],
				'success_pending' => $defaults['copy']['success_pending'],
				'honeypot'        => (bool) $defaults['honeypot'],
			);
	}

		/**
		 * Sanitize incoming form payload.
		 *
		 * @param array<string,mixed> $payload Raw POST payload.
		 *
		 * @return array{auto_approve:bool,honeypot:bool,headline:string,submit:string,success_auto:string,success_pending:string}
		 */
	private static function sanitize_payload( array $payload ): array {
			$defaults = self::defaults();

			$auto      = isset( $payload['auto_approve'] ) ? self::to_bool( $payload['auto_approve'] ) : false;
			$honeypot  = isset( $payload['honeypot'] ) ? self::to_bool( $payload['honeypot'] ) : (bool) $defaults['honeypot'];
			$headline  = isset( $payload['headline'] ) ? sanitize_text_field( (string) $payload['headline'] ) : $defaults['headline'];
			$submit    = isset( $payload['submit'] ) ? sanitize_text_field( (string) $payload['submit'] ) : $defaults['submit'];
			$auto_copy = isset( $payload['success_auto'] ) ? wp_kses_post( (string) $payload['success_auto'] ) : $defaults['success_auto'];
			$pending   = isset( $payload['success_pending'] ) ? wp_kses_post( (string) $payload['success_pending'] ) : $defaults['success_pending'];

			return array(
				'auto_approve'    => $auto,
				'honeypot'        => $honeypot,
				'headline'        => '' !== $headline ? $headline : $defaults['headline'],
				'submit'          => '' !== $submit ? $submit : $defaults['submit'],
				'success_auto'    => '' !== $auto_copy ? $auto_copy : $defaults['success_auto'],
				'success_pending' => '' !== $pending ? $pending : $defaults['success_pending'],
			);
	}

		/**
		 * Persist sanitized settings and options.
		 *
		 * @param array{auto_approve:bool,honeypot:bool,headline:string,submit:string,success_auto:string,success_pending:string} $sanitized Sanitized values.
		 */
	private static function store_settings( array $sanitized ): void {
			$registration_payload = RegistrationSettings::sanitize_registration_payload(
				array( 'auto_approve' => $sanitized['auto_approve'] )
			);

			$existing = get_option( 'fbm_settings', array() );
			$merged   = RegistrationSettings::merge_registration_settings( $existing, $registration_payload );
			update_option( 'fbm_settings', $merged );

			update_option( self::OPTION_HEADLINE, $sanitized['headline'] );
			update_option( self::OPTION_SUBMIT, $sanitized['submit'] );
			update_option( self::OPTION_SUCCESS_AUTO, $sanitized['success_auto'] );
			update_option( self::OPTION_SUCCESS_PENDING, $sanitized['success_pending'] );
			update_option( self::OPTION_HONEYPOT, $sanitized['honeypot'] ? 1 : 0 );
	}

		/**
		 * Normalize checkbox-style payloads to booleans.
		 *
		 * @param mixed $value Raw submitted value.
		 */
	private static function to_bool( $value ): bool {
		if ( is_bool( $value ) ) {
				return $value;
		}

		if ( is_numeric( $value ) ) {
				return (bool) (int) $value;
		}

		if ( is_string( $value ) ) {
				$value = strtolower( trim( $value ) );

				return in_array( $value, array( '1', 'true', 'yes', 'on' ), true );
		}

			return false;
	}
}
