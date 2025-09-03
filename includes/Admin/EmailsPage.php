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
		if ( ! current_user_can( 'fb_manage_settings' ) && ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ), '', array( 'response' => 403 ) );
		}
		if ( isset( $_GET['preview'], $_GET['template'], $_GET['_fbm_nonce'] ) ) {
				check_admin_referer( 'fbm_admin_action', '_fbm_nonce' );
				$template = sanitize_key( (string) wp_unslash( $_GET['template'] ) );
				self::handle_preview( $template );
				return;
		}
			$method = strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ?? '' ) ) );
		if ( 'POST' === $method ) {
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
		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'fb_manage_settings' ) ) {
				wp_die( esc_html__( 'You do not have permission to perform this action.', 'foodbank-manager' ) );
		}
			$action = sanitize_key( (string) ( $_POST['fbm_email_action'] ?? '' ) );
		if ( 'send_test' === $action ) {
				$template = sanitize_key( (string) ( $_POST['test_template'] ?? 'applicant_confirmation' ) );
				$to       = sanitize_email( wp_unslash( (string) ( $_POST['test_email'] ?? '' ) ) );
				self::send_test( $template, $to );
				return;
		}
		$data      = array();
		$templates = filter_input( INPUT_POST, 'templates', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		if ( is_array( $templates ) ) {
			/**
			 * Raw templates data.
			 *
			 * @var array<string,array<string,string>> $raw
			 */
			$raw = wp_unslash( $templates );
			foreach ( $raw as $key => $tpl ) {
				$data[ sanitize_key( $key ) ] = array(
					'subject' => sanitize_text_field( $tpl['subject'] ?? '' ),
					'body'    => wp_kses_post( $tpl['body'] ?? '' ),
				);
			}
		}
		Templates::saveAll( $data );
			add_settings_error( 'fbm-emails', 'fbm_saved', esc_html__( 'Templates saved.', 'foodbank-manager' ), 'updated' );
			$url = add_query_arg( 'updated', 1, menu_page_url( 'fbm-emails', false ) );
			wp_safe_redirect( esc_url_raw( $url ) );
			exit;
	}

		/**
		 * Send a test email.
		 *
		 * @since 0.1.1
		 *
		 * @param string $template Template key.
		 * @param string $to       Destination email.
		 *
		 * @return void
		 */
	private static function send_test( string $template, string $to ): void {
		$user = wp_get_current_user();
		if ( ! $user instanceof WP_User ) {
				return;
		}
		if ( ! $to || ! is_email( $to ) ) {
				return;
		}
			$rendered = Templates::render( $template, self::sample_vars() );
			wp_mail( $to, $rendered['subject'], $rendered['body_html'], array( 'Content-Type: text/html; charset=UTF-8' ) );
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				\call_user_func( 'error_log', 'fbm_test_email ' . $rendered['subject'] . ' ' . md5( $rendered['body_html'] ) );
		}
			add_settings_error( 'fbm-emails', 'fbm_test', esc_html__( 'Test email sent.', 'foodbank-manager' ), 'updated' );
		$url = add_query_arg( 'updated', 1, menu_page_url( 'fbm-emails', false ) );
		wp_safe_redirect( esc_url_raw( $url ) );
		exit;
	}

	/**
	 * Preview an email template.
	 *
	 * @since 0.1.1
	 */
		/**
		 * Preview an email template.
		 *
		 * @since 0.1.1
		 *
		 * @param string $template Template key.
		 *
		 * @return void
		 */
	private static function handle_preview( string $template ): void {
			$rendered = Templates::render( $template, self::sample_vars() );
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
	private static function sample_vars(): array {
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
