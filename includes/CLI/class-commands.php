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
use function defined;
use function is_readable;

/**
 * Registers WP-CLI commands for FoodBank Manager.
 */
final class Commands {
	/**
	 * Register CLI commands when WP-CLI is available.
	 */
        public static function register(): void {
                if ( ! defined( 'WP_CLI' ) || ! WP_CLI || ! class_exists( 'WP_CLI' ) ) {
                        return;
                }

                \WP_CLI::add_command(
                        'fbm version',
			static function (): void {
                                \WP_CLI::log( Plugin::VERSION );
                        }
                );

                $command_path = FBM_PATH . 'wp-cli/TokenCommand.php';

                if ( is_readable( $command_path ) ) {
                        require_once $command_path;

                        $token_command = __NAMESPACE__ . '\\TokenCommand';

                        if ( class_exists( $token_command ) ) {
                                \WP_CLI::add_command(
                                        'fbm token',
                                        $token_command
                                );
                        }
                }
        }
}
