<?php
/**
 * General settings admin page.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FoodBankManager\Crypto\EncryptionSettings;
use FoodBankManager\Registration\RegistrationSettings;
use FoodBankManager\Privacy\Eraser;
use WP_Error;
use function __;
use function add_action;
use function add_query_arg;
use function add_submenu_page;
use function admin_url;
use function check_admin_referer;
use function current_user_can;
use function esc_html__;
use function filter_input;
use function function_exists;
use function get_option;
use function is_array;
use function in_array;
use function is_readable;
use function implode;
use function is_email;
use function sanitize_email;
use function sanitize_key;
use function sanitize_text_field;
use function wp_die;
use function wp_privacy_personal_data_erasers;
use function wp_privacy_process_personal_data_erasure;
use function wp_safe_redirect;
use function wp_unslash;
use function update_option;
use const FILTER_UNSAFE_RAW;
use const INPUT_GET;
use const INPUT_POST;

/**
 * Provides the Food Bank settings page.
 */
final class SettingsPage {
	private const MENU_SLUG             = 'fbm-settings';
	private const PARENT_SLUG           = 'fbm-members';
	private const TEMPLATE              = 'templates/admin/settings-page.php';
	private const FORM_ACTION           = 'fbm_settings_save';
	private const NONCE_NAME            = 'fbm_settings_nonce';
	private const UNINSTALL_FORM_ACTION = 'fbm_settings_uninstall';
	private const UNINSTALL_NONCE_NAME  = 'fbm_uninstall_nonce';
	private const ERASE_FORM_ACTION     = 'fbm_settings_privacy_erase';
	private const ERASE_NONCE_NAME      = 'fbm_privacy_nonce';
	private const STATUS_PARAM          = 'fbm_settings_status';
	private const MESSAGE_PARAM         = 'fbm_settings_message';

		/**
		 * Register WordPress hooks.
		 */
	public static function register(): void {
			add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
			add_action( 'admin_post_' . self::FORM_ACTION, array( __CLASS__, 'handle_save' ) );
			add_action( 'admin_post_' . self::UNINSTALL_FORM_ACTION, array( __CLASS__, 'handle_uninstall_preferences' ) );
			add_action( 'admin_post_' . self::ERASE_FORM_ACTION, array( __CLASS__, 'handle_privacy_erase' ) );
	}

		/**
		 * Register the admin menu entry.
		 */
	public static function register_menu(): void {
			add_submenu_page(
				self::PARENT_SLUG,
				__( 'Food Bank Settings', 'foodbank-manager' ),
				__( 'Settings', 'foodbank-manager' ),
				'fbm_manage', // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability registered during activation.
				self::MENU_SLUG,
				array( __CLASS__, 'render' )
			);
	}

		/**
		 * Render the settings page.
		 */
	public static function render(): void {
		if ( ! current_user_can( 'fbm_manage' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
				wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ) );
		}

			$template = FBM_PATH . self::TEMPLATE;

		if ( ! is_readable( $template ) ) {
				wp_die( esc_html__( 'Settings template is missing.', 'foodbank-manager' ) );
		}

			$stored = get_option( 'fbm_settings', array() );

		if ( ! is_array( $stored ) ) {
				$stored = array();
		}

			$registration_settings = array();

		if ( isset( $stored['registration'] ) && is_array( $stored['registration'] ) ) {
				$registration_settings = $stored['registration'];
		}

						$registration       = RegistrationSettings::normalize_registration_settings( $registration_settings );
						$encrypt_new_writes = EncryptionSettings::encrypt_new_writes_enabled();

			$status_input  = filter_input( INPUT_GET, self::STATUS_PARAM, FILTER_UNSAFE_RAW );
			$message_input = filter_input( INPUT_GET, self::MESSAGE_PARAM, FILTER_UNSAFE_RAW );

			$status  = is_string( $status_input ) ? sanitize_key( $status_input ) : '';
			$message = is_string( $message_input ) ? sanitize_text_field( $message_input ) : '';

			global $wpdb;

			$destructive_enabled  = (bool) get_option( 'fbm_allow_destructive_uninstall', false );
			$destructive_constant = defined( 'FBM_ALLOW_DESTRUCTIVE_UNINSTALL' ) ? (bool) FBM_ALLOW_DESTRUCTIVE_UNINSTALL : false;

			$prefix = 'wp_';

		if ( isset( $wpdb ) && $wpdb instanceof \wpdb ) {
				$prefix = $wpdb->prefix;
		}

