<?php
// phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Core;

use FoodBankManager\Db\Migrations;
use FoodBankManager\Auth\Roles;
use FoodBankManager\Http\FormSubmitController;
use FoodBankManager\Http\DashboardExportController;
use FoodBankManager\Http\DiagnosticsController;
use FoodBankManager\Core\Options;
use FBM\Core\Retention;
use FoodBankManager\Admin\ShortcodesPage;
use FoodBankManager\Core\Screen;

final class Plugin {

    public const VERSION = '1.2.16';

        private static ?Plugin $instance = null;
        private static bool $booted = false;

        /**
         * Get singleton instance.
         */
        public static function get_instance(): self {
                return self::$instance ??= new self();
        }

        /**
         * Register hooks and assets.
         */
        public function init(): void {
                ( new Hooks() )->register();
                ( new Assets() )->register();
        }

        /**
         * Boot the plugin.
         */
        public static function boot(): void {
                if ( self::$booted ) {
                        return;
                }
               self::$booted = true;

               if ( is_admin() ) {
                       \FoodBankManager\Admin\Notices::maybe_handle_caps_notice_dismiss();
                       if ( class_exists( \FBM\Auth\Capabilities::class ) ) {
                               \FBM\Auth\Capabilities::ensure_for_admin();
                       }
               }

               \FBM\Shortcodes\Shortcodes::register();
               Options::boot();
               Retention::init();
                add_action(
                        'init',
                        static function (): void {
                                load_plugin_textdomain( 'foodbank-manager', false, dirname( plugin_basename( \FBM_FILE ) ) . '/languages' );
                        }
                );

                \FoodBankManager\Auth\CapabilitiesResolver::boot();

                if ( is_admin() ) {
                        \FoodBankManager\Admin\Notices::boot();
                        add_action( 'admin_menu', [\FoodBankManager\Admin\Menu::class, 'register'], 9 );
                        // Theme CSS enqueued via Core\Assets.
                }

                add_action( 'admin_post_nopriv_fbm_submit', array( FormSubmitController::class, 'handle' ) );
                add_action( 'admin_post_fbm_submit', array( FormSubmitController::class, 'handle' ) );
                add_action( 'admin_post_fbm_dash_export', array( DashboardExportController::class, 'handle' ) );
                add_action( 'admin_post_fbm_diag_mail_test', array( DiagnosticsController::class, 'mail_test' ) );

                self::get_instance()->init();
        }

        /** Activate plugin. */
        public static function activate(): void {
                ( new Migrations() )->maybe_migrate();
                Roles::install();
                Retention::schedule();
        }

        /** Deactivate plugin. */
        public function deactivate(): void {
                // Placeholder for future deactivation routines.
        }
}
