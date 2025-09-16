<?php
/**
 * Core plugin bootstrap.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Core;

use FoodBankManager\Auth\Capabilities;
use FoodBankManager\Rest\CheckinController;
use FoodBankManager\Shortcodes\RegistrationForm;
use FoodBankManager\Shortcodes\StaffDashboard;
use function add_action;
use function do_action;

/**
 * Main plugin orchestrator.
 */
final class Plugin {

	public const VERSION = '2.2.17';

	/**
	 * Register runtime hooks.
	 */
	public static function boot(): void {
		Assets::setup();
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
