<?php
/**
 * @package FoodBankManager\Tests
 */

declare(strict_types=1);

namespace FBM\Tests\CLI;

use FBM\CLI\CryptoCommand;
use FoodBankManager\Crypto\Crypto;
use FoodBankManager\Crypto\EncryptionSettings;
use FoodBankManager\Diagnostics\MailFailureLog;
use FoodBankManager\Registration\MembersRepository;
use PHPUnit\Framework\TestCase;

/**
 * @covers \FBM\CLI\CryptoCommand
 */
final class CryptoCommandTest extends TestCase {
        private \wpdb $wpdb;

        protected function setUp(): void {
                parent::setUp();

                if ( ! class_exists( '\\WP_CLI', false ) ) {
                        require_once __DIR__ . '/stubs/wp-cli.php';
                }

                \WP_CLI::$commands  = array();
                \WP_CLI::$logs      = array();
                \WP_CLI::$successes = array();

                $this->wpdb             = new \wpdb();
                $GLOBALS['wpdb']        = $this->wpdb;
                $GLOBALS['fbm_options'] = array();

                EncryptionSettings::update_encrypt_new_writes( false );
                $this->seedData();
        }

        protected function tearDown(): void {
                unset( $GLOBALS['wpdb'], $GLOBALS['fbm_options'], $GLOBALS['fbm_deleted_options'] );

                EncryptionSettings::update_encrypt_new_writes( false );

                parent::tearDown();
        }

        public function test_status_reports_adapter_counts(): void {
                $command = new CryptoCommand();
                $command->status( array(), array() );

                $this->assertNotEmpty( \WP_CLI::$logs );
                $this->assertStringContainsString( 'Encrypt new writes: disabled', \WP_CLI::$logs[0] );
                $this->assertStringContainsString( 'Members personal data', implode( "\n", \WP_CLI::$logs ) );
                $this->assertStringContainsString( 'Mail failure log', implode( "\n", \WP_CLI::$logs ) );
                $this->assertSame( array( 'Encryption adapter status listed.' ), \WP_CLI::$successes );
        }

        public function test_migrate_with_unknown_adapter_throws(): void {
                $command = new CryptoCommand();

                $this->expectException( \RuntimeException::class );
                $this->expectExceptionMessage( 'Unknown encryption adapter: unknown' );

                $command->migrate( array(), array( 'adapter' => 'unknown' ) );
        }

        public function test_migrate_and_rotate_commands_complete(): void {
                $command = new CryptoCommand();
                $command->migrate( array(), array() );

                $this->assertContains( 'Encryption migration finished.', \WP_CLI::$successes );
                $this->assertTrue( Crypto::is_envelope( $this->wpdb->members[1]['first_name'] ?? '' ) );

                $command->rotate( array(), array( 'limit' => '1' ) );
                $this->assertContains( 'Encryption rotation finished.', \WP_CLI::$successes );
        }

        private function seedData(): void {
                $members = new MembersRepository( $this->wpdb );
                $members->insert_pending_member( 'CLIREF', 'Cli', 'T', 'cli@example.com', 1 );

                $log = new MailFailureLog();
                $log->record_failure( 1, 'CLIREF', 'cli@example.com', MailFailureLog::CONTEXT_REGISTRATION, MailFailureLog::ERROR_MAIL );
        }
}
