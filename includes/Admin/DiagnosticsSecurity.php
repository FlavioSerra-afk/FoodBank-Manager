<?php
/**
 * Diagnostics security panel controller.
 *
 * @package FoodBankManager\Admin
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FBM\Security\ThrottleSettings;
use function add_action;
use function current_user_can;
use function get_editable_roles;
use function register_setting;

/**
 * Controller for Diagnostics → Security & Throttling panel.
 */
final class DiagnosticsSecurity {
	/** Boot settings registration. */
	public static function boot(): void {
		add_action(
			'admin_init',
			static function (): void {
				register_setting(
					'fbm_security',
					'fbm_throttle',
					array(
						'sanitize_callback' => 'fbm_throttle_sanitize',
						'type'              => 'array',
					)
				);
			}
		);
	}

	/**
	 * Render panel.
	 */
	public static function render_panel(): void {
		if ( ! current_user_can( 'fb_manage_diagnostics' ) ) {
			return;
		}
		$settings = ThrottleSettings::get();
		$roles    = get_editable_roles();
		require FBM_PATH . 'templates/admin/diagnostics-security.php';
	}
}
