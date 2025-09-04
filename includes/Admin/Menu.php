<?php
/**
 * Admin menu registration.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

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
	private const CAP_EMAILS      = 'fb_manage_emails';
	private const CAP_SETTINGS    = 'fb_manage_settings';
	private const CAP_DIAGNOSTICS = 'fb_manage_diagnostics';
	private const CAP_PERMISSIONS = 'fb_manage_permissions';
	private const CAP_THEME       = 'fb_manage_theme';

		/**
		 * Canonical admin slugs.
		 *
		 * @return array<int,string>
		 */
	public static function slugs(): array {
			return array(
				'fbm',
				'fbm_attendance',
				'fbm_database',
				'fbm_forms',
				'fbm_emails',
				'fbm_settings',
				'fbm_permissions',
				'fbm_diagnostics',
				'fbm_theme',
				'fbm_shortcodes',
			);
	}

		/**
		 * Register admin menus.
		 *
		 * @since 0.1.x
		 *
		 * @return void
		 */
	public static function register(): void {
			static $registered = false;
		if ( $registered ) {
				return;
		}
		if ( current_action() !== 'admin_menu' ) {
				add_action( 'admin_menu', array( self::class, __FUNCTION__ ) );
				return;
		}
			$registered  = true;
			$parent_slug = 'fbm';

			add_menu_page(
				esc_html__( 'FoodBank', 'foodbank-manager' ),
				esc_html__( 'FoodBank', 'foodbank-manager' ),
				self::CAP_DASHBOARD,
				$parent_slug,
				array( self::class, 'dashboard' ),
				'dashicons-clipboard',
				58
			);

			add_submenu_page(
				$parent_slug,
				esc_html__( 'Dashboard', 'foodbank-manager' ),
				esc_html__( 'Dashboard', 'foodbank-manager' ),
				self::CAP_DASHBOARD,
				$parent_slug,
				array( self::class, 'dashboard' )
			);

			add_submenu_page(
				$parent_slug,
				esc_html__( 'Attendance', 'foodbank-manager' ),
				esc_html__( 'Attendance', 'foodbank-manager' ),
				self::CAP_ATTENDANCE,
				'fbm_attendance',
				array( \FoodBankManager\Admin\AttendancePage::class, 'route' )
			);

			add_submenu_page(
				$parent_slug,
				esc_html__( 'Database', 'foodbank-manager' ),
				esc_html__( 'Database', 'foodbank-manager' ),
				self::CAP_DATABASE,
				'fbm_database',
				array( \FoodBankManager\Admin\DatabasePage::class, 'route' )
			);

			add_submenu_page(
				$parent_slug,
				esc_html__( 'Forms', 'foodbank-manager' ),
				esc_html__( 'Forms', 'foodbank-manager' ),
				self::CAP_FORMS,
				'fbm_forms',
				array( \FoodBankManager\Admin\FormsPage::class, 'route' )
			);

			add_submenu_page(
				$parent_slug,
				esc_html__( 'Email Templates', 'foodbank-manager' ),
				esc_html__( 'Email Templates', 'foodbank-manager' ),
				self::CAP_EMAILS,
				'fbm_emails',
				array( \FoodBankManager\Admin\EmailsPage::class, 'route' )
			);

			add_submenu_page(
				$parent_slug,
				esc_html__( 'Settings', 'foodbank-manager' ),
				esc_html__( 'Settings', 'foodbank-manager' ),
				self::CAP_SETTINGS,
				'fbm_settings',
				array( \FoodBankManager\Admin\SettingsPage::class, 'route' )
			);

			add_submenu_page(
				$parent_slug,
				esc_html__( 'Permissions', 'foodbank-manager' ),
				esc_html__( 'Permissions', 'foodbank-manager' ),
				self::CAP_PERMISSIONS,
				'fbm_permissions',
				array( \FoodBankManager\Admin\PermissionsPage::class, 'route' )
			);

			add_submenu_page(
				$parent_slug,
				esc_html__( 'Diagnostics', 'foodbank-manager' ),
				esc_html__( 'Diagnostics', 'foodbank-manager' ),
				self::CAP_DIAGNOSTICS,
				'fbm_diagnostics',
				array( \FoodBankManager\Admin\DiagnosticsPage::class, 'render' )
			);

			add_submenu_page(
				$parent_slug,
				esc_html__( 'Design & Theme', 'foodbank-manager' ),
				esc_html__( 'Design & Theme', 'foodbank-manager' ),
				self::CAP_THEME,
				'fbm_theme',
				array( \FoodBankManager\Admin\ThemePage::class, 'route' )
			);

			add_submenu_page(
				$parent_slug,
				esc_html__( 'Shortcodes', 'foodbank-manager' ),
				esc_html__( 'Shortcodes', 'foodbank-manager' ),
				self::CAP_FORMS,
				'fbm_shortcodes',
				array( \FoodBankManager\Admin\ShortcodesPage::class, 'route' )
			);
	}

				/**
				 * Render the dashboard page.
				 *
				 * @since 0.1.x
				 *
				 * @return void
				 */
	public static function dashboard(): void {
					self::include_template( 'dashboard.php' );
	}

		/**
		 * Safely include an admin template.
		 *
		 * @since 1.0.5
		 *
		 * @param string $file Template filename.
		 *
		 * @return void
		 */
	private static function include_template( string $file ): void {
			$path = FBM_PATH . 'templates/admin/' . $file;
		if ( file_exists( $path ) ) {
				/**
				 * Include template.
				 *
				 * @psalm-suppress UnresolvableInclude
				 */
				require $path;
		}
	}
}
