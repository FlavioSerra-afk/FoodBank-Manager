<?php
/**
 * Mail resend controller.
 *
 * @package FBM\Http
 */

declare(strict_types=1);

namespace FBM\Http;

use FBM\Mail\LogRepo;
use function absint;
use function add_query_arg;
use function apply_filters;
use function check_admin_referer;
use function current_user_can;
use function esc_html__;
use function esc_url_raw;
use function get_current_user_id;
use function is_array;
use function menu_page_url;
use function sanitize_email;
use function sanitize_key;
use function sanitize_text_field;
use function time;
use function wp_mail;
use function wp_safe_redirect;
use function wp_unslash;

/**
 * Resend a logged email.
 */
final class MailResendController {
	/**
	 * Handle resend request.
	 *
	 * @return void
	 */
	public static function handle(): void {
		if ( ! current_user_can( 'fb_manage_settings' ) ) {
			wp_die( esc_html__( 'Forbidden', 'foodbank-manager' ) );
		}
		check_admin_referer( 'fbm_mail_resend' );

		$id = absint( wp_unslash( $_POST['id'] ?? 0 ) );
		if ( 0 === $id ) {
			self::redirect( 'not_found' );
		}

		$orig = LogRepo::get_by_id( $id );
		if ( ! $orig ) {
			self::redirect( 'not_found' );
		}

		$to      = sanitize_email( (string) ( $orig['to_email'] ?? '' ) );
		$subject = sanitize_text_field( (string) ( $orig['subject'] ?? '' ) );
		$headers = sanitize_text_field( (string) ( $orig['headers'] ?? '' ) );
		$vars    = array();
		if ( isset( $orig['body_vars'] ) && is_array( $orig['body_vars'] ) ) {
			foreach ( $orig['body_vars'] as $k => $v ) {
				$vars[ sanitize_key( (string) $k ) ] = sanitize_text_field( (string) $v );
			}
		}
		$message = (string) ( $vars['body'] ?? '' );

		$ok = false;
		if ( $to && $subject ) {
			$ok = wp_mail( $to, $subject, $message, $headers );
		}

		LogRepo::append(
			array(
				'type'        => 'resend',
				'original_id' => $id,
				'by'          => get_current_user_id(),
				'at'          => (int) apply_filters( 'fbm_now', time() ),
				'result'      => $ok ? 'sent' : 'error',
			)
		);

		self::redirect( $ok ? 'resent' : 'error' );
	}

	/**
	 * Redirect back to emails page.
	 *
	 * @param string $notice Notice code.
	 * @return void
	 */
	private static function redirect( string $notice ): void {
		$url = add_query_arg( array( 'notice' => $notice ), menu_page_url( 'fbm_emails', false ) );
		wp_safe_redirect( esc_url_raw( $url ), 303 );
		if ( apply_filters( 'fbm_http_exit', true ) ) {
			exit;
		}
	}
}
