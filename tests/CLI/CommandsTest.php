<?php
/**
 * @package FoodBankManager\Tests
 */

declare(strict_types=1);

namespace FBM\Tests\CLI;

use FoodBankManager\CLI\Commands;
use FoodBankManager\CLI\TokenCommand;
use FoodBankManager\Core\Plugin;
use PHPUnit\Framework\TestCase;

/**
 * @covers \FoodBankManager\CLI\Commands
 */
final class CommandsTest extends TestCase {
        protected function setUp(): void {
                parent::setUp();

                if ( class_exists( '\\WP_CLI', false ) ) {
                        \WP_CLI::$commands = array();
                        \WP_CLI::$logs      = array();
                        \WP_CLI::$successes = array();
                }
        }

        public function test_register_returns_early_when_wp_cli_missing(): void {
                if ( class_exists( '\\WP_CLI', false ) ) {
                        $this->markTestSkipped( 'WP-CLI already loaded for this test run.' );
                }

                Commands::register();

                $this->assertTrue( true );
        }

        public function test_register_adds_version_command(): void {
                if ( ! class_exists( '\\WP_CLI', false ) ) {
                        require_once __DIR__ . '/stubs/wp-cli.php';
                }

                Commands::register();

                $this->assertArrayHasKey( 'fbm version', \WP_CLI::$commands );

                $handler = \WP_CLI::$commands['fbm version'];
                $this->assertIsCallable( $handler );

                \WP_CLI::$logs = array();
                $handler();

                $this->assertSame( array( Plugin::VERSION ), \WP_CLI::$logs );
        }

        public function test_register_adds_token_command(): void {
                if ( ! class_exists( '\\WP_CLI', false ) ) {
                        require_once __DIR__ . '/stubs/wp-cli.php';
                }

                Commands::register();

                $this->assertArrayHasKey( 'fbm token', \WP_CLI::$commands );

                $handler = \WP_CLI::$commands['fbm token'];
                $this->assertSame( TokenCommand::class, $handler );
        }
}
