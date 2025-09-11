<?php // phpcs:ignoreFile
/**
 * Asset loader.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Core;

use FoodBankManager\UI\Theme;
use function add_action;
use function add_filter;
use function wp_enqueue_style;
use function wp_register_style;
use function wp_add_inline_style;
use function esc_html;
use function wp_enqueue_script;
use function wp_localize_script;
use function admin_url;
use function wp_create_nonce;
use function current_user_can;
use function get_current_screen;

/**
 * Manages script and style loading.
 */
class Assets {
		/**
		 * Register hooks.
		 *
		 * @return void
		 */
	public function register(): void {
									add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin' ), 10 );
									add_action( 'admin_head', array( self::class, 'print_admin_head' ) );
									$theme = Theme::get();
		if ( ! empty( $theme['apply_admin_chrome'] ) ) {
										add_filter( 'admin_body_class', array( Theme::class, 'admin_body_class' ) );
		}
		if ( ! is_admin() && ! empty( $theme['apply_front_menus'] ) ) {
				add_filter( 'body_class', array( Theme::class, 'body_class' ) );
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_front_menus' ) );
		}
	}

		/**
		 * Enqueue admin assets when on plugin screens.
		 *
		 * @return void
		 */
	public function enqueue_admin(): void {
							$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
							$id     = $screen ? (string) $screen->id : '';
			$theme                  = Theme::get();
		if ( ! empty( $theme['apply_admin_chrome'] ) ) {
						wp_register_style( 'fbm-menus', FBM_URL . 'assets/css/menus.css', array(), Plugin::VERSION );
						wp_enqueue_style( 'fbm-menus' );
		}
		if ( 'toplevel_page_fbm' !== $id && ! str_starts_with( $id, 'foodbank_page_fbm_' ) ) {
						return;
		}
		wp_register_style( 'fbm-admin', FBM_URL . 'assets/css/admin.css', array(), Plugin::VERSION );
		wp_enqueue_style( 'fbm-admin' );
		wp_register_style( 'fbm-admin-tables', FBM_URL . 'assets/css/admin-tables.css', array(), Plugin::VERSION );
		wp_enqueue_style( 'fbm-admin-tables' );

                if ( $screen && 'foodbank_page_fbm_attendance' === $screen->id && current_user_can( 'fb_manage_attendance' ) ) {
                        wp_enqueue_script( 'fbm-qrcode', FBM_URL . 'assets/js/qrcode.min.js', array(), Plugin::VERSION, true );
                }
                if ( $screen && 'foodbank_page_fbm_form_builder' === $screen->id && current_user_can( 'fbm_manage_forms' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
                        wp_enqueue_script( 'fbm-form-builder', FBM_URL . 'assets/js/fbm-form-builder.js', array(), Plugin::VERSION, true );
                }
                if ( $screen && 'foodbank_page_fbm_diagnostics' === $screen->id && current_user_can( 'fb_manage_diagnostics' ) ) {
                        wp_enqueue_script( 'fbm-admin-diagnostics', FBM_URL . 'assets/js/admin-diagnostics.js', array(), Plugin::VERSION, true );
                }
                if ( $screen && 'foodbank_page_fbm_permissions' === $screen->id && current_user_can( 'fb_manage_permissions' ) ) {
                        wp_enqueue_script( 'fbm-admin-permissions', FBM_URL . 'assets/js/admin-permissions.js', array(), Plugin::VERSION, true );
                        wp_localize_script( 'fbm-admin-permissions', 'fbmPerms', array(
                                'url'   => admin_url( 'admin-post.php' ),
                                'nonce' => wp_create_nonce( 'fbm_perms_role_toggle' ),
                        ) );
                }
                if ( $screen && 'foodbank_page_fbm_shortcodes' === $screen->id && current_user_can( 'fb_manage_forms' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
                        wp_enqueue_script( 'fbm-admin-shortcodes', FBM_URL . 'assets/js/admin-shortcodes.js', array(), Plugin::VERSION, true );
                }
        }

		/**
		 * Legacy front-end enqueue for tests.
		 *
		 * @deprecated
		 */
	public function enqueue_front(): void {
			$content = (string) ( $GLOBALS['fbm_post_content'] ?? '' );
		if ( str_contains( $content, '[fbm_dashboard]' ) ) {
				$GLOBALS['fbm_styles']['fbm-frontend-dashboard'] = true;
		}
	}

		/**
		 * Enqueue front-end menu styles when enabled.
		 */
	public function enqueue_front_menus(): void {
			$theme = Theme::get();
		if ( empty( $theme['apply_front_menus'] ) ) {
				return;
		}
				wp_register_style( 'fbm-menus', FBM_URL . 'assets/css/menus.css', array(), Plugin::VERSION );
				wp_enqueue_style( 'fbm-menus' );
	}

				/**
				 * Print deterministic admin CSS variables.
				 *
				 * @return void
				 */
	public static function print_admin_head(): void {
			static $done = false;
		if ( $done ) {
				return;
		}
			$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
			$id     = $screen ? (string) $screen->id : '';
		if ( 'toplevel_page_fbm' !== $id && ! str_starts_with( $id, 'foodbank_page_fbm_' ) ) {
				return;
		}

			$css = Theme::css_vars( Theme::admin(), '.fbm-admin' ) . Theme::glass_support_css();
			echo '<style id="fbm-css-vars">' . esc_html( $css ) . '</style>';
			$done = true;
	}
}
