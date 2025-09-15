<?php // phpcs:ignoreFile
/**
 * Reports admin page.
 *
 * @package FBM\Admin
 */

declare(strict_types=1);

namespace FBM\Admin;

use function current_user_can;
use function esc_html__;
use function esc_html;
use function esc_attr;
use function esc_url;
use function add_query_arg;
use function sanitize_text_field;
use function sanitize_key;
use function wp_create_nonce;
use function admin_url;
use function absint;

/**
 * Controller for Attendance reports page.
 */
final class ReportsPage {
	/**
	 * Route handler.
	 */
	public static function route(): void {
		if ( ! current_user_can( 'fb_manage_reports' ) ) {
			echo '<div class="wrap fbm-admin"><p>' . esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ) . '</p></div>';
			return;
		}
                $filters       = self::get_filters();
                $summary       = array(
                        'today'        => 0,
                        'week'         => 0,
                        'month'        => 0,
                        'unique_today' => 0,
                        'unique_week'  => 0,
                        'unique_month' => 0,
                );
                $daily         = array(
                        'days' => array(),
                );
                if ( class_exists( '\\FBM\\Attendance\\ReportsService' ) ) {
                        $summary = (array) call_user_func( array( '\\FBM\\Attendance\\ReportsService', 'period_summary' ), $filters );
                        $daily   = (array) call_user_func( array( '\\FBM\\Attendance\\ReportsService', 'daily_counts' ), 7, $filters );
                }
                                $nonce = wp_create_nonce( 'fbm_attendance_export' );
				$base  = admin_url( 'admin-post.php' );
				$query = array_merge( $filters, array( '_wpnonce' => $nonce ) );
		$csv_url       = esc_url( add_query_arg( array_merge( $query, array( 'action' => 'fbm_export_attendance_csv' ) ), $base ) );
		$xlsx_url      = esc_url( add_query_arg( array_merge( $query, array( 'action' => 'fbm_export_attendance_xlsx' ) ), $base ) );
		$pdf_url       = esc_url( add_query_arg( array_merge( $query, array( 'action' => 'fbm_export_attendance_pdf' ) ), $base ) );
		require FBM_PATH . 'templates/admin/reports.php';
	}

	/**
	 * Parse filters from query vars.
	 *
	 * @return array<string,mixed>
	 */
	private static function get_filters(): array {
		$from           = isset( $_GET['from'] ) ? sanitize_text_field( (string) $_GET['from'] ) : '';
		$to             = isset( $_GET['to'] ) ? sanitize_text_field( (string) $_GET['to'] ) : '';
				$method = isset( $_GET['method'] ) ? sanitize_key( (string) $_GET['method'] ) : '';
		if ( ! in_array( $method, array( 'qr', 'manual' ), true ) ) {
			$method = '';
		}
		return array(
			'from'   => $from,
			'to'     => $to,
			'method' => $method,
		);
	}
}
