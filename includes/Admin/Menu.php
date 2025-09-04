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
	 * Register admin menus.
	 *
	 * @since 0.1.x
	 *
	 * @return void
	 */
	public static function register(): void {
		if ( current_action() !== 'admin_menu' ) {
			add_action( 'admin_menu', array( self::class, __FUNCTION__ ) );
		}

				add_menu_page(
					esc_html__( 'FoodBank', 'foodbank-manager' ),
					esc_html__( 'FoodBank', 'foodbank-manager' ),
					self::CAP_DASHBOARD,
					'fbm-dashboard',
					array( self::class, 'dashboard' ),
					'dashicons-clipboard',
					58
				);

		remove_submenu_page( 'fbm-dashboard', 'fbm-dashboard' );

				self::add_page(
					self::CAP_DASHBOARD,
					'fbm-dashboard',
					'fbm-dashboard',
					esc_html__( 'Dashboard', 'foodbank-manager' ),
					array( self::class, 'dashboard' )
				);

				self::add_page(
					self::CAP_ATTENDANCE,
					'fbm-dashboard',
					'fbm-attendance',
					esc_html__( 'Attendance', 'foodbank-manager' ),
					array( \FoodBankManager\Admin\AttendancePage::class, 'route' )
				);

				self::add_page(
					self::CAP_DATABASE,
					'fbm-dashboard',
					'fbm-database',
					esc_html__( 'Database', 'foodbank-manager' ),
					array( \FoodBankManager\Admin\DatabasePage::class, 'route' )
				);

				self::add_page(
					self::CAP_FORMS,
					'fbm-dashboard',
					'fbm-forms',
					esc_html__( 'Forms', 'foodbank-manager' ),
					array( \FoodBankManager\Admin\FormsPage::class, 'route' )
				);

				self::add_page(
					self::CAP_EMAILS,
					'fbm-dashboard',
					'fbm-emails',
					esc_html__( 'Email Templates', 'foodbank-manager' ),
					array( \FoodBankManager\Admin\EmailsPage::class, 'route' )
				);

				self::add_page(
					self::CAP_SETTINGS,
					'fbm-dashboard',
					'fbm-settings',
					esc_html__( 'Settings', 'foodbank-manager' ),
					array( \FoodBankManager\Admin\SettingsPage::class, 'route' )
				);

				self::add_page(
					self::CAP_DIAGNOSTICS,
					'fbm-dashboard',
					'fbm-diagnostics',
					esc_html__( 'Diagnostics', 'foodbank-manager' ),
					static function (): void {
								self::include_template( 'diagnostics.php' );
					}
				);

				self::add_page(
					self::CAP_PERMISSIONS,
					'fbm-dashboard',
					'fbm-permissions',
					esc_html__( 'Permissions', 'foodbank-manager' ),
					array( \FoodBankManager\Admin\PermissionsPage::class, 'route' )
				);

				self::add_page(
					self::CAP_THEME,
					'fbm-dashboard',
					'fbm-theme',
					esc_html__( 'Design & Theme', 'foodbank-manager' ),
					array( \FoodBankManager\Admin\ThemePage::class, 'route' )
				);
	}

		/**
		 * Add a submenu page.
		 *
		 * @param string   $capability  Capability required.
		 * @param string   $parent_slug Parent slug.
		 * @param string   $slug        Page slug.
		 * @param string   $title       Menu title.
		 * @param callable $callback    Callback for rendering.
		 *
		 * @return void
		 */
	private static function add_page( string $capability, string $parent_slug, string $slug, string $title, callable $callback ): void {
			add_submenu_page( $parent_slug, $title, $title, $capability, $slug, $callback );
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