			$summary = array(
				'tables'     => array(
					$prefix . 'fbm_members',
					$prefix . 'fbm_tokens',
					$prefix . 'fbm_attendance',
					$prefix . 'fbm_attendance_overrides',
				),
				'options'    => array(
					'fbm_db_version',
					'fbm_settings',
					'fbm_theme',
					'fbm_db_migration_summary',
					'fbm_schedule_window',
					'fbm_schedule_window_overrides',
					'fbm_token_signing_key',
					'fbm_token_storage_key',
					'fbm_mail_failures',
					'fbm_members_action_audit',
				),
				'transients' => array(
					__( 'Transient caches beginning with fbm_', 'foodbank-manager' ),
					__( 'Cache registries stored under fbm_cache_registry:*', 'foodbank-manager' ),
				),
				'events'     => array(
					__( 'Scheduled cron hooks prefixed with fbm_', 'foodbank-manager' ),
				),
			);

						$data = array(
							'settings'     => array(
								'registration' => $registration,
							),
							'encryption'   => array(
								'encrypt_new_writes' => $encrypt_new_writes,
							),
							'form_action'  => self::FORM_ACTION,
							'nonce_action' => self::FORM_ACTION,
							'nonce_name'   => self::NONCE_NAME,
							'status'       => $status,
							'message'      => $message,
							'uninstall'    => array(
								'enabled'      => $destructive_enabled,
								'constant'     => $destructive_constant,
								'summary'      => $summary,
								'form_action'  => self::UNINSTALL_FORM_ACTION,
								'nonce_action' => self::UNINSTALL_FORM_ACTION,
								'nonce_name'   => self::UNINSTALL_NONCE_NAME,
								'eraser'       => array(
									'form_action'  => self::ERASE_FORM_ACTION,
									'nonce_action' => self::ERASE_FORM_ACTION,
									'nonce_name'   => self::ERASE_NONCE_NAME,
								),
							),
						);

