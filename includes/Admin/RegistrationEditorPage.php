<?php
/**
 * Registration form editor admin page.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FoodBankManager\Core\Plugin;
use FoodBankManager\Registration\Editor\TemplateDefaults;
use FoodBankManager\Registration\Editor\TemplateRenderer;
use FoodBankManager\Registration\Editor\TagParser;
use FoodBankManager\Registration\Uploads;
use function __;
use function add_action;
use function add_query_arg;
use function add_settings_error;
use function add_submenu_page;
use function admin_url;
use function check_admin_referer;
use function current_user_can;
use function esc_html__;
use function esc_url;
use function explode;
use function get_current_screen;
use function get_option;
use function in_array;
use function is_array;
use function is_readable;
use function is_string;
use function json_decode;
use function nocache_headers;
use function plugins_url;
use function rest_url;
use function sanitize_key;
use function sanitize_text_field;
use function sanitize_textarea_field;
use function settings_errors;
use function settings_fields;
use function submit_button;
use function update_option;
use function wp_create_nonce;
use function wp_die;
use function wp_enqueue_code_editor;
use function wp_enqueue_script;
use function wp_enqueue_style;
use function wp_json_encode;
use function wp_kses_post;
use function wp_localize_script;
use function wp_nonce_field;
use function wp_parse_args;
use function wp_register_script;
use function wp_safe_redirect;
use function wp_unslash;
use function wp_verify_nonce;
use const FILTER_SANITIZE_FULL_SPECIAL_CHARS;
use const FILTER_UNSAFE_RAW;
use const INPUT_GET;
use const INPUT_POST;

/**
 * Admin page for editing the registration form template.
 */
final class RegistrationEditorPage {
        private const MENU_SLUG       = 'fbm-registration-form';
        private const OPTION_GROUP    = 'fbm_registration_editor';
        private const TEMPLATE_OPTION = 'fbm_registration_template';
        private const SETTINGS_OPTION = 'fbm_registration_settings';
        private const TEMPLATE_FIELD  = 'fbm_registration_template_field';
        private const SETTINGS_FIELD  = 'fbm_registration_settings_field';
        private const TEMPLATE_FILE   = 'templates/admin/registration-editor.php';
        private const RESET_ACTION    = 'fbm_registration_editor_reset';
        private const EXPORT_ACTION   = 'fbm_registration_editor_export';
        private const IMPORT_ACTION   = 'fbm_registration_editor_import';
        private const NOTICE_PARAM    = 'fbm_registration_editor_notice';
        private const MESSAGE_PARAM   = 'fbm_registration_editor_message';
        private const PREVIEW_NONCE   = 'fbm_registration_preview';

