<?php // phpcs:ignoreFile
/**
 * Plugin Name: FoodBank Manager
 * Description: Secure forms, encrypted storage, dashboards, and attendance tracking for food banks.
 * Version: 1.4.0-rc.7.6
 * Requires at least: 6.0
 * Requires PHP: 8.1
 * Author: Portuguese Community Centre London
 * Text Domain: foodbank-manager
 * Domain Path: /languages
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package FoodBankManager
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    return;
}

define( 'FBM_FILE', __FILE__ );
define( 'FBM_PATH', plugin_dir_path( __FILE__ ) );
define( 'FBM_URL', plugin_dir_url( __FILE__ ) );

$fbm_autoload = FBM_PATH . 'vendor/autoload.php';
if ( is_readable( $fbm_autoload ) ) {
    require_once $fbm_autoload;
}

require_once FBM_PATH . 'includes/Core/Headers.php';

/** Robust PSR-4 fallback for both namespaces */
spl_autoload_register(
    static function ( $class ): void {
        if ( strpos( $class, 'FBM\\' ) !== 0 && strpos( $class, 'FoodBankManager\\' ) !== 0 ) {
            return;
        }
        $base = FBM_PATH . 'includes/';
        $rel  = str_replace( [ 'FBM\\', 'FoodBankManager\\' ], '', $class );
        $file = $base . str_replace( '\\', '/', $rel ) . '.php';
        if ( is_readable( $file ) ) {
            require_once $file;
        }
    }
);

/** Namespace bridging (whichever exists first becomes canonical) */
if ( class_exists( 'FBM\\Core\\Plugin' ) && ! class_exists( 'FoodBankManager\\Core\\Plugin' ) ) {
    class_alias( 'FBM\\Core\\Plugin', 'FoodBankManager\\Core\\Plugin' );
} elseif ( class_exists( 'FoodBankManager\\Core\\Plugin' ) && ! class_exists( 'FBM\\Core\\Plugin' ) ) {
    class_alias( 'FoodBankManager\\Core\\Plugin', 'FBM\\Core\\Plugin' );
}

add_action(
    'init',
    static function (): void {
        load_plugin_textdomain(
            'foodbank-manager',
            false,
            dirname( plugin_basename( __FILE__ ) ) . '/languages'
        );
    }
);

/** Boot watchdog flag each admin request */
add_action(
    'plugins_loaded',
    static function (): void {
        if ( class_exists( '\FBM\Core\Plugin' ) ) {
            \FBM\Core\Plugin::boot();
            set_transient( 'fbm_boot_ok', time(), defined( 'MINUTE_IN_SECONDS' ) ? MINUTE_IN_SECONDS : 60 );
            do_action( 'fbm_booted' );
        }
    },
    0
);

add_action('admin_menu', static function () {
    // Only admins or FBM dashboard managers can ever see the parent
    if (!current_user_can('manage_options') && !current_user_can('fb_manage_dashboard')) {
        return;
    }

    // If the real menu already registered this request, do not add the fallback
    if (did_action('fbm_menu_registered')) {
        return;
    }

    // If core boot has run, do not add the fallback
    if (did_action('fbm_booted')) {
        return;
    }

    // If another plugin (or earlier run) already added our parent slug, do not add again
    global $menu;
    $parent_exists = false;
    if (is_array($menu)) {
        foreach ($menu as $item) {
            // index 2 is the slug in $menu entries
            if (isset($item[2]) && $item[2] === 'fbm') { $parent_exists = true; break; }
        }
    }
    if ($parent_exists) {
        return;
    }

    // Choose capability (fallback only affects parent)
    $root_cap = current_user_can('fb_manage_dashboard') ? 'fb_manage_dashboard' : 'manage_options';

    add_menu_page(
        __('FoodBank', 'foodbank-manager'),
        __('FoodBank', 'foodbank-manager'),
        $root_cap,
        'fbm',
        static function () {
            $diag  = admin_url('admin.php?page=fbm_diagnostics');
            echo '<div class="wrap"><h1>'.esc_html__('FoodBank Manager', 'foodbank-manager').'</h1>';
            echo '<p>'.esc_html__('Plugin bootstrap has not completed yet on this request. You can still reach Diagnostics to repair capabilities or run health checks.', 'foodbank-manager').'</p>';
            echo '<p><a class="button button-primary" href="'.esc_url($diag).'">'.esc_html__('Open Diagnostics', 'foodbank-manager').'</a></p></div>';
        },
        'dashicons-groups',
        58
    );
}, 9);

/** Emergency notice if boot didn’t run on this request (admins only) */
add_action('admin_notices', static function () {
    if (!is_admin() || !current_user_can('manage_options')) return;
    // If booted, don’t show the emergency notice
    if (did_action('fbm_booted')) return;

    $diag = admin_url('admin.php?page=fbm_diagnostics');
    echo '<div class="notice notice-warning"><p>'.
         esc_html__('FoodBank Manager is installed but did not complete bootstrap on this request.', 'foodbank-manager')
         .' <a href="'.esc_url($diag).'">'.esc_html__('Open Diagnostics → Repair caps', 'foodbank-manager').'</a>'.
         '</p></div>';
}, 1);

// Activation/Deactivation hooks.
register_activation_hook( FBM_FILE, [ \FBM\Core\Plugin::class, 'activate' ] ); // @phpstan-ignore-line
register_activation_hook(
    __FILE__,
    static function (): void {
        if ( class_exists( '\FBM\Auth\Capabilities' ) ) {
            \FBM\Auth\Capabilities::ensure_for_admin();
        }
    }
);
register_activation_hook(
    __FILE__,
    static function (): void {
        if ( class_exists( '\FBM\Core\Install' ) ) {
            \FBM\Core\Install::onActivate();
        }
    }
);


if ( ! function_exists( 'fbm_deactivate' ) ) {
    /**
     * Wrapper for plugin deactivation.
     */
    function fbm_deactivate(): void {
        if ( ! class_exists( '\FBM\Core\Plugin' ) ) {
            return;
        }
        $plugin = new \FBM\Core\Plugin();
        if ( method_exists( $plugin, 'deactivate' ) ) {
            $plugin->deactivate();
        }
    }
}
register_deactivation_hook( __FILE__, 'fbm_deactivate' );

