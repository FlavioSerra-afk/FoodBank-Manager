<?php
// phpcs:ignoreFile
/**
 * Design & Theme admin page.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FoodBankManager\Core\Options;

/**
 * Theme admin page.
 */
class ThemePage {

	/**
	 * Route the theme page.
	 *
	 * @since 0.1.1
	 */
	public static function route(): void {
		if ( ! current_user_can( 'fb_manage_settings' ) && ! current_user_can( 'manage_options' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
			wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ), '', array( 'response' => 403 ) );
		}
		if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] ) {
			self::handle_post();
		}
	}

	/**
	 * Handle theme actions.
	 *
	 * @since 0.1.1
	 */
	private static function handle_post(): void {
		check_admin_referer( 'fbm_admin_action', '_fbm_nonce' );
		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'fb_manage_settings' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
				wp_die( esc_html__( 'You do not have permission to perform this action.', 'foodbank-manager' ) );
		}
		if ( isset( $_POST['fbm_theme_export'] ) ) {
				$theme = Options::get( 'theme', array() );
				nocache_headers();
				header( 'Content-Type: application/json' );
				header( 'Content-Disposition: attachment; filename=fbm-theme.json' );
				echo wp_json_encode( $theme );
				exit;
		}
		if ( isset( $_POST['fbm_theme_import'] ) ) {
				$file = isset( $_FILES['theme_file']['tmp_name'] )
						? sanitize_text_field( wp_unslash( $_FILES['theme_file']['tmp_name'] ) )
						: '';
			if ( $file && is_readable( $file ) ) {
						// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- reading uploaded JSON.
						$json = file_get_contents( $file );
						$data = json_decode( (string) $json, true );
				if ( is_array( $data ) ) {
					$settings          = Options::all();
					$settings['theme'] = $data;
					Options::saveAll( $settings );
					add_settings_error( 'fbm-theme', 'fbm_theme_imported', esc_html__( 'Theme imported.', 'foodbank-manager' ), 'updated' );
				}
			}
				$url = wp_get_referer();
				$url = $url ? $url : admin_url();
				wp_safe_redirect( esc_url_raw( $url ) );
				exit;
		}
				$data              = isset( $_POST['fbm_theme'] ) && is_array( $_POST['fbm_theme'] )
						? (array) wp_unslash( $_POST['fbm_theme'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized in Options::saveAll.
						: array();
				$settings          = Options::all();
				$settings['theme'] = $data;
				Options::saveAll( $settings );
				add_settings_error( 'fbm-theme', 'fbm_theme_saved', esc_html__( 'Theme saved.', 'foodbank-manager' ), 'updated' );
				$url = wp_get_referer();
				$url = $url ? $url : admin_url();
				wp_safe_redirect( esc_url_raw( $url ) );
				exit;
	}
}
