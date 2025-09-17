<?php
/**
 * Theme settings admin page.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use WP_Error;
use function __;
use function add_action;
use function add_filter;
use function add_query_arg;
use function add_settings_field;
use function add_settings_section;
use function add_submenu_page;
use function admin_url;
use function array_merge;
use function check_admin_referer;
use function current_user_can;
use function esc_attr;
use function esc_html;
use function esc_html__;
use function get_option;
use function in_array;
use function is_array;
use function is_numeric;
use function is_readable;
use function wp_json_encode;
use function max;
use function min;
use function preg_match;
use function register_setting;
use function round;
use function sanitize_key;
use function sanitize_text_field;
use function sprintf;
use function strlen;
use function strtoupper;
use function selected;
use function update_option;
use function wp_die;
use function wp_safe_redirect;
use function wp_unslash;

/**
 * Provides the Food Bank theme settings page.
 */
final class ThemePage {

	private const MENU_SLUG     = 'fbm-theme';
	private const PARENT_SLUG   = 'fbm-members';
	private const OPTION_GROUP  = 'fbm_theme';
	private const OPTION_NAME   = 'fbm_theme';
	private const TEMPLATE      = 'templates/admin/theme-page.php';
	private const SECTION_STYLE = 'fbm_theme_style';
	private const SECTION_GLASS = 'fbm_theme_glass';
	private const FORM_ACTION   = 'fbm_theme_save';
	private const NONCE_NAME    = 'fbm_theme_nonce';
	private const STATUS_PARAM  = 'fbm_theme_status';
	private const MESSAGE_PARAM = 'fbm_theme_message';
	private const MAX_PAYLOAD   = 2048;

	private const ALLOWED_STYLES  = array( 'basic', 'glass' );
	private const ALLOWED_PRESETS = array( 'light', 'dark', 'high_contrast' );

	private const DEFAULT_THEME = array(
		'version' => 1,
		'style'   => 'basic',
		'preset'  => 'light',
		'accent'  => '#0B5FFF',
		'glass'   => array(
			'alpha'  => 0.0,
			'blur'   => 0,
			'elev'   => 8,
			'radius' => 12,
			'border' => 1,
		),
	);

