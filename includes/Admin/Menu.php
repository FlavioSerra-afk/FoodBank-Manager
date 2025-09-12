<?php // phpcs:ignoreFile
/**
 * Admin menu registration.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FBM\Core\RenderOnce;
use FBM\Core\Options;
use function add_query_arg;
use function admin_url;
use function wp_safe_redirect;

/**
 * Admin menu handler.
 *
 * @since 0.1.x
 */
final class Menu {
        private const CAP_DASHBOARD   = 'fb_manage_dashboard';
        private const CAP_ATTENDANCE  = 'fb_manage_attendance';
        private const CAP_DATABASE    = 'fb_manage_database';
        private const CAP_FORMS       = 'fb_manage_forms';
        private const CAP_FORM_BUILDER = 'fbm_manage_forms';
        private const CAP_EMAILS      = 'fb_manage_emails';
        private const CAP_SETTINGS    = 'fb_manage_settings';
        private const CAP_DIAGNOSTICS = 'fb_manage_diagnostics';
        private const CAP_PERMISSIONS = 'fb_manage_permissions';
        private const CAP_THEME       = 'fb_manage_theme';
        private const CAP_EVENTS      = 'fbm_manage_events';
        private const CAP_SCAN        = 'fb_manage_attendance';
        private const CAP_REPORTS     = 'fb_manage_reports';
        private const CAP_JOBS        = 'fbm_manage_jobs';

        /**
         * Canonical admin slugs.
         *
         * @return array<int,string>
         */
        public static function slugs(): array {
                return array(
                        'fbm',
                        'fbm_attendance',
                        'fbm_reports',
                        'fbm_jobs',
                        'fbm_events',
                        'fbm_scan',
                        'fbm_database',
                        'fbm_forms',
                        'fbm_form_builder',
                        'fbm_emails',
                        'fbm_settings',
                        'fbm_permissions',
                        'fbm_diagnostics',
                        'fbm_theme',
                        'fbm_shortcodes',
                );
        }

        /**
         * Whether menus have been registered.
         *
         * @var bool
         */
        private static bool $registered = false;

        /**
         * Register admin menus.
         *
         * @since 0.1.x
         *
         * @return void
         */
        public static function register(): void {
                if ( self::$registered ) {
                        return;
                }
                self::$registered = true;
                $parent_slug      = 'fbm';

                $root_cap = current_user_can( 'fb_manage_dashboard' )
                        ? 'fb_manage_dashboard'
                        : ( current_user_can( 'manage_options' ) ? 'manage_options' : 'do_not_allow' );

                add_menu_page(
                        __( 'FoodBank', 'foodbank-manager' ),
                        __( 'FoodBank', 'foodbank-manager' ),
                        $root_cap,
                        $parent_slug,
                        array( self::class, 'render_dashboard' ),
                        'dashicons-groups',
                        58
                );

                add_submenu_page(
                        $parent_slug,
                        esc_html__( 'Dashboard', 'foodbank-manager' ),
                        esc_html__( 'Dashboard', 'foodbank-manager' ),
                        self::CAP_DASHBOARD,
                        $parent_slug,
                        array( self::class, 'render_dashboard' )
                );

                add_submenu_page(
                        $parent_slug,
                        esc_html__( 'Attendance', 'foodbank-manager' ),
                        esc_html__( 'Attendance', 'foodbank-manager' ),
                        self::CAP_ATTENDANCE,
                        'fbm_attendance',
                        array( self::class, 'render_attendance' )
                );

                add_submenu_page(
                        $parent_slug,
                        esc_html__( 'Reports', 'foodbank-manager' ),
                        esc_html__( 'Reports', 'foodbank-manager' ),
                        self::CAP_REPORTS,
                        'fbm_reports',
                        array( self::class, 'render_reports' )
                );

                add_submenu_page(
                        $parent_slug,
                        esc_html__( 'Jobs', 'foodbank-manager' ),
                        esc_html__( 'Jobs', 'foodbank-manager' ),
                        self::CAP_JOBS,
                        'fbm_jobs',
                        array( self::class, 'render_jobs' )
                );

                if ( Options::get( 'modules.events', false ) ) {
                        add_submenu_page(
                                $parent_slug,
                                esc_html__( 'Events', 'foodbank-manager' ),
                                esc_html__( 'Events', 'foodbank-manager' ),
                                self::CAP_EVENTS,
                                'fbm_events',
                                array( self::class, 'render_events' )
                        );
                }

                add_submenu_page(
                        $parent_slug,
                        esc_html__( 'Scan', 'foodbank-manager' ),
                        esc_html__( 'Scan', 'foodbank-manager' ),
                        self::CAP_SCAN,
                        'fbm_scan',
                        array( self::class, 'redirect_scan' )
                );

                add_submenu_page(
                        $parent_slug,
                        esc_html__( 'Database', 'foodbank-manager' ),
                        esc_html__( 'Database', 'foodbank-manager' ),
                        self::CAP_DATABASE,
                        'fbm_database',
                        array( self::class, 'render_database' )
                );

                add_submenu_page(
                        $parent_slug,
                        esc_html__( 'Forms', 'foodbank-manager' ),
                        esc_html__( 'Forms', 'foodbank-manager' ),
                        self::CAP_FORMS,
                        'fbm_forms',
                        array( self::class, 'render_forms' )
                );

                add_submenu_page(
                        $parent_slug,
                        esc_html__( 'Forms (Builder)', 'foodbank-manager' ),
                        esc_html__( 'Forms (Builder)', 'foodbank-manager' ),
                        self::CAP_FORM_BUILDER,
                        'fbm_form_builder',
                        array( self::class, 'render_form_builder' )
                );

                add_submenu_page(
                        $parent_slug,
                        esc_html__( 'Email Templates', 'foodbank-manager' ),
                        esc_html__( 'Email Templates', 'foodbank-manager' ),
                        self::CAP_EMAILS,
                        'fbm_emails',
                        array( self::class, 'render_emails' )
                );

                add_submenu_page(
                        $parent_slug,
                        esc_html__( 'Settings', 'foodbank-manager' ),
                        esc_html__( 'Settings', 'foodbank-manager' ),
                        self::CAP_SETTINGS,
                        'fbm_settings',
                        array( self::class, 'render_settings' )
                );

                add_submenu_page(
                        $parent_slug,
                        esc_html__( 'Permissions', 'foodbank-manager' ),
                        esc_html__( 'Permissions', 'foodbank-manager' ),
                        self::CAP_PERMISSIONS,
                        'fbm_permissions',
                        array( self::class, 'render_permissions' )
                );

                add_submenu_page(
                        $parent_slug,
                        esc_html__( 'Diagnostics', 'foodbank-manager' ),
                        esc_html__( 'Diagnostics', 'foodbank-manager' ),
                        self::CAP_DIAGNOSTICS,
                        'fbm_diagnostics',
                        array( self::class, 'render_diagnostics' )
                );

                add_submenu_page(
                        $parent_slug,
                        esc_html__( 'Design & Theme', 'foodbank-manager' ),
                        esc_html__( 'Design & Theme', 'foodbank-manager' ),
                        self::CAP_THEME,
                        'fbm_theme',
                        array( self::class, 'render_theme' )
                );

                add_submenu_page(
                        $parent_slug,
                        esc_html__( 'Shortcodes', 'foodbank-manager' ),
                        esc_html__( 'Shortcodes', 'foodbank-manager' ),
                        self::CAP_FORMS,
                        'fbm_shortcodes',
                        array( self::class, 'render_shortcodes' )
                );
                do_action( 'fbm_menu_registered' );

                EntryPage::register();
        }

