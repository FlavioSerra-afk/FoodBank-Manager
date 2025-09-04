<?php
/**
 * Diagnostics admin page controller.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FoodBankManager\Auth\Roles;
use FoodBankManager\Core\Options;
use FoodBankManager\Admin\Menu;

/**
 * Diagnostics admin page.
 */
class DiagnosticsPage {
	/**
	 * Route the diagnostics page.
	 */
	public static function route(): void {
		if ( ! current_user_can( 'fb_manage_diagnostics' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ), '', array( 'response' => 403 ) );
		}

		$method = strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only
		if ( 'POST' !== $method ) {
			return;
		}

		$action = sanitize_key( wp_unslash( $_POST['fbm_action'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- validated in handlers
		if ( 'send_test_email' === $action ) {
			check_admin_referer( 'fbm_diagnostics_send_test_email', '_fbm_nonce' );
			self::send_test_email();
		} elseif ( 'repair_caps' === $action ) {
			check_admin_referer( 'fbm_diagnostics_repair_caps', '_fbm_nonce' );
			self::repair_caps();
		}
	}

		/**
		 * Render diagnostics template.
		 */
	public static function render(): void {
		if ( ! current_user_can( 'fb_manage_diagnostics' ) ) {
				wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ), '', array( 'response' => 403 ) );
		}
			/* @psalm-suppress UnresolvableInclude */
			require FBM_PATH . 'templates/admin/diagnostics.php';
	}

	/**
	 * Send a test email to the current user.
	 */
	private static function send_test_email(): void {
				$user = wp_get_current_user();
				$to   = (string) $user->user_email;

		$from_name  = (string) Options::get( 'emails.from_name' );
		$from_email = (string) Options::get( 'emails.from_email' );

		$from_filter = static function () use ( $from_email ): string {
			return $from_email;
		};
		$name_filter = static function () use ( $from_name ): string {
			return $from_name;
		};

		if ( is_email( $from_email ) ) {
			add_filter( 'wp_mail_from', $from_filter );
		}
		if ( '' !== $from_name ) {
			add_filter( 'wp_mail_from_name', $name_filter );
		}

				$sent = wp_mail(
					$to,
					__( 'FoodBank Manager test email', 'foodbank-manager' ),
					__( 'This is a test email from FoodBank Manager.', 'foodbank-manager' )
				);

		if ( is_email( $from_email ) ) {
			remove_filter( 'wp_mail_from', $from_filter );
		}
		if ( '' !== $from_name ) {
			remove_filter( 'wp_mail_from_name', $name_filter );
		}

		$notice      = $sent ? 'sent' : 'error';
				$url = add_query_arg( array( 'notice' => $notice ), menu_page_url( 'fbm_diagnostics', false ) );
		wp_safe_redirect( esc_url_raw( $url ), 303 );
		exit;
	}

	/**
	 * Repair roles and capabilities.
	 */
	private static function repair_caps(): void {
				Roles::install();
				Roles::ensure_admin_caps();
		if ( function_exists( 'add_menu_page' ) ) {
				Menu::register();
		}

				$url = add_query_arg( array( 'notice' => 'repaired' ), menu_page_url( 'fbm_diagnostics', false ) );
				wp_safe_redirect( esc_url_raw( $url ), 303 );
				exit;
	}
}
