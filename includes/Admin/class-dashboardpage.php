<?php
/**
 * Staff dashboard admin settings page.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FoodBankManager\Shortcodes\StaffDashboard;
use function __;
use function add_action;
use function add_query_arg;
use function add_submenu_page;
use function admin_url;
use function check_admin_referer;
use function current_user_can;
use function esc_html__;
use function filter_input;
use function is_array;
use function is_readable;
use function is_string;
use function sanitize_key;
use function sanitize_text_field;
use function update_option;
use function wp_die;
use function wp_safe_redirect;
use function wp_unslash;
use const FILTER_UNSAFE_RAW;
use const INPUT_GET;

/**
 * Configure the staff dashboard shortcode defaults.
 */
final class DashboardPage {
	private const MENU_SLUG     = 'fbm-staff-dashboard-settings';
	private const TEMPLATE      = 'templates/admin/dashboard-settings.php';
	private const FORM_ACTION   = 'fbm_staff_dashboard_save';
	private const NONCE_NAME    = 'fbm_staff_dashboard_nonce';
	private const STATUS_PARAM  = 'fbm_staff_dashboard_status';
	private const MESSAGE_PARAM = 'fbm_staff_dashboard_message';
	private const OPTION_NAME   = 'fbm_staff_dashboard_settings';

		/**
		 * Register WordPress hooks for the settings page.
		 */
	public static function register(): void {
			add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
			add_action( 'admin_post_' . self::FORM_ACTION, array( __CLASS__, 'handle_save' ) );
	}

		/**
		 * Register the submenu entry beneath Food Bank.
		 */
	public static function register_menu(): void {
			add_submenu_page(
				Menu::SLUG,
				__( 'Staff Dashboard', 'foodbank-manager' ),
				__( 'Staff Dashboard', 'foodbank-manager' ),
				'fbm_manage', // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability registered on activation.
				self::MENU_SLUG,
				array( __CLASS__, 'render' )
			);
	}

		/**
		 * Render the settings interface.
		 */
	public static function render(): void {
		if ( ! current_user_can( 'fbm_manage' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability registered on activation.
				wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ) );
		}

			$template = FBM_PATH . self::TEMPLATE;
		if ( ! is_readable( $template ) ) {
				wp_die( esc_html__( 'Dashboard settings template is missing.', 'foodbank-manager' ) );
		}

			$status_input  = filter_input( INPUT_GET, self::STATUS_PARAM, FILTER_UNSAFE_RAW );
			$message_input = filter_input( INPUT_GET, self::MESSAGE_PARAM, FILTER_UNSAFE_RAW );

			$status  = is_string( $status_input ) ? sanitize_key( $status_input ) : '';
			$message = is_string( $message_input ) ? sanitize_text_field( $message_input ) : '';

			$settings = StaffDashboard::settings();

			$data = array(
				'status'       => $status,
				'message'      => $message,
				'form_action'  => self::FORM_ACTION,
				'nonce_action' => self::FORM_ACTION,
				'nonce_name'   => self::NONCE_NAME,
				'menu_slug'    => self::MENU_SLUG,
				'settings'     => $settings,
			);

			$context = $data;
			include $template;
	}

		/**
		 * Persist dashboard preferences.
		 */
	public static function handle_save(): void {
		if ( ! current_user_can( 'fbm_manage' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability registered on activation.
				wp_die( esc_html__( 'You do not have permission to modify the staff dashboard.', 'foodbank-manager' ) );
		}

			check_admin_referer( self::FORM_ACTION, self::NONCE_NAME );

			$payload = array();
		if ( isset( $_POST['fbm_staff_dashboard'] ) && is_array( $_POST['fbm_staff_dashboard'] ) ) {
				$payload = wp_unslash( $_POST['fbm_staff_dashboard'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized in sanitize_payload().
		}

			$sanitized = self::sanitize_payload( $payload );
			update_option( self::OPTION_NAME, $sanitized );

			$redirect = add_query_arg(
				array(
					'page'              => self::MENU_SLUG,
					self::STATUS_PARAM  => 'success',
					self::MESSAGE_PARAM => sanitize_text_field( __( 'Staff dashboard settings saved.', 'foodbank-manager' ) ),
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
		 * Sanitize raw POST data.
		 *
		 * @param array<string,mixed> $payload Raw payload from the settings form.
		 *
		 * @return array{show_counters:bool,allow_override:bool,scanner:array{prefer_torch:bool,roi:int,decode_debounce:int}}
		 */
	private static function sanitize_payload( array $payload ): array {
			$defaults = self::defaults();

			$show_counters  = isset( $payload['show_counters'] ) ? self::to_bool( $payload['show_counters'] ) : $defaults['show_counters'];
			$allow_override = isset( $payload['allow_override'] ) ? self::to_bool( $payload['allow_override'] ) : $defaults['allow_override'];

			$scanner = array();
		if ( isset( $payload['scanner'] ) && is_array( $payload['scanner'] ) ) {
				$scanner = $payload['scanner'];
		}

			$prefer_torch = isset( $scanner['prefer_torch'] ) ? self::to_bool( $scanner['prefer_torch'] ) : $defaults['scanner']['prefer_torch'];
			$roi          = isset( $scanner['roi'] ) ? (int) $scanner['roi'] : $defaults['scanner']['roi'];
			$debounce     = isset( $scanner['decode_debounce'] ) ? (int) $scanner['decode_debounce'] : $defaults['scanner']['decode_debounce'];

			$roi      = max( 30, min( 100, $roi ) );
			$debounce = max( 0, min( 5000, $debounce ) );

			return array(
				'show_counters'  => $show_counters,
				'allow_override' => $allow_override,
				'scanner'        => array(
					'prefer_torch'    => $prefer_torch,
					'roi'             => $roi,
					'decode_debounce' => $debounce,
				),
			);
	}

		/**
		 * Default settings for the staff dashboard.
		 *
		 * @return array{show_counters:bool,allow_override:bool,scanner:array{prefer_torch:bool,roi:int,decode_debounce:int}}
		 */
	public static function defaults(): array {
			return StaffDashboard::default_settings();
	}

		/**
		 * Normalize checkbox-style values.
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
