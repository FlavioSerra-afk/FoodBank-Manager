<?php
/**
 * WP-CLI command registrations.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\CLI;

use FoodBankManager\Core\Plugin;
use function class_exists;

/**
 * Registers WP-CLI commands for FoodBank Manager.
 */
final class Commands {
	/**
	 * Register CLI commands when WP-CLI is available.
	 */
	public static function register(): void {
		if ( ! class_exists( 'WP_CLI' ) ) {
			return;
		}

		\WP_CLI::add_command(
			'fbm version',
			static function (): void {
				\WP_CLI::log( Plugin::VERSION );
			}
		);
	}
}
