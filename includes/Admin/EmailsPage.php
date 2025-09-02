<?php
/**
 * Email templates admin page.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FoodBankManager\Mail\Templates;
use WP_User;

/**
 * Emails admin page.
 */
class EmailsPage {

	/**
	 * Route the emails page.
	 *
	 * @since 0.1.1
	 */
	public static function route(): void {
		if ( ! current_user_can( 'fb_manage_emails' ) && ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ), '', array( 'response' => 403 ) );
		}
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only preview; template sanitized in handler.
		if ( isset( $_GET['preview'], $_GET['template'] ) ) {
			self::handle_preview();
			return;
		}
		if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] ) {
			self::handle_post();
		}
	}

	/**
	 * Handle save or send test actions.
	 *
	 * @since 0.1.1
	 */
	private static function handle_post(): void {
		check_admin_referer( 'fbm_admin_action', '_fbm_nonce' );
		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'fb_manage_emails' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'foodbank-manager' ) );
		}
		$action = sanitize_key( (string) ( $_POST['fbm_email_action'] ?? '' ) );
		if ( 'send_test' === $action ) {
			self::send_test();
			return;
		}
		$data = isset( $_POST['templates'] ) && is_array( $_POST['templates'] )
			? array_map(
				fn( $tpl ) => array(
					'subject' => sanitize_text_field( $tpl['subject'] ?? '' ),
					'body'    => wp_kses_post( $tpl['body'] ?? '' ),
				),
				(array) wp_unslash( $_POST['templates'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized in array_map.
			)
			: array();
		Templates::saveAll( $data );
		add_settings_error( 'fbm-emails', 'fbm_saved', esc_html__( 'Templates saved.', 'foodbank-manager' ), 'updated' );
		$url = wp_get_referer();
		$url = $url ? $url : admin_url();
		wp_safe_redirect( esc_url_raw( $url ) );
		exit;
	}

	/**
	 * Send a test email.
	 *
	 * @since 0.1.1
	 */
	private static function send_test(): void {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified in handle_post().
		$template = sanitize_key( (string) ( $_POST['test_template'] ?? 'applicant_confirmation' ) );
		$user     = wp_get_current_user();
		if ( ! $user instanceof WP_User ) {
			return;
		}
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified in handle_post().
		$to = sanitize_email( (string) wp_unslash( $_POST['test_email'] ?? $user->user_email ) );
		if ( ! $to || ! is_email( $to ) ) {
			return;
		}
		$rendered = Templates::render( $template, self::sampleVars() );
		wp_mail( $to, $rendered['subject'], $rendered['body_html'], array( 'Content-Type: text/html; charset=UTF-8' ) );
		add_settings_error( 'fbm-emails', 'fbm_test', esc_html__( 'Test email sent.', 'foodbank-manager' ), 'updated' );
		$url = wp_get_referer();
		$url = $url ? $url : admin_url();
		wp_safe_redirect( esc_url_raw( $url ) );
		exit;
	}

	/**
	 * Preview an email template.
	 *
	 * @since 0.1.1
	 */
	private static function handle_preview(): void {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only preview; template sanitized below.
		$template = sanitize_key( (string) ( $_GET['template'] ?? '' ) );
		$rendered = Templates::render( $template, self::sampleVars() );
		header( 'Content-Type: text/html; charset=UTF-8' );
		echo wp_kses_post( $rendered['body_html'] );
		exit;
	}

	/**
	 * Sample variables for preview/test.
	 *
	 * @since 0.1.1
	 *
	 * @return array<string,string>
	 */
	private static function sampleVars(): array {
		$now = current_time( 'mysql' );
		return array(
			'application_id'   => 123,
			'first_name'       => 'Jane',
			'last_name'        => 'Doe',
			'created_at'       => $now,
			'summary_table'    => '<table><tr><th>Name</th><td>Jane Doe</td></tr></table>',
			'qr_code_url'      => 'https://example.com/qr.png',
			'reference'        => 'FBM-123',
			'application_link' => admin_url( 'admin.php?page=fbm_application&id=123' ),
		);
	}
}
