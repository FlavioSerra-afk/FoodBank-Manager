<?php
/**
 * Email templates admin page.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FoodBankManager\Mail\Templates;

/**
 * Emails admin page.
 */
final class EmailsPage {
	/**
	 * Route the emails page.
	 *
	 * @return void
	 */
	public static function route(): void {
		if ( ! current_user_can( 'fb_manage_emails' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
			wp_die(
				esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ),
				'',
				array( 'response' => 403 )
			);
		}

		$templates = Templates::defaults();
		$current   = isset( $_GET['tpl'] ) ? sanitize_key( (string) $_GET['tpl'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		require FBM_PATH . 'templates/admin/emails.php';
	}
}
