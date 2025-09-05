<?php // phpcs:ignoreFile
/**
 * Plugin Name: FoodBank Manager
 * Description: Secure forms, encrypted storage, dashboards, and attendance tracking for food banks.
 * Version: 1.2.8
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

// Prefer Composer if present.
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
        require __DIR__ . '/vendor/autoload.php';
}

// Fallback PSR-4 autoloader for both namespaces: FBM\ and FoodBankManager\.
spl_autoload_register(
        static function ( $class ): void {
                if ( strncmp( $class, 'FBM\\', 4 ) !== 0 && strncmp( $class, 'FoodBankManager\\', 17 ) !== 0 ) {
                        return;
                }
                $rel  = preg_replace( '#^(FBM\\\\|FoodBankManager\\\\)#', '', $class );
                $path = __DIR__ . '/includes/' . str_replace( '\\', '/', $rel ) . '.php';
                if ( is_file( $path ) ) {
                        require $path;
                }
        }
);

// Namespace bridges (temporary): map old <-> new so both references work.
if ( ! class_exists( 'FBM\\Core\\Retention' ) && class_exists( 'FoodBankManager\\Core\\Retention' ) ) {
        class_alias( 'FoodBankManager\\Core\\Retention', 'FBM\\Core\\Retention' );
}
if ( ! class_exists( 'FBM\\Exports\\SarExporter' ) && class_exists( 'FoodBankManager\\Exports\\SarExporter' ) ) {
        class_alias( 'FoodBankManager\\Exports\\SarExporter', 'FBM\\Exports\\SarExporter' );
}
if ( ! class_exists( 'FBM\\Mail\\LogRepo' ) && class_exists( 'FoodBankManager\\Mail\\LogRepo' ) ) {
        class_alias( 'FoodBankManager\\Mail\\LogRepo', 'FBM\\Mail\\LogRepo' );
}

// If our core class still isn't available, show a safe admin notice and bail (no fatals).
add_action(
        'admin_notices',
        static function () {
                if ( ! class_exists( \FoodBankManager\Core\Plugin::class ) ) {
                        $s = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
                        if ( ! $s || ( strpos( $s->id, 'toplevel_page_fbm' ) !== 0 && strpos( $s->id, 'foodbank_page_fbm_' ) !== 0 ) ) {
                                return;
                        }
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
                \FoodBankManager\Core\Plugin::boot();
        }
);

// Activation/Deactivation hooks.
register_activation_hook( FBM_FILE, [ \FoodBankManager\Core\Plugin::class, 'activate' ] );
register_activation_hook( __FILE__, static function () {
        if ( class_exists( \FBM\Auth\Capabilities::class ) ) {
                \FBM\Auth\Capabilities::ensure_for_admin();
        }
} );

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
