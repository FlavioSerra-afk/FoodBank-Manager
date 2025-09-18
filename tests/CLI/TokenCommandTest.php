<?php // phpcs:ignoreFile
/**
 * @package FoodBankManager\Tests
 */

declare(strict_types=1);

namespace FBM\Tests\CLI;

use FBM\CLI\TokenCommand;
use FoodBankManager\Diagnostics\TokenProbeService;
use FoodBankManager\Token\Token;
use FoodBankManager\Token\TokenRepository;
use PHPUnit\Framework\TestCase;
use function json_decode;

/**
 * @covers \FBM\CLI\TokenCommand
 */
final class TokenCommandTest extends TestCase {
        protected function setUp(): void {
                parent::setUp();

                if ( ! class_exists( '\\WP_CLI', false ) ) {
                        require_once __DIR__ . '/stubs/wp-cli.php';
                }

                \WP_CLI::$commands  = array();
                \WP_CLI::$logs      = array();
                \WP_CLI::$successes = array();

                $GLOBALS['wpdb'] = new \wpdb();
        }

        protected function tearDown(): void {
                unset( $GLOBALS['wpdb'] );

                parent::tearDown();
        }

        public function test_probe_outputs_json_result(): void {
                $repository = new TokenRepository( $GLOBALS['wpdb'] );
                $token      = new Token( $repository, 'cli-current-secret', 'cli-previous-secret' );
                $issued     = $token->issue( 42 );

                $command = new TokenCommand( new TokenProbeService( $token ) );

                \WP_CLI::$successes = array();

                $command->probe( array( $issued['payload'] ), array() );

                $this->assertCount( 1, \WP_CLI::$successes );

                $payload = \WP_CLI::$successes[0];
                $this->assertIsString( $payload );

                $this->assertSame(
                        array(
                                'version'    => '1',
                                'hmac_match' => true,
                                'revoked'    => false,
                        ),
                        json_decode( $payload, true )
                );
        }

        public function test_probe_requires_argument(): void {
                $command = new TokenCommand( new TokenProbeService( new Token( new TokenRepository( $GLOBALS['wpdb'] ), 'cli-current-secret', 'cli-previous-secret' ) ) );

                $this->expectException( \RuntimeException::class );
                $this->expectExceptionMessage( 'Exactly one token payload argument is required.' );

                $command->probe( array(), array() );
        }

        public function test_probe_rejects_empty_payload(): void {
                $command = new TokenCommand( new TokenProbeService( new Token( new TokenRepository( $GLOBALS['wpdb'] ), 'cli-current-secret', 'cli-previous-secret' ) ) );

                $this->expectException( \RuntimeException::class );
                $this->expectExceptionMessage( 'The token payload cannot be empty.' );

                $command->probe( array( '' ), array() );
        }
}
