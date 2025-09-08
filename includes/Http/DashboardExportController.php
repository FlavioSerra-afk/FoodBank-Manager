<?php // phpcs:ignoreFile
/**
 * Dashboard CSV export controller.
 *
 * @package FoodBankManager\Http
 */

declare(strict_types=1);

namespace FoodBankManager\Http;

use FoodBankManager\Attendance\AttendanceRepo;
use FoodBankManager\Exports\DashboardCsv;
use FoodBankManager\Shortcodes\Dashboard;
use function current_user_can;
use function esc_html__;
use function apply_filters;
use function sanitize_key;
use function wp_die;
use function wp_verify_nonce;

final class DashboardExportController {
	/**
	 * Handle export request.
	 */
	public static function handle(): void {
		if ( ! current_user_can( 'fb_manage_dashboard' ) ) {
			wp_die( esc_html__( 'Forbidden', 'foodbank-manager' ) );
		}
		$nonce = $_GET['_wpnonce'] ?? '';
		if ( ! wp_verify_nonce( $nonce, 'fbm_dash_export' ) ) {
			wp_die( esc_html__( 'Invalid nonce', 'foodbank-manager' ) );
		}
		$period  = Dashboard::sanitize_period( sanitize_key( (string) ( $_GET['period'] ?? '7d' ) ) );
		$since   = Dashboard::since_from_period( $period );
		$event   = Dashboard::sanitize_event( (string) ( $_GET['event'] ?? '' ) );
		$type    = Dashboard::sanitize_type( (string) ( $_GET['type'] ?? 'all' ) );
		$policy  = ! empty( $_GET['policy_only'] );
		$filters = array(
			'since'       => $since,
			'event'       => $event,
			'type'        => $type,
			'policy_only' => $policy,
		);
		$totals  = AttendanceRepo::period_totals( $since, $filters );
		$series  = AttendanceRepo::daily_present_counts( $since, $filters );
		$csv     = DashboardCsv::render( $totals, $series, $period, $filters );
		$date    = gmdate( 'Ymd' );
                $headers = array(
                        'Content-Type: text/csv; charset=utf-8',
                        'Content-Disposition: attachment; filename="fbm-dashboard-' . $date . '.csv"',
                );
                fbm_send_headers( $headers );
                echo $csv;
                if ( apply_filters( 'fbm_http_exit', true ) ) {
                        exit;
                }
	}
}
