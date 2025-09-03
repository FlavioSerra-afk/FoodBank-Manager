<?php // phpcs:ignoreFile
/**
 * Plugin Name: FoodBank Manager
 * Description: Secure forms, encrypted storage, dashboards, and attendance tracking for food banks.
 * Version: 1.0.3
 * Requires at least: 6.0
 * Requires PHP: 8.1
 * Author: Portuguese Community Centre London
 * Text Domain: foodbank-manager
 * Domain Path: /languages
 *
 * @package FoodBankManager
 */

declare( strict_types=1 );

namespace FoodBankManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Paths/URLs.
define( 'FBM_FILE', __FILE__ );
define( 'FBM_PATH', plugin_dir_path( __FILE__ ) );
define( 'FBM_URL', plugin_dir_url( __FILE__ ) );

// Try Composer autoloader first.
$autoload = FBM_PATH . 'vendor/autoload.php';
if ( file_exists( $autoload ) ) {
	require_once $autoload;
} else {
	// Lightweight PSR-4 autoloader for our namespace so activation never fatals.
	spl_autoload_register(
		static function ( $class_name ): void {
			$prefix = __NAMESPACE__ . '\\';
			if ( strpos( $class_name, $prefix ) !== 0 ) {
				return;
			}
			$rel  = substr( $class_name, strlen( $prefix ) );
			$rel  = str_replace( '\\', DIRECTORY_SEPARATOR, $rel );
			$file = FBM_PATH . 'includes/' . $rel . '.php';
			if ( is_readable( $file ) ) {
				require $file;
			}
		}
	);
}

// If our core class still isn't available, show a safe admin notice and bail (no fatals).
add_action(
	'admin_notices',
	static function () {
		if ( ! class_exists( \FoodBankManager\Core\Plugin::class ) ) {
						$notice = esc_html__( 'Autoloader not found.', 'foodbank-manager' ) . ' '
								. esc_html__( 'Please install using the Release ZIP from GitHub.', 'foodbank-manager' ) . ' '
								. esc_html__( 'The ZIP includes the vendor/ folder.', 'foodbank-manager' ) . ' '
								. esc_html__( 'Alternatively, run composer install before activation.', 'foodbank-manager' );
				printf(
					'<div class="notice notice-error"><p><strong>FoodBank Manager:</strong> %s</p></div>',
					esc_html( $notice )
				);
		}
	}
);
if ( ! class_exists( \FoodBankManager\Core\Plugin::class ) ) {
	return;
}

// Boot plugin after all plugins load.
add_action(
	'plugins_loaded',
	static function (): void {
		$plugin = new \FoodBankManager\Core\Plugin();
		if ( method_exists( $plugin, 'boot' ) ) {
			$plugin->boot();
		}
	}
);

// Activation/Deactivation hooks.
register_activation_hook( FBM_FILE, [ \FoodBankManager\Core\Plugin::class, 'activate' ] );

if ( ! function_exists( __NAMESPACE__ . '\\pcc_fbm_deactivate' ) ) {
		/**
		 * Wrapper for plugin deactivation.
		 */
	function pcc_fbm_deactivate(): void {
			$plugin = new \FoodBankManager\Core\Plugin();
		if ( method_exists( $plugin, 'deactivate' ) ) {
				$plugin->deactivate();
		}
	}
}
register_deactivation_hook( __FILE__, __NAMESPACE__ . '\\pcc_fbm_deactivate' );
