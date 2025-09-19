<?php
/**
 * Core plugin bootstrap.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Core;

use FoodBankManager\Core\Assets;
use FoodBankManager\Core\Install;

use FBM\CLI\CryptoCommand;
use FBM\CLI\TokenCommand;
use FoodBankManager\Admin\DiagnosticsPage;
use FoodBankManager\Admin\MembersPage;
use FoodBankManager\Admin\ReportsPage;
use FoodBankManager\Admin\SchedulePage;
use FoodBankManager\Admin\SettingsPage;
use FoodBankManager\Admin\ThemePage;
use FoodBankManager\Auth\Capabilities;
use FoodBankManager\Crypto\EncryptionSettings;
use FoodBankManager\Privacy\Privacy;
use FoodBankManager\Rest\CheckinController;
use FoodBankManager\Shortcodes\RegistrationForm;
use FoodBankManager\Shortcodes\StaffDashboard;
use WP_Role;
use function __;
use function add_action;
use function add_role;
use function array_fill_keys;
use function do_action;
use function get_role;

/**
 * Main plugin orchestrator.
 */
final class Plugin {
	public const VERSION = '1.3.0';

	/**
	 * Register runtime hooks.
	 */
	public static function boot(): void {
		Assets::setup();
		DiagnosticsPage::register();
		MembersPage::register();
		ReportsPage::register();
		SchedulePage::register();
		SettingsPage::register();
		ThemePage::register();
		RegistrationForm::register();
		StaffDashboard::register();
		Privacy::register();

		add_action( 'rest_api_init', array( CheckinController::class, 'register_routes' ) );

		if ( defined( 'WP_CLI' ) && WP_CLI && class_exists( '\\WP_CLI' ) ) {
			self::register_cli_commands();
		}

		do_action( 'fbm_booted' );
	}

	/**
	 * Perform activation tasks.
	 */
	public static function activate(): void {
		Capabilities::ensure();

		$roles = array(
			'foodbank_member' => array(
				'label'        => __( 'FoodBank Member', 'foodbank-manager' ),
				'capabilities' => array_fill_keys( Capabilities::all(), true ),
			),
			'fbm_manager'     => array(
				'label'        => __( 'FoodBank Manager', 'foodbank-manager' ),
				'capabilities' => array_fill_keys( Capabilities::bundle( 'fbm_manager' ), true ),
			),
			'fbm_staff'       => array(
				'label'        => __( 'FoodBank Staff', 'foodbank-manager' ),
				'capabilities' => array_fill_keys( Capabilities::bundle( 'fbm_staff' ), true ),
			),
		);

		foreach ( $roles as $role_name => $definition ) {
			$role = get_role( $role_name );

			if ( $role instanceof WP_Role ) {
				foreach ( array_keys( $definition['capabilities'] ) as $capability ) {
					$role->add_cap( $capability );
				}

				continue;
			}

			add_role( $role_name, $definition['label'], $definition['capabilities'] );
		}

		Install::ensure_tables();
		EncryptionSettings::bootstrap_on_activation();
		do_action( 'fbm_activated' );
	}

	/**
	 * Perform deactivation cleanup.
	 */
	public static function deactivate(): void {
		do_action( 'fbm_deactivated' );
	}

	/**
	 * Register WP-CLI commands under the fbm namespace.
	 */
	public static function register_cli_commands(): void {
		\WP_CLI::add_command(
			'fbm version',
			static function (): void {
				\WP_CLI::log( self::VERSION );
			}
		);

		\WP_CLI::add_command( 'fbm token', TokenCommand::class );
		\WP_CLI::add_command( 'fbm crypto', CryptoCommand::class );
	}
}
