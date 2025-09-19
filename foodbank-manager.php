<?php
/**
 * Plugin Name: FoodBank Manager
 * Description: Secure forms, encrypted storage, dashboards, and attendance tracking for food banks.
 * Author: Portuguese Community Centre London
 * Version: 1.0.10
 * Requires at least: 6.0
 * Requires PHP: 8.2
 * Text Domain: foodbank-manager
 * Domain Path: /languages
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FBM_FILE', __FILE__ );
define( 'FBM_PATH', plugin_dir_path( __FILE__ ) );
define( 'FBM_URL', plugin_dir_url( __FILE__ ) );

$fbm_autoload = FBM_PATH . 'vendor/autoload.php';
if ( is_readable( $fbm_autoload ) ) {
	require_once $fbm_autoload;
}

spl_autoload_register(
	static function ( string $fqcn ): void {
		if ( strpos( $fqcn, 'FoodBankManager\\' ) !== 0 ) {
			return;
		}

		$relative = substr( $fqcn, strlen( 'FoodBankManager\\' ) );
		$path     = str_replace( '\\', '/', $relative );
		$standard = FBM_PATH . 'includes/' . $path . '.php';

		if ( is_readable( $standard ) ) {
			require_once $standard;
			return;
		}

		$segments       = explode( '/', $path );
		$class          = array_pop( $segments );
		$dir            = FBM_PATH . 'includes/' . ( $segments ? implode( '/', $segments ) . '/' : '' );
		$pattern_hyphen = strtolower( preg_replace( '/([a-z0-9])([A-Z])/', '$1-$2', $class ) );
		$wp_hyphen      = $dir . 'class-' . $pattern_hyphen . '.php';

		if ( is_readable( $wp_hyphen ) ) {
			require_once $wp_hyphen;
			return;
		}

		$pattern_compact = strtolower( $class );
		$wp_compact      = $dir . 'class-' . $pattern_compact . '.php';

		if ( is_readable( $wp_compact ) ) {
			require_once $wp_compact;
		}
	}
);

if ( ! defined( 'FBM_VER' ) ) {
	define( 'FBM_VER', \FoodBankManager\Core\Plugin::VERSION );
}

add_action(
	'plugins_loaded',
	static function (): void {
		load_plugin_textdomain( 'foodbank-manager', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		\FoodBankManager\Core\Plugin::boot();
	}
);

register_activation_hook(
	__FILE__,
	static function (): void {
		\FoodBankManager\Core\Plugin::activate();
	}
);

register_deactivation_hook(
	__FILE__,
	static function (): void {
		\FoodBankManager\Core\Plugin::deactivate();
	}
);
