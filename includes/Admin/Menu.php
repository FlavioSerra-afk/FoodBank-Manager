<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase,WordPress.Files.FileName.InvalidClassFileName
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
	private const CAP_SETTINGS    = 'fb_manage_settings';
	private const CAP_DIAGNOSTICS = 'fb_manage_diagnostics';
	private const CAP_PERMISSIONS = 'fb_manage_permissions';

	/**
	 * Captured page hooks.
	 *
	 * @var string[]
	 */
	private static array $hooks = array();

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
			return;
		}

		$capability = current_user_can( self::CAP_DASHBOARD ) ? self::CAP_DASHBOARD : 'manage_options';

		self::$hooks[] = add_menu_page(
			__( 'FoodBank', 'foodbank-manager' ),
			__( 'FoodBank', 'foodbank-manager' ),
			$capability,
			'fbm-dashboard',
			array( self::class, 'dashboard' ),
			'dashicons-clipboard',
			58
		);

		remove_submenu_page( 'fbm-dashboard', 'fbm-dashboard' );

		self::$hooks[] = self::add_page(
			self::CAP_DASHBOARD,
			'fbm-dashboard',
			'fbm-dashboard',
			__( 'Dashboard', 'foodbank-manager' ),
			array( self::class, 'dashboard' )
		);

		self::$hooks[] = self::add_page(
			self::CAP_ATTENDANCE,
			'fbm-dashboard',
			'fbm-attendance',
			__( 'Attendance', 'foodbank-manager' ),
			array( \FoodBankManager\Admin\AttendancePage::class, 'route' )
		);

		self::$hooks[] = self::add_page(
			self::CAP_DATABASE,
			'fbm-dashboard',
			'fbm-database',
			__( 'Database', 'foodbank-manager' ),
			array( \FoodBankManager\Admin\DatabasePage::class, 'route' )
		);

		self::$hooks[] = self::add_page(
			self::CAP_FORMS,
			'fbm-dashboard',
			'fbm-forms',
			__( 'Forms', 'foodbank-manager' ),
			static function (): void {
				$file = FBM_PATH . 'templates/admin/forms.php';
				if ( file_exists( $file ) ) {
					/**
					 * Include forms template.
					 *
					 * @psalm-suppress UnresolvableInclude
					 */
					require $file;
				}
			}
		);

		self::$hooks[] = self::add_page(
			self::CAP_SETTINGS,
			'fbm-dashboard',
			'fbm-emails',
			__( 'Email Templates', 'foodbank-manager' ),
			array( \FoodBankManager\Admin\EmailsPage::class, 'route' )
		);

		self::$hooks[] = self::add_page(
			self::CAP_SETTINGS,
			'fbm-dashboard',
			'fbm-settings',
			__( 'Settings', 'foodbank-manager' ),
			array( \FoodBankManager\Admin\SettingsPage::class, 'route' )
		);

		self::$hooks[] = self::add_page(
			self::CAP_DIAGNOSTICS,
			'fbm-dashboard',
			'fbm-diagnostics',
			__( 'Diagnostics', 'foodbank-manager' ),
			static function (): void {
				$file = FBM_PATH . 'templates/admin/diagnostics.php';
				if ( file_exists( $file ) ) {
					/**
					 * Include diagnostics template.
					 *
					 * @psalm-suppress UnresolvableInclude
					 */
					require $file;
				}
			}
		);

		self::$hooks[] = self::add_page(
			self::CAP_PERMISSIONS,
			'fbm-dashboard',
			'fbm-permissions',
			__( 'Permissions', 'foodbank-manager' ),
			array( \FoodBankManager\Admin\PermissionsPage::class, 'route' )
		);

		self::$hooks[] = self::add_page(
			self::CAP_SETTINGS,
			'fbm-dashboard',
			'fbm-theme',
			__( 'Design & Theme', 'foodbank-manager' ),
			array( \FoodBankManager\Admin\ThemePage::class, 'route' )
		);

		self::screen_hooks();
	}

	/**
	 * Add a submenu page.
	 *
	 * @param string   $capability Capability required.
	 * @param string   $parent_slug     Parent slug.
	 * @param string   $slug       Page slug.
	 * @param string   $title      Menu title.
	 * @param callable $callback   Callback for rendering.
	 *
	 * @return string Hook suffix.
	 */
	private static function add_page( string $capability, string $parent_slug, string $slug, string $title, callable $callback ): string {
		return (string) add_submenu_page( $parent_slug, $title, $title, $capability, $slug, $callback );
	}

	/**
	 * Attach screen-specific hooks.
	 *
	 * @return void
	 */
	private static function screen_hooks(): void {
		foreach ( self::$hooks as $hook ) {
			add_action( "load-{$hook}", array( self::class, 'enqueue_admin_assets' ) );
		}
	}

	/**
	 * Render the dashboard page.
	 *
	 * @since 0.1.x
	 *
	 * @return void
	 */
	public static function dashboard(): void {
		$file = FBM_PATH . 'templates/admin/dashboard.php';
		if ( file_exists( $file ) ) {
			/**
			 * Include dashboard template.
			 *
			 * @psalm-suppress UnresolvableInclude
			 */
			require $file;
		}
	}

	/**
	 * Enqueue admin assets for plugin screens.
	 *
	 * @since 0.1.x
	 *
	 * @return void
	 */
	public static function enqueue_admin_assets(): void {
		$handle = 'fbm-admin-theme';
		if ( ! wp_style_is( $handle, 'registered' ) ) {
			wp_register_style( $handle, FBM_URL . 'assets/css/theme-admin.css', array(), \FoodBankManager\Core\Plugin::FBM_VERSION );
		}
		wp_enqueue_style( $handle );
	}
}
