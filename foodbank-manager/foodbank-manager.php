<?php
/**
 * Plugin Name: FoodBank Manager
 * Plugin URI: https://pcclondon.uk/
 * Description: Secure management of Food Bank applications and attendance.
 * Version: 0.1.0
 * Author: Portuguese Community Centre London
 * License: GPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: foodbank-manager
 * Domain Path: /languages
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager;

use FoodBankManager\Core\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
} else {
	add_action(
		'admin_notices',
		function (): void {
			echo '<div class="notice notice-warning"><p>' . \esc_html__( 'FoodBank Manager dependencies not installed.', 'foodbank-manager' ) . '</p></div>';
		}
	);
}

/**
 * Activation hook.
 */
function activate(): void {
	Plugin::get_instance()->activate();
}
register_activation_hook( __FILE__, __NAMESPACE__ . '\\activate' );

/**
 * Deactivation hook.
 */
function deactivate(): void {
	Plugin::get_instance()->deactivate();
}
register_deactivation_hook( __FILE__, __NAMESPACE__ . '\\deactivate' );

Plugin::get_instance()->init();
