<?php
/**
 * GDPR SAR admin page.
 *
 * @package FBM
 */

declare(strict_types=1);

namespace FBM\Admin;

use FBM\Exports\SarExporter;
use FoodBankManager\Database\ApplicationsRepo;
use FoodBankManager\Attendance\AttendanceRepo;
use FBM\Mail\LogRepo;
use function absint;
use function sanitize_key;
use function sanitize_text_field;
use function wp_unslash;

/**
 * GDPR SAR page controller.
 */
class GDPRPage {
	/**
	 * Handle exports.
	 */
	public static function route(): void {
		if ( ! current_user_can( 'fb_manage_diagnostics' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ), '', array( 'response' => 403 ) );
		}
		$method = strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read only
		if ( 'POST' !== $method ) {
			return;
		}
		$action = sanitize_key( wp_unslash( $_POST['fbm_action'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- checked below
		if ( 'export' !== $action ) {
			return;
		}
		check_admin_referer( 'fbm_gdpr_export', '_fbm_nonce' );
		$app_id = absint( $_POST['app_id'] ?? 0 );
		if ( $app_id <= 0 ) {
			return;
		}
				$masked  = ! current_user_can( 'fb_view_sensitive' );
				$subject = self::gather_subject( $app_id );
				$name    = 'sar-' . $app_id . '-' . gmdate( 'Ymd' );
				SarExporter::stream( $subject, $masked, $name );
				exit;
	}

	/**
	 * Render template.
	 */
	public static function render(): void {
		if ( ! current_user_can( 'fb_manage_diagnostics' ) ) {
				echo '<div class="wrap fbm-admin">'
						. '<div class="fbm-notice fbm-notice--error">You do not have permission to access this page.</div>'
						. '</div>';
				return;
		}
			/* @psalm-suppress UnresolvableInclude */
			require FBM_PATH . 'templates/admin/gdpr.php';
	}

	/**
	 * Gather data for export.
	 *
	 * @param int $app_id Application ID.
	 * @return array
	 */
	private static function gather_subject( int $app_id ): array {
		$app = ApplicationsRepo::get( $app_id );
		if ( $app ) {
			$app['files'] = ApplicationsRepo::get_files_for_application( $app_id );
		}
		return array(
			'applications' => $app ? array( $app ) : array(),
			'attendance'   => AttendanceRepo::find_by_application_id( $app_id ),
			'emails'       => LogRepo::find_by_application_id( $app_id ),
		);
	}
}