        public static function render_dashboard(): void {
                self::render_once( 'admin:dashboard', static function (): void {
                        \FBM\Admin\DashboardPage::route();
                } );
        }


        public static function render_attendance(): void {
                self::render_once( 'admin:attendance', static function (): void {
                        \FoodBankManager\Admin\AttendancePage::route();
                } );
        }

        public static function render_reports(): void {
                self::render_once( 'admin:reports', static function (): void {
                        \FBM\Admin\ReportsPage::route();
                } );
        }

        public static function render_jobs(): void {
                self::render_once( 'admin:jobs', static function (): void {
                        \FBM\Admin\JobsPage::route();
                } );
        }

        public static function render_events(): void {
                self::render_once( 'admin:events', static function (): void {
                        \FBM\Admin\EventsPage::route();
                } );
        }

        public static function redirect_scan(): void {
                wp_safe_redirect( add_query_arg( array( 'page' => 'fbm_attendance', 'tab' => 'scan' ), admin_url( 'admin.php' ) ) );
                exit;
        }

        public static function render_database(): void {

                self::render_once( 'admin:database', static function (): void {
                        \FoodBankManager\Admin\DatabasePage::route();
                } );
        }

        public static function render_forms(): void {
                self::render_once( 'admin:forms', static function (): void {
                        \FoodBankManager\Admin\FormsPage::route();
                } );
        }

        public static function render_form_builder(): void {
                self::render_once( 'admin:form_builder', static function (): void {
                        \FBM\Admin\FormBuilderPage::route();
                } );
        }

        public static function render_emails(): void {
                self::render_once( 'admin:emails', static function (): void {
                        \FoodBankManager\Admin\EmailTemplatesPage::route();
                } );
        }

        public static function render_settings(): void {
                self::render_once( 'admin:settings', static function (): void {
                        \FoodBankManager\Admin\SettingsPage::route();
                } );
        }

        public static function render_permissions(): void {
                self::render_once( 'admin:permissions', static function (): void {
                        \FoodBankManager\Admin\PermissionsPage::route();
                } );
        }

        public static function render_diagnostics(): void {
                self::render_once( 'admin:diagnostics', static function (): void {
                        \FoodBankManager\Admin\DiagnosticsPage::render();
                } );
        }

        public static function render_theme(): void {
                self::render_once( 'admin:theme', static function (): void {
                        \FoodBankManager\Admin\ThemePage::route();
                } );
        }

        public static function render_shortcodes(): void {
                self::render_once( 'admin:shortcodes', static function (): void {
                        \FoodBankManager\Admin\ShortcodesPage::route();
                } );
        }

        private static function render_once( string $key, callable $cb ): void {
                if ( RenderOnce::already( $key ) ) {
                        return;
                }
                RenderOnce::enter( $key );
                $cb();
        }

}
