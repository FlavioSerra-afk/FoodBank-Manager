<?php
/**
 * Unified Food Bank admin menu.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use function __;
use function add_action;
use function add_menu_page;
use function current_user_can;
use function esc_html__;
use function wp_die;

/**
 * Registers the unified Food Bank top-level menu.
 */
final class Menu {
	public const SLUG = 'fbm-admin';

		/**
		 * Attach menu registration to WordPress.
		 */
	public static function register(): void {
			add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
	}

		/**
		 * Register the top-level Food Bank menu.
		 */
	public static function register_menu(): void {
			add_menu_page(
				__( 'Food Bank', 'foodbank-manager' ),
				__( 'Food Bank', 'foodbank-manager' ),
				'fbm_manage', // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability registered on activation.
				self::SLUG,
				array( __CLASS__, 'render' ),
				'dashicons-groups'
			);
	}

		/**
		 * Render a lightweight overview for the top-level menu.
		 */
	public static function render(): void {
		if ( ! current_user_can( 'fbm_manage' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability registered on activation.
				wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ) );
		}

			echo '<div class="wrap">';
			echo '<h1 class="wp-heading-inline">' . esc_html__( 'Food Bank', 'foodbank-manager' ) . '</h1>';
			echo '<p>' . esc_html__( 'Use the Food Bank menu to manage members, reports, diagnostics, and shortcode settings.', 'foodbank-manager' ) . '</p>';
			echo '</div>';
	}
}