        /**
         * Register WordPress hooks.
         */
        public static function register(): void {
                add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
                add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
                add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
                add_action( 'admin_post_' . self::RESET_ACTION, array( __CLASS__, 'handle_reset' ) );
                add_action( 'admin_post_' . self::EXPORT_ACTION, array( __CLASS__, 'handle_export' ) );
                add_action( 'admin_post_' . self::IMPORT_ACTION, array( __CLASS__, 'handle_import' ) );
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
         * Register settings and fields for the editor.
         */
        public static function register_settings(): void {
                register_setting(
                        self::OPTION_GROUP,
                        self::TEMPLATE_OPTION,
                        array(
                                'type'              => 'string',
                                'sanitize_callback' => array( __CLASS__, 'sanitize_template' ),
                                'default'           => TemplateDefaults::template(),
                        )
                );

                register_setting(
                        self::OPTION_GROUP,
                        self::SETTINGS_OPTION,
                        array(
                                'type'              => 'array',
                                'sanitize_callback' => array( __CLASS__, 'sanitize_settings' ),
                                'default'           => TemplateDefaults::settings(),
                        )
                );
        }

        /**
         * Render the registration editor screen.
         */
        public static function render(): void {
                if ( ! current_user_can( 'fbm_manage' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability registered on activation.
                        wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ) );
                }

                $template_file = FBM_PATH . self::TEMPLATE_FILE;
                if ( ! is_readable( $template_file ) ) {
                        wp_die( esc_html__( 'Registration editor template is missing.', 'foodbank-manager' ) );
                }

                $template = get_option( self::TEMPLATE_OPTION, TemplateDefaults::template() );
                if ( ! is_string( $template ) ) {
                        $template = TemplateDefaults::template();
                }

                $settings = get_option( self::SETTINGS_OPTION, TemplateDefaults::settings() );
                if ( ! is_array( $settings ) ) {
                        $settings = TemplateDefaults::settings();
                }

                $query_notice = filter_input( INPUT_GET, self::NOTICE_PARAM, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                $query_message = filter_input( INPUT_GET, self::MESSAGE_PARAM, FILTER_UNSAFE_RAW );

                $notice  = is_string( $query_notice ) ? sanitize_key( $query_notice ) : '';
                $message = is_string( $query_message ) ? sanitize_text_field( $query_message ) : '';

                $data = array(
                        'option_group'    => self::OPTION_GROUP,
                        'template_option' => self::TEMPLATE_OPTION,
                        'settings_option' => self::SETTINGS_OPTION,
                        'template'        => $template,
                        'settings'        => wp_parse_args( $settings, TemplateDefaults::settings() ),
                        'menu_slug'       => self::MENU_SLUG,
                        'reset_action'    => self::RESET_ACTION,
                        'export_action'   => self::EXPORT_ACTION,
                        'import_action'   => self::IMPORT_ACTION,
                        'reset_nonce'     => wp_create_nonce( self::RESET_ACTION ),
                        'import_nonce'    => wp_create_nonce( self::IMPORT_ACTION ),
                        'export_nonce'    => wp_create_nonce( self::EXPORT_ACTION ),
                        'preview_nonce'   => wp_create_nonce( self::PREVIEW_NONCE ),
                        'notice'          => $notice,
                        'message'         => $message,
                        'template_field'  => self::TEMPLATE_FIELD,
                        'settings_field'  => self::SETTINGS_FIELD,
                        'snippets'        => self::toolbar_snippets(),
                );

                settings_errors( self::OPTION_GROUP );

                $context = $data;
                include $template_file;
        }

        /**
         * Enqueue assets for the editor screen.
         *
         * @param string $hook_suffix Current admin page hook.
         */
        public static function enqueue_assets( string $hook_suffix ): void {
                unset( $hook_suffix );

                if ( ! function_exists( 'get_current_screen' ) ) {
                        return;
                }

                $screen = get_current_screen();
                if ( ! $screen || self::screen_id() !== $screen->id ) {
                        return;
                }

                $editor_settings = wp_enqueue_code_editor( array( 'type' => 'text/html' ) );

                if ( ! empty( $editor_settings ) ) {
                        wp_enqueue_script( 'code-editor' );
                        wp_enqueue_style( 'code-editor' );
                } else {
                        $editor_settings = array();
                }

                $stored_settings = get_option( self::SETTINGS_OPTION, TemplateDefaults::settings() );
                $theme           = 'light';
                if ( is_array( $stored_settings ) && isset( $stored_settings['editor']['theme'] ) && is_string( $stored_settings['editor']['theme'] ) ) {
                        $candidate = sanitize_key( $stored_settings['editor']['theme'] );
                        if ( in_array( $candidate, array( 'light', 'dark' ), true ) ) {
                                $theme = $candidate;
                        }
                }

                $version = defined( 'FBM_VER' ) ? FBM_VER : Plugin::VERSION;
                wp_enqueue_script( 'jquery' );

		$handle = 'fbm-registration-editor';
		$script = plugins_url( 'assets/js/registration-editor.js', FBM_FILE );
		$dependencies = array( 'jquery' );
		if ( ! empty( $editor_settings ) ) {
			$dependencies[] = 'code-editor';
		}
		wp_register_script( $handle, $script, $dependencies, $version, true );
                wp_localize_script(
                        $handle,
                        'fbmRegistrationEditor',
                        array(
                                'previewNonce' => wp_create_nonce( 'wp_rest' ),
                                'previewUrl'   => esc_url( rest_url( 'fbm/v1/registration/preview' ) ),
                                'textareaId'   => self::TEMPLATE_FIELD,
                                'codeEditor'   => $editor_settings,
                                'editorTheme'  => $theme,
                                'i18n'         => array(
                                        'previewTitle' => esc_html__( 'Template Preview', 'foodbank-manager' ),
                                        'previewError' => esc_html__( 'Unable to load the preview. Please save first or try again.', 'foodbank-manager' ),
                                        'closeLabel'   => esc_html__( 'Close preview', 'foodbank-manager' ),
                                        'modalDescription' => esc_html__( 'Preview only. Form controls are disabled.', 'foodbank-manager' ),
                                ),
                        )
                );
                wp_enqueue_script( $handle );
        }

        /**
         * Sanitize the template payload.
         *
         * @param mixed $value Raw template value.
         */
        public static function sanitize_template( $value ): string {
                $template = is_string( $value ) ? $value : '';
                $template = TemplateRenderer::sanitize_template( $template );

                $parser   = new TagParser();
                $parsed   = $parser->parse( $template );
                $fields   = $parsed['fields'];
                $warnings = $parsed['warnings'];

                foreach ( $warnings as $warning ) {
                        if ( '' === $warning ) {
                                continue;
                        }
                        add_settings_error( self::OPTION_GROUP, 'fbm_registration_template_warning', sanitize_text_field( $warning ), 'notice-warning' );
                }

                self::validate_canonical_fields( $fields );

                return $template;
        }

        /**
         * Sanitize registration settings payload.
         *
         * @param mixed $value Raw settings array.
         *
         * @return array<string,mixed>
         */
        public static function sanitize_settings( $value ): array {
                $defaults = TemplateDefaults::settings();

                if ( ! is_array( $value ) ) {
                        $value = array();
                }

                $uploads    = isset( $value['uploads'] ) && is_array( $value['uploads'] ) ? $value['uploads'] : array();
                $max_size   = isset( $uploads['max_size_mb'] ) ? (int) $uploads['max_size_mb'] : (int) ( $defaults['uploads']['max_size'] / 1048576 );
                $max_size   = $max_size > 0 ? $max_size : (int) ( $defaults['uploads']['max_size'] / 1048576 );
                $mime_raw   = isset( $uploads['allowed_mime_types'] ) ? sanitize_textarea_field( (string) $uploads['allowed_mime_types'] ) : '';
                $mime_parts = array();
                if ( '' !== $mime_raw ) {
                        $candidates = array_map( 'trim', explode( ',', $mime_raw ) );
                        foreach ( $candidates as $candidate ) {
                                if ( '' !== $candidate ) {
                                        $mime_parts[] = strtolower( $candidate );
                                }
                        }
                }

                $uploads_settings = array(
                        'max_size'           => max( 1, $max_size ) * 1048576,
                        'allowed_mime_types' => ! empty( $mime_parts ) ? $mime_parts : $defaults['uploads']['allowed_mime_types'],
                );

                $conditions_enabled = isset( $value['conditions']['enabled'] ) ? self::to_bool( $value['conditions']['enabled'] ) : (bool) $defaults['conditions']['enabled'];

                $editor_theme = isset( $value['editor']['theme'] ) && is_string( $value['editor']['theme'] ) ? sanitize_key( $value['editor']['theme'] ) : 'light';
                if ( ! in_array( $editor_theme, array( 'light', 'dark' ), true ) ) {
                        $editor_theme = 'light';
                }

                $messages = isset( $value['messages'] ) && is_array( $value['messages'] ) ? $value['messages'] : array();
                $messages = array_merge( $defaults['messages'], array(
                        'success_auto'    => isset( $messages['success_auto'] ) ? wp_kses_post( (string) $messages['success_auto'] ) : $defaults['messages']['success_auto'],
                        'success_pending' => isset( $messages['success_pending'] ) ? wp_kses_post( (string) $messages['success_pending'] ) : $defaults['messages']['success_pending'],
                ) );

                return array(
                        'uploads'    => Uploads::normalize_settings( $uploads_settings ),
                        'conditions' => array(
                                'enabled' => $conditions_enabled,
                                'rules'   => array(),
                        ),
                        'editor'     => array(
                                'theme' => $editor_theme,
                        ),
                        'honeypot'   => isset( $value['honeypot'] ) ? self::to_bool( $value['honeypot'] ) : (bool) $defaults['honeypot'],
                        'messages'   => $messages,
                );
        }

        /**
         * Handle reset to default action.
         */
        public static function handle_reset(): void {
                if ( ! current_user_can( 'fbm_manage' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability registered on activation.
                        wp_die( esc_html__( 'You do not have permission to modify the registration form.', 'foodbank-manager' ) );
                }

                check_admin_referer( self::RESET_ACTION );

                update_option( self::TEMPLATE_OPTION, TemplateDefaults::template() );
                update_option( self::SETTINGS_OPTION, TemplateDefaults::settings() );

                $redirect = add_query_arg(
                        array(
                                'page'                          => self::MENU_SLUG,
                                self::NOTICE_PARAM              => 'success',
                                self::MESSAGE_PARAM             => sanitize_text_field( __( 'Registration form reset to defaults.', 'foodbank-manager' ) ),
                        ),
                        admin_url( 'admin.php' )
                );

                wp_safe_redirect( $redirect );
                exit;
        }

        /**
         * Handle export action.
         */
        public static function handle_export(): void {
                if ( ! current_user_can( 'fbm_manage' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability registered on activation.
                        wp_die( esc_html__( 'You do not have permission to export the registration form.', 'foodbank-manager' ) );
                }

                check_admin_referer( self::EXPORT_ACTION );

                $payload = array(
                        'template' => get_option( self::TEMPLATE_OPTION, TemplateDefaults::template() ),
                        'settings' => get_option( self::SETTINGS_OPTION, TemplateDefaults::settings() ),
                );

                $json = wp_json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
                if ( ! is_string( $json ) ) {
                        wp_die( esc_html__( 'Unable to export registration form.', 'foodbank-manager' ) );
                }

                nocache_headers();
                header( 'Content-Type: application/json; charset=utf-8' );
                header( 'Content-Disposition: attachment; filename="fbm-registration-template.json"' );
                echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON output.
                exit;
        }

        /**
         * Handle import action.
         */
        public static function handle_import(): void {
                if ( ! current_user_can( 'fbm_manage' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability registered on activation.
                        wp_die( esc_html__( 'You do not have permission to import the registration form.', 'foodbank-manager' ) );
                }

                check_admin_referer( self::IMPORT_ACTION );

                $payload = array();
                if ( isset( $_POST['fbm_registration_import'] ) ) {
                        $raw = wp_unslash( $_POST['fbm_registration_import'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized below.
                        $payload = json_decode( sanitize_textarea_field( (string) $raw ), true );
                }

                if ( ! is_array( $payload ) || empty( $payload['template'] ) || empty( $payload['settings'] ) ) {
                        $redirect = add_query_arg(
                                array(
                                        'page'             => self::MENU_SLUG,
                                        self::NOTICE_PARAM => 'error',
                                        self::MESSAGE_PARAM => sanitize_text_field( __( 'Import failed. Ensure the JSON file is valid.', 'foodbank-manager' ) ),
                                ),
                                admin_url( 'admin.php' )
                        );
                        wp_safe_redirect( $redirect );
                        exit;
                }

                update_option( self::TEMPLATE_OPTION, TemplateRenderer::sanitize_template( (string) $payload['template'] ) );
                update_option( self::SETTINGS_OPTION, self::sanitize_settings( $payload['settings'] ) );

                $redirect = add_query_arg(
                        array(
                                'page'             => self::MENU_SLUG,
                                self::NOTICE_PARAM => 'success',
                                self::MESSAGE_PARAM => sanitize_text_field( __( 'Registration form imported.', 'foodbank-manager' ) ),
                        ),
                        admin_url( 'admin.php' )
                );

                wp_safe_redirect( $redirect );
                exit;
        }

        /**
         * Convert checkbox-style values to booleans.
         *
         * @param mixed $value Raw value.
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

        /**
         * Validate presence of canonical fields and add admin warnings.
         *
         * @param array<string,array<string,mixed>> $fields Field map from parser.
         */
        private static function validate_canonical_fields( array $fields ): void {
                $required = array( 'first_name', 'last_initial', 'email', 'household_size' );
                $present  = array();

                foreach ( $fields as $definition ) {
                        if ( ! is_array( $definition ) ) {
                                continue;
                        }

                        $name = isset( $definition['name'] ) ? (string) $definition['name'] : '';
                        $key  = self::canonical_key( $name );
                        if ( null !== $key ) {
                                $present[] = $key;
                        }
                }

                foreach ( $required as $key ) {
                        if ( ! in_array( $key, $present, true ) ) {
                                $label = ucfirst( str_replace( '_', ' ', $key ) );
                                add_settings_error(
                                        self::OPTION_GROUP,
                                        'fbm_registration_template_missing_' . $key,
                                        sprintf(
                                                /* translators: %s: Missing canonical field label. */
                                                __( 'Template is missing the required %s field.', 'foodbank-manager' ),
                                                $label
                                        ),
                                        'notice-warning'
                                );
                        }
                }
        }

        /**
         * Map field names to canonical keys.
         *
         * @param string $name Field name.
         */
        private static function canonical_key( string $name ): ?string {
                $normalized = strtolower( preg_replace( '/[^a-z0-9]+/', '_', $name ) ?? '' );

                switch ( $normalized ) {
                        case 'fbm_first_name':
                        case 'first_name':
                                return 'first_name';
                        case 'fbm_last_initial':
                        case 'last_initial':
                                return 'last_initial';
                        case 'fbm_email':
                        case 'email_address':
                        case 'email':
                                return 'email';
                        case 'fbm_household_size':
                        case 'household_size':
                        case 'household':
                                return 'household_size';
                        default:
                                return null;
                }
        }

        /**
         * Toolbar snippets for quick insertion.
         *
         * @return array<int,array<string,string>>
         */
        private static function toolbar_snippets(): array {
                return array(
                        array(
                                'label'   => __( 'Text field', 'foodbank-manager' ),
                                'snippet' => '[text* fbm_first_name placeholder "Enter your first name" autocomplete "given-name"]',
                        ),
                        array(
                                'label'   => __( 'Email field', 'foodbank-manager' ),
                                'snippet' => '[email* fbm_email placeholder "name@example.com" autocomplete "email"]',
                        ),
                        array(
                                'label'   => __( 'Telephone field', 'foodbank-manager' ),
                                'snippet' => '[tel fbm_phone placeholder "+44 7123 456789" autocomplete "tel"]',
                        ),
                        array(
                                'label'   => __( 'Date field', 'foodbank-manager' ),
                                'snippet' => '[date fbm_preferred_date min:2024-01-01 max:2030-12-31]',
                        ),
                        array(
                                'label'   => __( 'Number field', 'foodbank-manager' ),
                                'snippet' => '[number* fbm_household_size min:1 max:12 step:1]',
                        ),
                        array(
                                'label'   => __( 'Textarea', 'foodbank-manager' ),
                                'snippet' => '[textarea fbm_additional_notes placeholder "Share any additional information"]',
                        ),
                        array(
                                'label'   => __( 'Radio options', 'foodbank-manager' ),
                                'snippet' => '[radio* fbm_contact_method "Email" "Phone" "SMS"]',
                        ),
                        array(
                                'label'   => __( 'Checkbox group', 'foodbank-manager' ),
                                'snippet' => '[checkbox fbm_support_needs use_label_element "Delivery" "Dietary requirements" "Accessibility support"]',
                        ),
                        array(
                                'label'   => __( 'Select field', 'foodbank-manager' ),
                                'snippet' => '[select* fbm_collection_day "Thursday|thu" "Friday|fri" "Saturday|sat"]',
                        ),
                        array(
                                'label'   => __( 'File upload', 'foodbank-manager' ),
                                'snippet' => '[file fbm_proof_of_address]',
                        ),
                        array(
                                'label'   => __( 'Consent checkbox', 'foodbank-manager' ),
                                'snippet' => '[checkbox fbm_registration_consent "Yes, I consent to service updates."]',
                        ),
                        array(
                                'label'   => __( 'Submit button', 'foodbank-manager' ),
                                'snippet' => '[submit fbm_submit_button "Submit registration"]',
                        ),
                );
        }

        /**
         * Compute the screen ID for the editor page.
         */
        private static function screen_id(): string {
                return Menu::SLUG . '_page_' . self::MENU_SLUG;
        }
}
