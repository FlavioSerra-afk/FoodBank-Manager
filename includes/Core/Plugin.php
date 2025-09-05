<?php
// phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Core;

use FoodBankManager\Db\Migrations;
use FoodBankManager\Auth\Roles;
use FoodBankManager\Http\FormSubmitController;
use FoodBankManager\Http\DashboardExportController;
use FoodBankManager\Core\Options;
use FBM\Core\Retention;
use FoodBankManager\Admin\ShortcodesPage;
use FoodBankManager\Core\Screen;

final class Plugin {

    public const FBM_VERSION = '1.2.7';

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
                        add_action( 'admin_init', [\FoodBankManager\Auth\Roles::class, 'ensure_admin_caps'], 5 );
                        \FoodBankManager\Admin\Notices::boot();
                        add_action( 'admin_menu', [\FoodBankManager\Admin\Menu::class, 'register'], 9 );
                        add_action( 'load-foodbank_page_fbm_database', [\FoodBankManager\Admin\DatabasePage::class, 'route'] );
                        add_action( 'load-foodbank_page_fbm_attendance', [\FoodBankManager\Admin\AttendancePage::class, 'route'] );
                        add_action( 'load-foodbank_page_fbm_permissions', [\FoodBankManager\Admin\PermissionsPage::class, 'route'] );
                        add_action( 'load-foodbank_page_fbm_theme', [\FoodBankManager\Admin\ThemePage::class, 'route'] );
                        add_action( 'load-foodbank_page_fbm_emails', [\FoodBankManager\Admin\EmailsPage::class, 'route'] );
                        add_action( 'load-foodbank_page_fbm_diagnostics', [\FoodBankManager\Admin\DiagnosticsPage::class, 'route'] );
                        add_action( 'load-foodbank_page_fbm_shortcodes', [ShortcodesPage::class, 'route'] );
                        // Theme CSS enqueued via Core\Assets.
                }

                add_action( 'admin_post_nopriv_fbm_submit', array( FormSubmitController::class, 'handle' ) );
                add_action( 'admin_post_fbm_submit', array( FormSubmitController::class, 'handle' ) );
                add_action( 'admin_post_fbm_dash_export', array( DashboardExportController::class, 'handle' ) );

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
