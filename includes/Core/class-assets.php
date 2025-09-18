<?php
/**
 * Asset management helpers.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Core;

use function add_action;
use function current_user_can;
use function esc_html__;
use function esc_url_raw;
use function function_exists;
use function get_current_screen;
use function is_user_logged_in;
use function plugins_url;
use function rest_url;
use function sprintf;
use function wp_create_nonce;
use function wp_enqueue_script;
use function wp_enqueue_style;
use function wp_localize_script;
use function wp_register_script;
use function wp_register_style;
use function wp_set_script_translations;

/**
 * Coordinates asset loading and gating.
 */
final class Assets {

	private const STAFF_HANDLE   = 'fbm-staff-dashboard';
	private const STAFF_STYLE    = 'fbm-staff-dashboard';
	private const SCANNER_HANDLE = 'fbm-scanner';
	private const ZXING_HANDLE   = 'fbm-zxing-browser';

	/**
	 * Tracks whether staff assets should be enqueued.
	 *
	 * @var bool
	 */
	private static bool $load_staff_dashboard = false;

	/**
	 * Hook the WordPress lifecycle.
	 */
	public static function setup(): void {
		add_action( 'admin_enqueue_scripts', array( self::class, 'maybe_enqueue_admin' ), 10, 1 );
		add_action( 'wp_enqueue_scripts', array( self::class, 'maybe_enqueue_staff_dashboard' ) );
	}

	/**
	 * Flag that the staff dashboard assets should load for this request.
	 */
	public static function mark_staff_dashboard(): void {
		self::$load_staff_dashboard = true;
	}

	/**
	 * Restrict admin assets to FBM screens.
	 */
	/**
	 * Restrict admin assets to FBM screens.
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 */
	public static function maybe_enqueue_admin( string $hook_suffix ): void {
		unset( $hook_suffix );
		if ( ! function_exists( 'get_current_screen' ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}

		if ( strpos( (string) $screen->id, 'fbm' ) === false ) {
			return;
		}

		if ( ! function_exists( 'wp_enqueue_style' ) ) {
			return;
		}

		$version = defined( 'FBM_VER' ) ? FBM_VER : '1.0.7';
		$style   = plugins_url( 'assets/css/admin.css', FBM_FILE );

		wp_enqueue_style( 'fbm-admin', $style, array(), $version );
	}

	/**
	 * Load staff dashboard assets only when required and authorized.
	 */
	public static function maybe_enqueue_staff_dashboard(): void {
		if ( ! self::$load_staff_dashboard ) {
			return;
		}

		self::$load_staff_dashboard = false;

		if ( ! is_user_logged_in() || ! current_user_can( 'fbm_view' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability registered on activation.
			return;
		}

		if ( ! function_exists( 'wp_register_style' ) || ! function_exists( 'wp_register_script' ) ) {
			return;
		}

		$version        = defined( 'FBM_VER' ) ? FBM_VER : '1.0.7';
		$style          = plugins_url( 'assets/css/staff-dashboard.css', FBM_FILE );
		$script         = plugins_url( 'assets/js/staff-dashboard.js', FBM_FILE );
		$scanner_script = plugins_url( 'assets/js/fbm-scanner.js', FBM_FILE );
		$zxing_script   = plugins_url( 'assets/vendor/zxing-browser.min.js', FBM_FILE );
		$window         = ( new Schedule() )->current_window();
		$labels         = Schedule::window_labels( $window );

		$ready_message = sprintf(
			/* translators: %s: Description of the scheduled collection window. */
			esc_html__( 'Ready for the next collection. Collections run on %s.', 'foodbank-manager' ),
			$labels['sentence']
		);

		$out_of_window_message = sprintf(
			/* translators: %s: Description of the scheduled collection window. */
			esc_html__( 'Outside the %s collection window.', 'foodbank-manager' ),
			$labels['sentence']
		);

		wp_register_style( self::STAFF_STYLE, $style, array(), $version );
		wp_enqueue_style( self::STAFF_STYLE );

		wp_register_script( self::STAFF_HANDLE, $script, array(), $version, true );
		wp_register_script( self::ZXING_HANDLE, $zxing_script, array(), '0.1.5', true );
		wp_register_script( self::SCANNER_HANDLE, $scanner_script, array( self::STAFF_HANDLE, self::ZXING_HANDLE ), $version, true );

		$data = array(
			'restUrl'  => esc_url_raw( rest_url( 'fbm/v1/checkin' ) ),
			'nonce'    => wp_create_nonce( 'wp_rest' ),
			'schedule' => array(
				'window' => $window,
				'labels' => $labels,
			),
			'strings'  => array(
				'ready'                    => $ready_message,
				'loading'                  => esc_html__( 'Recording collection…', 'foodbank-manager' ),
				'success'                  => esc_html__( 'Collection recorded.', 'foodbank-manager' ),
				'recent_warning'           => esc_html__( 'Member collected less than a week ago. Only managers can continue with a justified override.', 'foodbank-manager' ),
				'duplicate_day'            => esc_html__( 'Member already collected today.', 'foodbank-manager' ),
				'out_of_window'            => $out_of_window_message,
				'collection_window_notice' => Schedule::window_notice( $window ),
				'error'                    => esc_html__( 'Unable to record collection. Please try again.', 'foodbank-manager' ),
				'scanner_ready'            => esc_html__( 'Camera starting…', 'foodbank-manager' ),
				'scanner_active'           => esc_html__( 'Scanning for QR codes…', 'foodbank-manager' ),
				'scanner_error'            => esc_html__( 'Unable to start the camera. Try manual entry instead.', 'foodbank-manager' ),
				'scanner_unsupported'      => esc_html__( 'Camera scanning is not supported on this device.', 'foodbank-manager' ),
				'scanner_permission'       => esc_html__( 'Camera permission denied. Use manual entry instead.', 'foodbank-manager' ),
				'scanner_hold_steady'      => esc_html__( 'Hold steady, avoid glare.', 'foodbank-manager' ),
				'scanner_camera_label'     => esc_html__( 'Camera source', 'foodbank-manager' ),
				'scanner_camera_default'   => esc_html__( 'Default camera', 'foodbank-manager' ),
				'scanner_torch_on'         => esc_html__( 'Turn torch on', 'foodbank-manager' ),
				'scanner_torch_off'        => esc_html__( 'Turn torch off', 'foodbank-manager' ),
				'reference_required'       => esc_html__( 'Enter a member reference before recording.', 'foodbank-manager' ),
				'override_note_required'   => esc_html__( 'An override note is required.', 'foodbank-manager' ),
				'override_success'         => esc_html__( 'Override recorded successfully.', 'foodbank-manager' ),
				/* translators: %s: Member reference. */
				'override_prompt'          => esc_html__( 'Member %s collected within the last week.', 'foodbank-manager' ),
				'override_requirements'    => esc_html__( 'Only managers can continue by recording an override with a justification.', 'foodbank-manager' ),
			),
		);

		wp_localize_script( self::STAFF_HANDLE, 'fbmStaffDashboard', $data );
		wp_set_script_translations( self::STAFF_HANDLE, 'foodbank-manager' );
		wp_enqueue_script( self::STAFF_HANDLE );
		wp_enqueue_script( self::ZXING_HANDLE );
		wp_enqueue_script( self::SCANNER_HANDLE );
	}
}
