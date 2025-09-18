<?php // phpcs:ignoreFile
/**
 * @package FoodBankManager\Tests
 */

declare(strict_types=1);

namespace FBM\Tests\CLI;

use FBM\CLI\TokenCommand;
use FoodBankManager\Core\Plugin;
use PHPUnit\Framework\TestCase;

/**
 * @covers \FoodBankManager\Core\Plugin::register_cli_commands
 */
final class PluginCliTest extends TestCase {
        protected function setUp(): void {
                parent::setUp();

                if ( class_exists( '\\WP_CLI', false ) ) {
                        \WP_CLI::$commands  = array();
                        \WP_CLI::$logs      = array();
                        \WP_CLI::$successes = array();
                }
        }

        public function test_boot_skips_cli_registration_without_wp_cli(): void {
                if ( class_exists( '\\WP_CLI', false ) ) {
                        $this->markTestSkipped( 'WP-CLI already loaded for this test run.' );
                }

                Plugin::boot();

                $this->assertTrue( true );
        }

        public function test_register_cli_commands_registers_version_and_token(): void {
                if ( ! class_exists( '\\WP_CLI', false ) ) {
                        require_once __DIR__ . '/stubs/wp-cli.php';
                }

                \WP_CLI::$commands  = array();
                \WP_CLI::$logs      = array();
                \WP_CLI::$successes = array();

                Plugin::register_cli_commands();

                $this->assertArrayHasKey( 'fbm version', \WP_CLI::$commands );

                $version_handler = \WP_CLI::$commands['fbm version'];
                $this->assertIsCallable( $version_handler );

                \WP_CLI::$logs = array();
                $version_handler();

                $this->assertSame( array( Plugin::VERSION ), \WP_CLI::$logs );

                $this->assertArrayHasKey( 'fbm token', \WP_CLI::$commands );
                $this->assertSame( TokenCommand::class, \WP_CLI::$commands['fbm token'] );
        }
}
