<?php
/**
 * Screen helper.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Core;

/**
 * Determine current admin screen context.
 */
final class Screen {
	/**
	 * Check if current screen belongs to FoodBank Manager.
	 *
	 * @return bool
	 */
	public static function is_fbm_screen(): bool {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		$id     = $screen ? (string) $screen->id : '';

		if ( 'toplevel_page_fbm' === $id ) {
			return true;
		}
		if ( str_starts_with( $id, 'foodbank_page_fbm_' ) ) {
			return true;
		}
		// Fallback for early hooks.
		$page = isset( $_GET['page'] ) ? sanitize_key( (string) $_GET['page'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return 'fbm' === $page || str_starts_with( $page, 'fbm_' );
	}
}
