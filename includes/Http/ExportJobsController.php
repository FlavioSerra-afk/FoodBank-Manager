<?php
/**
 * Export jobs HTTP controller.
 *
 * @package FBM\Http
 */

declare(strict_types=1);

namespace FBM\Http;

use FBM\Core\Jobs\JobsRepo;
use FBM\Core\Jobs\JobsWorker;
use function absint;
use function admin_url;
use function apply_filters;
use function current_user_can;
use function fbm_send_headers;
use function readfile;
use function sanitize_key;
use function wp_die;
use function wp_safe_redirect;
use function wp_verify_nonce;

/**
 * Handle export job queueing and downloads.
 */
final class ExportJobsController {
	/** Queue a new job. */
	public static function queue(): void {
		if ( ! current_user_can( 'fbm_manage_events' ) ) {
			wp_die( __( 'Forbidden', 'foodbank-manager' ) );
		}
		$nonce = $_POST['_wpnonce'] ?? '';
		if ( ! wp_verify_nonce( $nonce, 'fbm_export_queue' ) ) {
			wp_die( __( 'Invalid nonce', 'foodbank-manager' ) );
		}
		$format = isset( $_POST['format'] ) ? sanitize_key( (string) $_POST['format'] ) : 'csv';
		if ( ! in_array( $format, array( 'csv', 'xlsx', 'pdf' ), true ) ) {
			$format = 'csv';
		}
		$filters = isset( $_POST['filters'] ) && is_array( $_POST['filters'] ) ? $_POST['filters'] : array();
		$masked  = true;
		$unmask  = isset( $_POST['unmask'] ) && '1' === sanitize_key( (string) $_POST['unmask'] );
		if ( $unmask && current_user_can( 'fb_view_sensitive' ) ) {
			$masked = false;
		}
		$job_id = JobsRepo::create( 'attendance_export', $format, $filters, $masked );
		$loc    = admin_url( 'admin.php?page=fbm_reports&notice=export_queued&job=' . $job_id );
		wp_safe_redirect( $loc );
		if ( apply_filters( 'fbm_http_exit', true ) ) {
			exit;
		}
	}

	/** Secure file download. */
	public static function download(): void {
		if ( ! current_user_can( 'fbm_manage_events' ) ) {
			wp_die( __( 'Forbidden', 'foodbank-manager' ) );
		}
		$id    = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		$nonce = $_GET['_wpnonce'] ?? '';
		if ( ! $id || ! wp_verify_nonce( $nonce, 'fbm_export_download_' . $id ) ) {
			wp_die( __( 'Invalid nonce', 'foodbank-manager' ) );
		}
		$job = JobsRepo::get( $id );
		if ( ! $job || 'done' !== $job['status'] ) {
			$notice = ! $job || 'failed' === ( $job['status'] ?? '' ) ? 'export_failed' : 'export_pending';
			wp_safe_redirect( admin_url( 'admin.php?page=fbm_reports&notice=' . $notice . '&job=' . $id ) );
			if ( apply_filters( 'fbm_http_exit', true ) ) {
				exit;
			}
			return;
		}
		$path = $job['file_path'];
		$ext  = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
		$type = 'application/octet-stream';
		switch ( $ext ) {
			case 'csv':
				$type = 'text/csv; charset=utf-8';
				break;
			case 'xlsx':
				$type = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
				break;
			case 'pdf':
				$type = 'application/pdf';
				break;
			case 'html':
				$type = 'text/html; charset=utf-8';
				break;
		}
		$headers = array(
			'Content-Type: ' . $type,
			'Content-Disposition: attachment; filename="' . basename( $path ) . '"',
		);
		fbm_send_headers( $headers );
		readfile( $path );
		if ( apply_filters( 'fbm_http_exit', true ) ) {
			exit;
		}
	}

	/** Run worker now. */
	public static function run(): void {
		if ( ! current_user_can( 'fb_manage_diagnostics' ) ) {
			wp_die( __( 'Forbidden', 'foodbank-manager' ) );
		}
		$nonce = $_POST['_wpnonce'] ?? '';
		if ( ! wp_verify_nonce( $nonce, 'fbm_export_job_run' ) ) {
			wp_die( __( 'Invalid nonce', 'foodbank-manager' ) );
		}
		JobsWorker::tick();
		wp_safe_redirect( admin_url( 'admin.php?page=fbm_diagnostics' ) );
		if ( apply_filters( 'fbm_http_exit', true ) ) {
			exit;
		}
	}

	/** Retry a failed job. */
	public static function retry(): void {
		if ( ! current_user_can( 'fb_manage_diagnostics' ) ) {
			wp_die( __( 'Forbidden', 'foodbank-manager' ) );
		}
		$id    = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$nonce = $_POST['_wpnonce'] ?? '';
		if ( ! $id || ! wp_verify_nonce( $nonce, 'fbm_export_job_retry_' . $id ) ) {
			wp_die( __( 'Invalid nonce', 'foodbank-manager' ) );
		}
		JobsRepo::retry( $id );
		wp_safe_redirect( admin_url( 'admin.php?page=fbm_diagnostics' ) );
		if ( apply_filters( 'fbm_http_exit', true ) ) {
			exit;
		}
	}
}
