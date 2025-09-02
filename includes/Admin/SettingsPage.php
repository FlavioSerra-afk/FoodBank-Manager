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
	 *
	 * @since 0.1.1
	 */
	public static function route(): void {
		if ( ! current_user_can( 'fb_manage_settings' ) && ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ), '', array( 'response' => 403 ) );
		}
		if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] ) {
			self::handle_post();
		}
	}

	/**
	 * Handle saving settings.
	 *
	 * @since 0.1.1
	 */
	private static function handle_post(): void {
		check_admin_referer( 'fbm_admin_action', '_fbm_nonce' );
		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'fb_manage_settings' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'foodbank-manager' ) );
		}
				$data = isset( $_POST['fbm_settings'] ) && is_array( $_POST['fbm_settings'] )
						? (array) wp_unslash( $_POST['fbm_settings'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized in Options::saveAll.
						: array();
		Options::saveAll( $data );
		add_settings_error( 'fbm-settings', 'fbm_saved', esc_html__( 'Settings saved.', 'foodbank-manager' ), 'updated' );
				$url = wp_get_referer();
				$url = $url ? $url : admin_url();
				wp_safe_redirect( esc_url_raw( $url ) );
		exit;
	}
}
