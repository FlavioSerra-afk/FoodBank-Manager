<?php
/**
 * Forms presets admin page.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FoodBankManager\Forms\Presets;

/**
 * Forms page controller.
 */
final class FormsPage {
	private const CAP = 'fb_manage_forms';

	/**
	 * Route the forms page.
	 */
	public static function route(): void {
		if ( ! current_user_can( self::CAP ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom plugin cap.
			wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ), '', array( 'response' => 403 ) );
		}

		$presets = Presets::all();
		require FBM_PATH . 'templates/admin/forms.php';
	}
}
