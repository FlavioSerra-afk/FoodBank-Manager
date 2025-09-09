<?php
/**
 * Asset loader.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Core;

use FoodBankManager\Core\Screen;
use FoodBankManager\UI\Theme;
use function add_action;
use function get_post;
use function has_shortcode;
use function is_singular;
use function wp_enqueue_style;
use function wp_register_style;
use function wp_add_inline_style;
use function esc_html;
use function wp_enqueue_script;
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
					add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_front' ) );
					add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin' ), 10, 0 );
					add_action( 'admin_head', array( self::class, 'print_admin_head' ) );
	}

		/**
		 * Enqueue frontend assets if needed.
		 *
		 * @return void
		 */
	public function enqueue_front(): void {
		if ( ! function_exists( 'has_shortcode' ) || ! is_singular() ) {
				return;
		}
			$post = get_post();
		if ( ! $post ) {
				return;
		}
			$content  = (string) $post->post_content;
			$has_form = has_shortcode( $content, 'fbm_form' );
			$has_dash = has_shortcode( $content, 'fbm_dashboard' );
		if ( ! $has_form && ! $has_dash ) {
				return;
		}

				$front = Theme::front();
		if ( ! empty( $front['enabled'] ) ) {
						wp_register_style( 'fbm-frontend-theme', false, array(), Plugin::VERSION );
						wp_add_inline_style( 'fbm-frontend-theme', Theme::css_vars( $front, ':root' ) );
						wp_enqueue_style( 'fbm-frontend-theme' );
		}

		if ( $has_dash ) {
						wp_register_style( 'fbm-frontend-dashboard', FBM_URL . 'assets/css/frontend-dashboard.css', array(), Plugin::VERSION );
						wp_enqueue_style( 'fbm-frontend-dashboard' );
		}
	}

		/**
		 * Enqueue admin assets when on plugin screens.
		 *
		 * @return void
		 */
	public function enqueue_admin(): void {
		if ( ! Screen::is_fbm_screen() ) {
										return;
		}
										$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
												wp_register_style( 'fbm-admin', FBM_URL . 'assets/css/admin.css', array(), Plugin::VERSION );
												wp_enqueue_style( 'fbm-admin' );

		if ( $screen && 'foodbank_page_fbm_attendance' === $screen->id && current_user_can( 'fb_manage_attendance' ) ) {
				wp_enqueue_script( 'fbm-qrcode', FBM_URL . 'assets/js/qrcode.min.js', array(), Plugin::VERSION, true );
		}
		if ( $screen && 'foodbank_page_fbm_form_builder' === $screen->id && current_user_can( 'fbm_manage_forms' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
			wp_enqueue_script( 'fbm-form-builder', FBM_URL . 'assets/js/fbm-form-builder.js', array(), Plugin::VERSION, true );
		}
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

												$css = Theme::css_vars( Theme::admin(), '.fbm-admin' );
												echo '<style id="fbm-css-vars">' . esc_html( $css ) . '</style>';
												$done = true;
	}
}
