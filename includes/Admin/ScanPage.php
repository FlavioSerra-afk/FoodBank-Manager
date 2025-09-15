<?php
/**
 * Scan admin page.
 *
 * @package FBM\Admin
 */

declare(strict_types=1);

namespace FBM\Admin;

use WP_REST_Request;
use function current_user_can;
use function wp_nonce_field;
use function check_admin_referer;
use function sanitize_text_field;
use function wp_unslash;
use function wp_create_nonce;
use function esc_html__;

/**
 * Admin scan page controller.
 */
final class ScanPage {
	/**
	 * Route handler.
	 */
	public static function route(): void {
		if ( ! current_user_can( 'fb_manage_attendance' ) ) {
			echo '<div class="wrap fbm-admin"><p>' . esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ) . '</p></div>';
			return;
		}
		$status    = '';
		$recipient = '';
		$method    = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( (string) $_SERVER['REQUEST_METHOD'] ) : 'GET';
		if ( 'POST' === $method ) {
			check_admin_referer( 'fbm_scan_verify', 'fbm_nonce' );
			$token      = isset( $_POST['token'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['token'] ) ) : '';
                        $controller_class = '\\FBM\\Rest\\ScanController';
                        if ( class_exists( $controller_class ) ) {
                                $controller = new $controller_class();
                                $req        = new WP_REST_Request();
                                $req->set_header( 'x-wp-nonce', wp_create_nonce( 'wp_rest' ) );
                                $req->set_param( 'token', $token );
                                $res       = $controller->verify( $req );
                                $data      = $res->get_data();
                                $status    = (string) ( $data['status'] ?? '' );
                                $recipient = (string) ( $data['recipient_masked'] ?? '' );
                        } else {
                                $status = 'error';
                        }
		}
		require FBM_PATH . 'templates/admin/scan.php';
	}
}
