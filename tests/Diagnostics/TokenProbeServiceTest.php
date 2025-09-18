<?php
/**
 * @package FoodBankManager\Tests
 */

declare(strict_types=1);

namespace FBM\Tests\Diagnostics;

use FoodBankManager\Diagnostics\TokenProbeService;
use FoodBankManager\Token\Token;
use FoodBankManager\Token\TokenRepository;
use PHPUnit\Framework\TestCase;

use function strlen;
use function substr;
use function substr_replace;

/**
 * @covers \FoodBankManager\Diagnostics\TokenProbeService
 */
final class TokenProbeServiceTest extends TestCase {
        private Token $token;

        private TokenProbeService $service;

        protected function setUp(): void {
                parent::setUp();

                $repository    = new TokenRepository( new \wpdb() );
                $this->token   = new Token( $repository, 'probe-current-secret', 'probe-previous-secret' );
                $this->service = new TokenProbeService( $this->token );
        }

        public function test_probe_reports_hmac_match_for_valid_token(): void {
                $issued = $this->token->issue( 1001 );

                $result = $this->service->probe( $issued['payload'] );

                $this->assertSame(
                        array(
                                'version'    => 'v1',
                                'hmac_match' => true,
                                'revoked'    => false,
                        ),
                        $result
                );
        }

        public function test_probe_detects_single_byte_mutation(): void {
                $issued = $this->token->issue( 2002 );
                $payload = $issued['payload'];
                $length  = strlen( $payload );

                $replacement = 'A';

                if ( $length > 0 ) {
                        $last = substr( $payload, -1 );
                        $replacement = 'A' === $last ? 'B' : 'A';
                }

                $mutated = substr_replace( $payload, $replacement, -1 );

                $result = $this->service->probe( $mutated );

                $this->assertSame(
                        array(
                                'version'    => 'v1',
                                'hmac_match' => false,
                                'revoked'    => false,
                        ),
                        $result
                );
        }

        public function test_probe_marks_revoked_tokens(): void {
                $issued = $this->token->issue( 3003 );

                $this->assertTrue( $this->token->revoke( 3003 ) );

                $result = $this->service->probe( $issued['payload'] );

                $this->assertSame(
                        array(
                                'version'    => 'v1',
                                'hmac_match' => true,
                                'revoked'    => true,
                        ),
                        $result
                );
        }

        public function test_probe_handles_invalid_format_gracefully(): void {
                $result = $this->service->probe( 'invalid-token' );

                $this->assertSame(
                        array(
                                'version'    => null,
                                'hmac_match' => false,
                                'revoked'    => false,
                        ),
                        $result
                );
        }
}