						$context = $data;
						include $template;
	}

		/**
		 * Handle saving settings.
		 */
	public static function handle_save(): void {
		if ( ! current_user_can( 'fbm_manage' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
				wp_die( esc_html__( 'You do not have permission to save these settings.', 'foodbank-manager' ) );
		}

			check_admin_referer( self::FORM_ACTION, self::NONCE_NAME );

			$raw = array();

		if ( isset( $_POST['fbm_settings'] ) && is_array( $_POST['fbm_settings'] ) ) {
				$raw = wp_unslash( $_POST['fbm_settings'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized below.
		}

						$sanitized = self::sanitize( $raw );

						$encrypt_input = filter_input( INPUT_POST, 'fbm_encrypt_new_writes', FILTER_UNSAFE_RAW );
						$encrypt_value = is_string( $encrypt_input ) ? sanitize_text_field( wp_unslash( $encrypt_input ) ) : '';
						$encrypt_flag  = in_array( $encrypt_value, array( '1', 'on' ), true );

						EncryptionSettings::update_encrypt_new_writes( $encrypt_flag );

			$status  = 'error';
			$message = esc_html__( 'Settings could not be saved.', 'foodbank-manager' );

		if ( $sanitized instanceof WP_Error ) {
				$error_message = $sanitized->get_error_message();

			if ( '' !== $error_message ) {
					$message = $error_message;
			}
		} else {
				$existing = get_option( 'fbm_settings', array() );

				$merged = RegistrationSettings::merge_registration_settings( $existing, $sanitized['registration'] );

				update_option( 'fbm_settings', $merged );

				$status  = 'success';
				$message = esc_html__( 'Settings saved.', 'foodbank-manager' );
		}

			$redirect = add_query_arg(
				array(
					'page'              => self::MENU_SLUG,
					self::STATUS_PARAM  => $status,
					self::MESSAGE_PARAM => $message,
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
		 * Persist the destructive uninstall preference.
		 */
	public static function handle_uninstall_preferences(): void {
		if ( ! current_user_can( 'fbm_manage' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
				wp_die( esc_html__( 'You do not have permission to modify these settings.', 'foodbank-manager' ) );
		}

			check_admin_referer( self::UNINSTALL_FORM_ACTION, self::UNINSTALL_NONCE_NAME );

		$enabled      = false;
		$raw_checkbox = filter_input( INPUT_POST, 'fbm_allow_destructive_uninstall', FILTER_UNSAFE_RAW );

		if ( is_string( $raw_checkbox ) ) {
			$value   = sanitize_text_field( wp_unslash( $raw_checkbox ) );
			$enabled = ( '1' === $value || 'on' === $value );
		}

			update_option( 'fbm_allow_destructive_uninstall', $enabled, false );

			$message = $enabled
					? esc_html__( 'Destructive uninstall enabled. FoodBank Manager tables and options will be removed when the plugin is uninstalled.', 'foodbank-manager' )
					: esc_html__( 'Destructive uninstall disabled.', 'foodbank-manager' );

			$redirect = add_query_arg(
				array(
					'page'              => self::MENU_SLUG,
					self::STATUS_PARAM  => 'success',
					self::MESSAGE_PARAM => $message,
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
		 * Kick off the FoodBank Manager eraser for the provided identifier.
		 */
	public static function handle_privacy_erase(): void {
		if ( ! current_user_can( 'fbm_manage' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
				wp_die( esc_html__( 'You do not have permission to erase this data.', 'foodbank-manager' ) );
		}

			check_admin_referer( self::ERASE_FORM_ACTION, self::ERASE_NONCE_NAME );

			$status  = 'error';
			$message = esc_html__( 'No FoodBank Manager data was erased.', 'foodbank-manager' );

		$raw_identifier      = filter_input( INPUT_POST, 'fbm_privacy_identifier', FILTER_UNSAFE_RAW );
		$identifier          = is_string( $raw_identifier ) ? sanitize_text_field( wp_unslash( $raw_identifier ) ) : '';
			$email_candidate = sanitize_email( $identifier );

		if ( is_email( $email_candidate ) ) {
				$identifier = $email_candidate;
		}

		if ( '' === $identifier ) {
				$message = esc_html__( 'Please provide an email address or member reference.', 'foodbank-manager' );
		} elseif ( ! function_exists( 'wp_privacy_personal_data_erasers' ) || ! function_exists( 'wp_privacy_process_personal_data_erasure' ) ) {
					$message = esc_html__( 'WordPress privacy tools are unavailable on this site.', 'foodbank-manager' );
		} else {
				$erasers = wp_privacy_personal_data_erasers();

			if ( ! isset( $erasers[ Eraser::ID ] ) || ! is_array( $erasers[ Eraser::ID ] ) ) {
					$message = esc_html__( 'FoodBank Manager eraser is unavailable.', 'foodbank-manager' );
			} else {
					$target         = array( Eraser::ID => $erasers[ Eraser::ID ] );
					$page           = 1;
					$items_removed  = false;
					$items_retained = false;
					$notes          = array();

				do {
							$response = wp_privacy_process_personal_data_erasure( $identifier, $target, $page );

					if ( ! is_array( $response ) ) {
						break;
					}

					if ( ! empty( $response['items_removed'] ) ) {
							$items_removed = true;
					}

					if ( ! empty( $response['items_retained'] ) ) {
							$items_retained = true;
					}

					if ( ! empty( $response['messages'] ) && is_array( $response['messages'] ) ) {
						foreach ( $response['messages'] as $note ) {
									$notes[] = sanitize_text_field( (string) $note );
						}
					}

							++$page;
				} while ( empty( $response['done'] ) && $page <= 10 );

				if ( $items_removed ) {
						$status  = 'success';
						$message = esc_html__( 'FoodBank Manager data erasure completed.', 'foodbank-manager' );
				} elseif ( $items_retained ) {
						$message = esc_html__( 'Some FoodBank Manager data could not be erased.', 'foodbank-manager' );
				} else {
						$message = esc_html__( 'No FoodBank Manager data matched that identifier.', 'foodbank-manager' );
				}

				if ( ! empty( $notes ) ) {
						$message .= ' ' . implode( ' ', $notes );
				}
			}
		}

			$redirect = add_query_arg(
				array(
					'page'              => self::MENU_SLUG,
					self::STATUS_PARAM  => $status,
					self::MESSAGE_PARAM => $message,
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
		 * Sanitize the incoming settings payload.
		 *
		 * @param mixed $raw Raw settings payload.
		 *
		 * @return array{registration:array{auto_approve:bool}}|WP_Error
		 */
	private static function sanitize( $raw ) {
		if ( ! is_array( $raw ) ) {
				return new WP_Error( 'fbm_settings_invalid_payload', __( 'Settings payload must be an array.', 'foodbank-manager' ) );
		}

			$registration_raw = array();

		if ( isset( $raw['registration'] ) ) {
				$registration_raw = $raw['registration'];
		}

			$registration = RegistrationSettings::sanitize_registration_payload( $registration_raw );

			return array(
				'registration' => $registration,
			);
	}
}
