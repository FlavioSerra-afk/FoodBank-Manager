<?php
/**
 * Settings admin page.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FoodBankManager\Core\Options;

/**
 * Settings admin page.
 */
class SettingsPage {
		/**
		 * Route the settings page.
		 */
	public static function route(): void {
		if ( ! current_user_can( 'fb_manage_settings' ) ) {
				wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ), '', array( 'response' => 403 ) );
		}

				$method = strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only server var
		if ( 'POST' !== $method ) {
				return;
		}

				$action = sanitize_key( wp_unslash( $_POST['fbm_action'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- validated in handler
		if ( 'branding_save' === $action ) {
				self::handle_branding();
		} elseif ( 'email_save' === $action ) {
				self::handle_email();
		}
	}

		/**
		 * Handle branding settings save.
		 */
	private static function handle_branding(): void {
			check_admin_referer( 'fbm_branding_save', '_fbm_nonce' );
		if ( ! current_user_can( 'fb_manage_settings' ) ) {
				wp_die( esc_html__( 'You do not have permission to perform this action.', 'foodbank-manager' ) );
		}

				$data = isset( $_POST['branding'] ) && is_array( $_POST['branding'] )
						? wp_unslash( $_POST['branding'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
						: array();
			Options::update( array( 'branding' => $data ) );

			$url = add_query_arg(
				array(
					'notice' => 'saved',
					'tab'    => 'branding',
				),
				menu_page_url( 'fbm_settings', false )
			);
			wp_safe_redirect( esc_url_raw( $url ), 303 );
			exit;
	}

		/**
		 * Handle email settings save.
		 */
	private static function handle_email(): void {
			check_admin_referer( 'fbm_email_save', '_fbm_nonce' );
		if ( ! current_user_can( 'fb_manage_settings' ) ) {
				wp_die( esc_html__( 'You do not have permission to perform this action.', 'foodbank-manager' ) );
		}

				$data = isset( $_POST['emails'] ) && is_array( $_POST['emails'] )
						? wp_unslash( $_POST['emails'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
						: array();
			Options::update( array( 'emails' => $data ) );

			$url = add_query_arg(
				array(
					'notice' => 'saved',
					'tab'    => 'email',
				),
				menu_page_url( 'fbm_settings', false )
			);
			wp_safe_redirect( esc_url_raw( $url ), 303 );
			exit;
	}
}
