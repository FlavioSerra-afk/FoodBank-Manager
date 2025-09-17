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
use FoodBankManager\Admin\ThemePage;
use FoodBankManager\Auth\Capabilities;
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

	public const VERSION = '2.2.24';


	/**
	 * Register runtime hooks.
	 */
	public static function boot(): void {
                Assets::setup();
                DiagnosticsPage::register();
                MembersPage::register();
                ReportsPage::register();
                ThemePage::register();
		RegistrationForm::register();
		StaffDashboard::register();

		add_action( 'rest_api_init', array( CheckinController::class, 'register_routes' ) );

		do_action( 'fbm_booted' );
	}

	/**
	 * Perform activation tasks.
	 */
        public static function activate(): void {
                Capabilities::ensure();

                $capabilities = array_fill_keys( Capabilities::all(), true );
                $role         = get_role( 'foodbank_member' );

                if ( $role instanceof WP_Role ) {
                        foreach ( array_keys( $capabilities ) as $capability ) {
                                $role->add_cap( $capability );
                        }
                } else {
                        add_role(
                                'foodbank_member',
                                __( 'FoodBank Member', 'foodbank-manager' ),
                                $capabilities
                        );
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
