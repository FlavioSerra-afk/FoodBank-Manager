<?php
/**
 * General settings admin page.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FoodBankManager\Registration\RegistrationSettings;
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
use function get_option;
use function is_array;
use function is_readable;
use function sanitize_key;
use function sanitize_text_field;
use function wp_die;
use function wp_safe_redirect;
use function wp_unslash;
use function update_option;
use const FILTER_UNSAFE_RAW;
use const INPUT_GET;

/**
 * Provides the Food Bank settings page.
 */
final class SettingsPage {
        private const MENU_SLUG    = 'fbm-settings';
        private const PARENT_SLUG  = 'fbm-members';
        private const TEMPLATE     = 'templates/admin/settings-page.php';
        private const FORM_ACTION  = 'fbm_settings_save';
        private const NONCE_NAME   = 'fbm_settings_nonce';
        private const STATUS_PARAM = 'fbm_settings_status';
        private const MESSAGE_PARAM = 'fbm_settings_message';

        /**
         * Register WordPress hooks.
         */
        public static function register(): void {
                add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
                add_action( 'admin_post_' . self::FORM_ACTION, array( __CLASS__, 'handle_save' ) );
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

                $registration = RegistrationSettings::normalize_registration_settings( $registration_settings );

                $status_input  = filter_input( INPUT_GET, self::STATUS_PARAM, FILTER_UNSAFE_RAW );
                $message_input = filter_input( INPUT_GET, self::MESSAGE_PARAM, FILTER_UNSAFE_RAW );

                $status  = is_string( $status_input ) ? sanitize_key( $status_input ) : '';
                $message = is_string( $message_input ) ? sanitize_text_field( $message_input ) : '';

                $data = array(
                        'settings'     => array(
                                'registration' => $registration,
                        ),
                        'form_action'  => self::FORM_ACTION,
                        'nonce_action' => self::FORM_ACTION,
                        'nonce_name'   => self::NONCE_NAME,
                        'status'       => $status,
                        'message'      => $message,
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
                                'page'                     => self::MENU_SLUG,
                                self::STATUS_PARAM        => $status,
                                self::MESSAGE_PARAM       => $message,
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
