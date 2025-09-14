<?php
// phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Core;

use FoodBankManager\Db\Migrations;
use FoodBankManager\Auth\Roles;
use FoodBankManager\Http\FormSubmitController;
use FoodBankManager\Http\DashboardExportController;
use FoodBankManager\Http\DiagnosticsController;
use FoodBankManager\Admin\DiagnosticsPdf;
use FoodBankManager\Admin\DiagnosticsReport;
use FBM\Http\ExportJobsController;
use FBM\Core\Jobs\JobsWorker;
use FoodBankManager\Core\Options;
use FBM\Core\Retention;
use FoodBankManager\Core\Cron;
use FoodBankManager\CLI\Commands;
use FoodBankManager\Admin\ShortcodesPage;
use FoodBankManager\Core\Screen;

final class Plugin {

    public $1.8.0$2 // x-release-please-version
    public const VERSION = self::FBM_VER;
    private const OPTION_VERSION = 'fbm_version';

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

                self::maybe_upgrade();
                self::maybe_register_cli();

               if ( is_admin() ) {
                       \FoodBankManager\Admin\Notices::maybe_handle_caps_notice_dismiss();
                       if ( class_exists( \FBM\Auth\Capabilities::class ) ) {
                               \FBM\Auth\Capabilities::ensure_for_admin();
                       }
                        \FoodBankManager\Admin\ThemePage::boot();
                        \FoodBankManager\Admin\PermissionsPage::boot();
                        \FoodBankManager\Admin\DiagnosticsSecurity::boot();
               }

               \FBM\Shortcodes\Shortcodes::register();
               Options::boot();
               Retention::init();
               Cron::init();
               Cron::maybe_schedule_retention();
               \FBM\Forms\FormCpt::register();
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
                add_action( 'wp_ajax_fbm_mail_test', array( DiagnosticsController::class, 'ajax_mail_test' ) ); // @phpstan-ignore-line
                add_action( 'wp_ajax_fbm_mail_replay', array( \FBM\Admin\MailReplay::class, 'handle' ) ); // @phpstan-ignore-line
                add_action( 'admin_post_fbm_diag_mail_retry', array( DiagnosticsController::class, 'mail_retry' ) );
                add_action( 'admin_post_fbm_mail_resend', array( DiagnosticsController::class, 'mail_resend' ) );
                add_action( 'admin_post_' . DiagnosticsPdf::ACTION_PREVIEW, array( DiagnosticsPdf::class, 'preview' ) );
                add_action( 'admin_post_' . DiagnosticsReport::ACTION, array( DiagnosticsReport::class, 'download' ) );
                add_action( 'admin_post_fbm_export_queue', array( ExportJobsController::class, 'queue' ) );
                add_action( 'admin_post_fbm_export_download', array( ExportJobsController::class, 'download' ) );
                add_action( 'admin_post_fbm_export_job_run', array( ExportJobsController::class, 'run' ) );
                add_action( 'admin_post_fbm_export_job_retry', array( ExportJobsController::class, 'retry' ) );
                add_filter( 'wp_privacy_personal_data_exporters', static function ( array $exporters ): array {
                    $exporters['foodbank_manager'] = array(
                        'exporter_friendly_name' => 'FoodBank Manager',
                        'callback'               => [\FBM\Privacy\Exporter::class, 'export'],
                    );
                    return $exporters;
                } );
                add_filter( 'wp_privacy_personal_data_erasers', static function ( array $erasers ): array {
                    $erasers['foodbank_manager'] = array(
                        'eraser_friendly_name' => 'FoodBank Manager',
                        'callback'             => [\FBM\Privacy\Eraser::class, 'erase'],
                    );
                    return $erasers;
                } );

                self::get_instance()->init();
                JobsWorker::init();
        }

        private static function maybe_register_cli(): void {
                if ( defined( 'WP_CLI' ) && \WP_CLI ) {
                        \WP_CLI::add_command( 'fbm', new Commands( new \FBM\CLI\WpCliIO() ) );
                }
        }

        private static function maybe_upgrade(): void {
                $current = \get_option( self::OPTION_VERSION );
                if ( $current === self::VERSION ) {
                        return;
                }

                if ( \version_compare( (string) $current, '1.10.2', '<' ) ) {
                        $flag         = 'fbm_caps_migrated_2025_09';
                        $get_flag     = \is_multisite() ? 'get_site_option' : 'get_option';
                        $update_flag  = \is_multisite() ? 'update_site_option' : 'update_option';
                        if ( ! $get_flag( $flag ) ) {
                                if ( \is_multisite() ) {
                                        foreach ( \get_sites( array( 'number' => 0 ) ) as $site ) {
                                                \switch_to_blog( (int) $site->blog_id );
                                                $role = \get_role( 'administrator' );
                                                if ( $role && ! $role->has_cap( 'fbm_manage_jobs' ) ) {
                                                        $role->add_cap( 'fbm_manage_jobs', true );
                                                }
                                        }
                                        \restore_current_blog();
                                } else {
                                        $role = \get_role( 'administrator' );
                                        if ( $role && ! $role->has_cap( 'fbm_manage_jobs' ) ) {
                                                $role->add_cap( 'fbm_manage_jobs', true );
                                        }
                                }
                                $update_flag( $flag, 1 );
                        }
                }

                \update_option( self::OPTION_VERSION, self::VERSION );
        }

        /** Activate plugin. */
        public static function activate(): void {
                ( new Migrations() )->maybe_migrate();
                Roles::install();
                Cron::maybe_schedule_retention();
                JobsWorker::schedule();
                \update_option( self::OPTION_VERSION, self::VERSION );
        }

        /** Deactivate plugin. */
        public function deactivate(): void {
                if ( function_exists( 'wp_clear_scheduled_hook' ) ) {
                        $hooks = array(
                                \FoodBankManager\Core\Cron::RETENTION_HOOK,
                                \FBM\Core\Retention::EVENT,
                                \FBM\Core\Jobs\JobsWorker::EVENT,
                                'fbm_retention_hourly',
                                'fbm_retention_tick',
                                'fbm_jobs_tick',
                        );
                        foreach ( array_unique( $hooks ) as $hook ) {
                                wp_clear_scheduled_hook( $hook );
                        }
                }
        }
}

if ( ! defined( 'FBM_VER' ) ) {
    $12.2.5$2;
}
