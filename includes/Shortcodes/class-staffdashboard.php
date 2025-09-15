<?php
/**
 * Staff dashboard shortcode handler.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Shortcodes;

use FoodBankManager\Core\Assets;
use function add_shortcode;
use function current_user_can;
use function esc_html__;
use function function_exists;
use function is_readable;
use function is_user_logged_in;
use function ob_get_clean;
use function ob_start;
use function status_header;

/**
 * Renders the staff dashboard shortcode.
 */
final class StaffDashboard {

	private const SHORTCODE = 'fbm_staff_dashboard';

	/**
	 * Register the shortcode with WordPress.
	 */
	public static function register(): void {
		add_shortcode( self::SHORTCODE, array( self::class, 'render' ) );
	}

	/**
	 * Render the staff dashboard view.
	 *
	 * @param array<string, mixed> $atts Shortcode attributes.
	 */
	public static function render( array $atts = array() ): string {
			unset( $atts );

		if ( ! is_user_logged_in() || ! current_user_can( 'fbm_view' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability registered on activation.
			if ( function_exists( 'status_header' ) ) {
				status_header( 403 );
			}

				return '<div class="fbm-staff-dashboard fbm-staff-dashboard--denied">'
				. esc_html__( 'Staff dashboard is available to authorised team members only.', 'foodbank-manager' )
				. '</div>';
		}

			Assets::mark_staff_dashboard();

			ob_start();
			$template = FBM_PATH . 'templates/public/staff-dashboard.php';
		if ( is_readable( $template ) ) {
			include $template;
		}

			$output = ob_get_clean();

			return is_string( $output ) ? $output : '';
	}
}
