<?php
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
		if ( ! current_user_can( 'fb_manage_theme' ) && ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ), '', array( 'response' => 403 ) );
		}
			$method = strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ?? '' ) ) );
		if ( 'POST' === $method ) {
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
		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'fb_manage_theme' ) ) {
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
				$file = isset( $_FILES['theme_file']['tmp_name'] ) ? sanitize_text_field( wp_unslash( $_FILES['theme_file']['tmp_name'] ) ) : '';
			if ( $file && is_readable( $file ) ) {
					$data = wp_json_file_decode( $file, array( 'associative' => true ) );
				if ( is_array( $data ) ) {
					$settings          = Options::all();
					$settings['theme'] = $data;
					Options::saveAll( $settings );
					add_settings_error( 'fbm-theme', 'fbm_theme_imported', esc_html__( 'Theme imported.', 'foodbank-manager' ), 'updated' );
				}
			}
				$url = add_query_arg( 'updated', 1, menu_page_url( 'fbm-theme', false ) );
				wp_safe_redirect( esc_url_raw( $url ) );
				exit;
		}
				$data              = isset( $_POST['fbm_theme'] ) && is_array( $_POST['fbm_theme'] )
						? map_deep( wp_unslash( $_POST['fbm_theme'] ), 'sanitize_text_field' )
						: array();
				$settings          = Options::all();
				$settings['theme'] = $data;
			Options::saveAll( $settings );
			add_settings_error( 'fbm-theme', 'fbm_theme_saved', esc_html__( 'Theme saved.', 'foodbank-manager' ), 'updated' );
			$url = add_query_arg( 'updated', 1, menu_page_url( 'fbm-theme', false ) );
			wp_safe_redirect( esc_url_raw( $url ) );
			exit;
	}
}