	/**
	 * Register WordPress hooks.
	 */
	public static function register(): void {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'admin_post_' . self::FORM_ACTION, array( __CLASS__, 'handle_save' ) );
		add_filter( 'allowed_options', array( __CLASS__, 'filter_allowed_options' ) );
	}

	/**
	 * Register the admin menu entry.
	 */
	public static function register_menu(): void {
		add_submenu_page(
			self::PARENT_SLUG,
			__( 'Food Bank Theme', 'foodbank-manager' ),
			__( 'Theme', 'foodbank-manager' ),
			'fbm_manage', // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability registered during activation.
			self::MENU_SLUG,
			array( __CLASS__, 'render' )
		);
	}

	/**
	 * Register the fbm_theme option with sections and fields.
	 */
	public static function register_settings(): void {
		register_setting(
			self::OPTION_GROUP,
			self::OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => self::default_theme(),
				'show_in_rest'      => false,
				'capability'        => 'fbm_manage',
			)
		);

		add_settings_section(
			self::SECTION_STYLE,
			__( 'Display', 'foodbank-manager' ),
			array( __CLASS__, 'render_style_section' ),
			self::MENU_SLUG
		);

		add_settings_field(
			'fbm_theme_style',
			__( 'Theme style', 'foodbank-manager' ),
			array( __CLASS__, 'render_style_field' ),
			self::MENU_SLUG,
			self::SECTION_STYLE
		);

		add_settings_field(
			'fbm_theme_preset',
			__( 'Preset', 'foodbank-manager' ),
			array( __CLASS__, 'render_preset_field' ),
			self::MENU_SLUG,
			self::SECTION_STYLE
		);

		add_settings_field(
			'fbm_theme_accent',
			__( 'Accent color', 'foodbank-manager' ),
			array( __CLASS__, 'render_accent_field' ),
			self::MENU_SLUG,
			self::SECTION_STYLE
		);

		add_settings_section(
			self::SECTION_GLASS,
			__( 'Glass effects', 'foodbank-manager' ),
			array( __CLASS__, 'render_glass_section' ),
			self::MENU_SLUG
		);

		add_settings_field(
			'fbm_theme_glass_alpha',
			__( 'Alpha', 'foodbank-manager' ),
			array( __CLASS__, 'render_glass_alpha_field' ),
			self::MENU_SLUG,
			self::SECTION_GLASS
		);

		add_settings_field(
			'fbm_theme_glass_blur',
			__( 'Blur', 'foodbank-manager' ),
			array( __CLASS__, 'render_glass_blur_field' ),
			self::MENU_SLUG,
			self::SECTION_GLASS
		);

		add_settings_field(
			'fbm_theme_glass_elev',
			__( 'Elevation', 'foodbank-manager' ),
			array( __CLASS__, 'render_glass_elev_field' ),
			self::MENU_SLUG,
			self::SECTION_GLASS
		);

		add_settings_field(
			'fbm_theme_glass_radius',
			__( 'Radius', 'foodbank-manager' ),
			array( __CLASS__, 'render_glass_radius_field' ),
			self::MENU_SLUG,
			self::SECTION_GLASS
		);

		add_settings_field(
			'fbm_theme_glass_border',
			__( 'Border width', 'foodbank-manager' ),
			array( __CLASS__, 'render_glass_border_field' ),
			self::MENU_SLUG,
			self::SECTION_GLASS
		);
	}

	/**
	 * Render the theme settings page.
	 */
	public static function render(): void {
		if ( ! current_user_can( 'fbm_manage' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability defined on activation.
			wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ) );
		}

		$theme = self::get_theme_option();

		$status  = '';
		$message = '';

		if ( isset( $_GET[ self::STATUS_PARAM ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display context only.
			$status = sanitize_key( (string) wp_unslash( $_GET[ self::STATUS_PARAM ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display context only.
		}

		if ( isset( $_GET[ self::MESSAGE_PARAM ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display context only.
			$message = sanitize_text_field( (string) wp_unslash( $_GET[ self::MESSAGE_PARAM ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display context only.
		}

		$template = FBM_PATH . self::TEMPLATE;

		if ( ! is_readable( $template ) ) {
			wp_die( esc_html__( 'Theme admin template is missing.', 'foodbank-manager' ) );
		}

		$data = array(
			'theme'   => $theme,
			'status'  => $status,
			'message' => $message,
		);

		include $template;
	}

	/**
	 * Handle the form submission.
	 */
	public static function handle_save(): void {
		if ( ! current_user_can( 'fbm_manage' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability defined on activation.
			wp_die( esc_html__( 'You do not have permission to save theme settings.', 'foodbank-manager' ) );
		}

		check_admin_referer( self::FORM_ACTION, self::NONCE_NAME );

		$raw = array();

		if ( isset( $_POST[ self::OPTION_NAME ] ) && is_array( $_POST[ self::OPTION_NAME ] ) ) {
			$raw = wp_unslash( $_POST[ self::OPTION_NAME ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized in sanitize().
		}

		$result = self::sanitize( $raw );

		$status  = 'error';
		$message = esc_html__( 'Theme settings could not be saved.', 'foodbank-manager' );

		if ( $result instanceof WP_Error ) {
			$error_message = $result->get_error_message();

			if ( '' !== $error_message ) {
				$message = $error_message;
			}
		} else {
			update_option( self::OPTION_NAME, $result );
			$status  = 'success';
			$message = esc_html__( 'Theme settings saved.', 'foodbank-manager' );
		}

		$message = sanitize_text_field( $message );

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
	 * Sanitize the theme option payload.
	 *
	 * @param mixed $value Raw option value.
	 *
	 * @return array|WP_Error
	 */
	public static function sanitize( $value ) {
		if ( ! is_array( $value ) ) {
			return new WP_Error( 'fbm_theme_invalid_payload', __( 'Theme payload must be an array.', 'foodbank-manager' ) );
		}

		$theme = self::default_theme();

		$style = $theme['style'];

		if ( isset( $value['style'] ) ) {
			$style = sanitize_key( (string) $value['style'] );
		}

		if ( ! in_array( $style, self::ALLOWED_STYLES, true ) ) {
			return new WP_Error( 'fbm_theme_invalid_style', __( 'The selected style is not available.', 'foodbank-manager' ) );
		}

		$theme['style'] = $style;

		$preset = $theme['preset'];

		if ( isset( $value['preset'] ) ) {
			$preset = sanitize_key( (string) $value['preset'] );
		}

		if ( ! in_array( $preset, self::ALLOWED_PRESETS, true ) ) {
			return new WP_Error( 'fbm_theme_invalid_preset', __( 'The selected preset is not available.', 'foodbank-manager' ) );
		}

		$theme['preset'] = $preset;

		if ( isset( $value['accent'] ) ) {
			$accent = strtoupper( (string) $value['accent'] );
		} else {
			$accent = $theme['accent'];
		}

		if ( 1 !== preg_match( '/^#[0-9A-F]{6}$/', $accent ) ) {
			return new WP_Error( 'fbm_theme_invalid_accent', __( 'Accent color must be a valid hex value.', 'foodbank-manager' ) );
		}

		$theme['accent'] = $accent;

		$glass = $theme['glass'];

		if ( isset( $value['glass'] ) && is_array( $value['glass'] ) ) {
			$glass = self::sanitize_glass( $value['glass'], $theme['glass'] );

			if ( $glass instanceof WP_Error ) {
				return $glass;
			}
		}

		$theme['glass'] = $glass;

		$encoded = wp_json_encode( $theme );

		if ( false === $encoded ) {
			return new WP_Error( 'fbm_theme_encoding_failed', __( 'Unable to encode theme settings.', 'foodbank-manager' ) );
		}

		if ( strlen( $encoded ) > self::MAX_PAYLOAD ) {
			return new WP_Error( 'fbm_theme_payload_oversize', __( 'Theme settings payload is too large.', 'foodbank-manager' ) );
		}

		return $theme;
	}

	/**
	 * Ensure the fbm_theme option is on the allowed options list.
	 *
 * @param array<string,mixed> $options Allowed options map.
	 *
	 * @return array<string,array<int,string>>
	 */
	public static function filter_allowed_options( array $options ): array {
		if ( ! isset( $options[ self::OPTION_GROUP ] ) || ! is_array( $options[ self::OPTION_GROUP ] ) ) {
			$options[ self::OPTION_GROUP ] = array();
		}

		if ( ! in_array( self::OPTION_NAME, $options[ self::OPTION_GROUP ], true ) ) {
			$options[ self::OPTION_GROUP ][] = self::OPTION_NAME;
		}

		return $options;
	}

	/**
	 * Default theme payload.
	 *
	 * @return array<string,mixed>
	 */
	public static function default_theme(): array {
		return self::DEFAULT_THEME;
	}

	/**
	 * Render the style section intro.
	 */
	public static function render_style_section(): void {
		printf( '<p>%s</p>', esc_html__( 'Control the base theme, preset, and accent color.', 'foodbank-manager' ) );
	}

	/**
	 * Render the glass section intro.
	 */
	public static function render_glass_section(): void {
		printf( '<p>%s</p>', esc_html__( 'Adjust the glass styling parameters for supported themes.', 'foodbank-manager' ) );
	}

	/**
	 * Render the theme style field.
	 */
	public static function render_style_field(): void {
		$theme  = self::get_theme_option();
		$style  = isset( $theme['style'] ) ? (string) $theme['style'] : self::DEFAULT_THEME['style'];
		$styles = array(
			'basic' => __( 'Basic', 'foodbank-manager' ),
			'glass' => __( 'Glass', 'foodbank-manager' ),
		);

		echo '<select id="fbm_theme_style" name="' . esc_attr( self::OPTION_NAME ) . '[style]">';

		foreach ( $styles as $value => $label ) {
			echo '<option value="' . esc_attr( $value ) . '"' . selected( $style, $value, false ) . '>' . esc_html( $label ) . '</option>';
		}

		echo '</select>';
	}

	/**
	 * Render the preset field.
	 */
	public static function render_preset_field(): void {
		$theme   = self::get_theme_option();
		$preset  = isset( $theme['preset'] ) ? (string) $theme['preset'] : self::DEFAULT_THEME['preset'];
		$presets = array(
			'light'         => __( 'Light', 'foodbank-manager' ),
			'dark'          => __( 'Dark', 'foodbank-manager' ),
			'high_contrast' => __( 'High contrast', 'foodbank-manager' ),
		);

		echo '<select id="fbm_theme_preset" name="' . esc_attr( self::OPTION_NAME ) . '[preset]">';

		foreach ( $presets as $value => $label ) {
			echo '<option value="' . esc_attr( $value ) . '"' . selected( $preset, $value, false ) . '>' . esc_html( $label ) . '</option>';
		}

		echo '</select>';
	}

	/**
	 * Render the accent field.
	 */
	public static function render_accent_field(): void {
		$theme  = self::get_theme_option();
		$accent = isset( $theme['accent'] ) ? (string) $theme['accent'] : self::DEFAULT_THEME['accent'];

		echo '<input type="text" id="fbm_theme_accent" name="' . esc_attr( self::OPTION_NAME ) . '[accent]" value="' . esc_attr( $accent ) . '" class="regular-text" />';
	}

	/**
	 * Render the glass alpha field.
	 */
	public static function render_glass_alpha_field(): void {
		$glass = self::get_theme_glass();
		$alpha = isset( $glass['alpha'] ) ? (float) $glass['alpha'] : (float) self::DEFAULT_THEME['glass']['alpha'];

		echo '<input type="number" id="fbm_theme_glass_alpha" name="' . esc_attr( self::OPTION_NAME ) . '[glass][alpha]" min="0" max="1" step="0.05" value="' . esc_attr( sprintf( '%.2f', $alpha ) ) . '" />';
	}

	/**
	 * Render the glass blur field.
	 */
	public static function render_glass_blur_field(): void {
		$glass = self::get_theme_glass();
		$blur  = isset( $glass['blur'] ) ? (int) $glass['blur'] : (int) self::DEFAULT_THEME['glass']['blur'];

		echo '<input type="number" id="fbm_theme_glass_blur" name="' . esc_attr( self::OPTION_NAME ) . '[glass][blur]" min="0" max="20" value="' . esc_attr( (string) $blur ) . '" />';
	}

	/**
	 * Render the glass elevation field.
	 */
	public static function render_glass_elev_field(): void {
		$glass = self::get_theme_glass();
		$elev  = isset( $glass['elev'] ) ? (int) $glass['elev'] : (int) self::DEFAULT_THEME['glass']['elev'];

		echo '<input type="number" id="fbm_theme_glass_elev" name="' . esc_attr( self::OPTION_NAME ) . '[glass][elev]" min="0" max="24" value="' . esc_attr( (string) $elev ) . '" />';
	}

	/**
	 * Render the glass radius field.
	 */
	public static function render_glass_radius_field(): void {
		$glass  = self::get_theme_glass();
		$radius = isset( $glass['radius'] ) ? (int) $glass['radius'] : (int) self::DEFAULT_THEME['glass']['radius'];

		echo '<input type="number" id="fbm_theme_glass_radius" name="' . esc_attr( self::OPTION_NAME ) . '[glass][radius]" min="6" max="20" value="' . esc_attr( (string) $radius ) . '" />';
	}

	/**
	 * Render the glass border field.
	 */
	public static function render_glass_border_field(): void {
		$glass  = self::get_theme_glass();
		$border = isset( $glass['border'] ) ? (int) $glass['border'] : (int) self::DEFAULT_THEME['glass']['border'];

		echo '<input type="number" id="fbm_theme_glass_border" name="' . esc_attr( self::OPTION_NAME ) . '[glass][border]" min="1" max="2" value="' . esc_attr( (string) $border ) . '" />';
	}

	/**
	 * Sanitize glass settings.
	 *
	 * @param array<string,mixed> $glass    Raw glass payload.
	 * @param array<string,mixed> $defaults Default glass payload.
	 *
	 * @return array<string,mixed>|WP_Error
	 */
	private static function sanitize_glass( array $glass, array $defaults ) {
		$result = $defaults;

		if ( isset( $glass['alpha'] ) ) {
			if ( ! is_numeric( $glass['alpha'] ) ) {
				return new WP_Error( 'fbm_theme_invalid_alpha', __( 'Glass alpha must be a number.', 'foodbank-manager' ) );
			}

			$value           = (float) $glass['alpha'];
			$result['alpha'] = max( 0.0, min( 1.0, $value ) );
		}

		if ( isset( $glass['blur'] ) ) {
			if ( ! is_numeric( $glass['blur'] ) ) {
				return new WP_Error( 'fbm_theme_invalid_blur', __( 'Glass blur must be an integer.', 'foodbank-manager' ) );
			}

			$value          = (int) round( (float) $glass['blur'] );
			$result['blur'] = max( 0, min( 20, $value ) );
		}

		if ( isset( $glass['elev'] ) ) {
			if ( ! is_numeric( $glass['elev'] ) ) {
				return new WP_Error( 'fbm_theme_invalid_elev', __( 'Glass elevation must be an integer.', 'foodbank-manager' ) );
			}

			$value          = (int) round( (float) $glass['elev'] );
			$result['elev'] = max( 0, min( 24, $value ) );
		}

		if ( isset( $glass['radius'] ) ) {
			if ( ! is_numeric( $glass['radius'] ) ) {
				return new WP_Error( 'fbm_theme_invalid_radius', __( 'Glass radius must be an integer.', 'foodbank-manager' ) );
			}

			$value            = (int) round( (float) $glass['radius'] );
			$result['radius'] = max( 6, min( 20, $value ) );
		}

		if ( isset( $glass['border'] ) ) {
			if ( ! is_numeric( $glass['border'] ) ) {
				return new WP_Error( 'fbm_theme_invalid_border', __( 'Glass border width must be an integer.', 'foodbank-manager' ) );
			}

			$value            = (int) round( (float) $glass['border'] );
			$result['border'] = max( 1, min( 2, $value ) );
		}

		$result['alpha']  = (float) $result['alpha'];
		$result['blur']   = (int) $result['blur'];
		$result['elev']   = (int) $result['elev'];
		$result['radius'] = (int) $result['radius'];
		$result['border'] = (int) $result['border'];

		return $result;
	}

	/**
	 * Retrieve the currently saved theme option.
	 *
	 * @return array<string,mixed>
	 */
	private static function get_theme_option(): array {
		$option = get_option( self::OPTION_NAME, self::default_theme() );

		if ( ! is_array( $option ) ) {
			return self::default_theme();
		}

		return array_merge( self::default_theme(), $option );
	}

	/**
	 * Retrieve the current glass settings.
	 *
	 * @return array<string,mixed>
	 */
	private static function get_theme_glass(): array {
		$theme = self::get_theme_option();

		if ( isset( $theme['glass'] ) && is_array( $theme['glass'] ) ) {
			return array_merge( self::DEFAULT_THEME['glass'], $theme['glass'] );
		}

		return self::DEFAULT_THEME['glass'];
	}
}
