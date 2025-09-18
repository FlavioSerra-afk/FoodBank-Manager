<?php
/**
 * Core plugin bootstrap.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Core;

use FoodBankManager\Admin\DiagnosticsPage;
use FoodBankManager\Admin\MembersPage;
use FoodBankManager\Admin\ReportsPage;
use FoodBankManager\Admin\SchedulePage;
use FoodBankManager\Admin\SettingsPage;
use FoodBankManager\Admin\ThemePage;
use FoodBankManager\Auth\Capabilities;
use FoodBankManager\CLI\Commands;
use FoodBankManager\Rest\CheckinController;
use FoodBankManager\Shortcodes\RegistrationForm;
use FoodBankManager\Shortcodes\StaffDashboard;
use WP_Role;
use function __;
use function add_role;
use function array_fill_keys;
use function get_role;
use function add_action;
use function do_action;

/**
 * Main plugin orchestrator.
 */
final class Plugin {

        public const VERSION = '1.0.6';


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

                add_action( 'rest_api_init', array( CheckinController::class, 'register_routes' ) );

                if ( defined( 'WP_CLI' ) && WP_CLI ) {
                        Commands::register();
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
		do_action( 'fbm_activated' );
	}

	/**
	 * Perform deactivation cleanup.
	 */
	public static function deactivate(): void {
		do_action( 'fbm_deactivated' );
	}
}
