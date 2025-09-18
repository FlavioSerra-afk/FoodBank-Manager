<?php
/**
 * Token service tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Token;

if ( ! function_exists( __NAMESPACE__ . '\\hash_equals' ) ) {
        /**
         * Capture hash_equals calls for constant-time verification assertions.
         *
         * @param string $known_string Expected value.
         * @param string $user_string  User supplied value.
         */
        function hash_equals( $known_string, $user_string ) {
                if ( ! isset( $GLOBALS['fbm_token_hash_equals_calls'] ) || ! is_array( $GLOBALS['fbm_token_hash_equals_calls'] ) ) {
                        $GLOBALS['fbm_token_hash_equals_calls'] = array();
                }

                $GLOBALS['fbm_token_hash_equals_calls'][] = array(
                        'known' => $known_string,
                        'user'  => $user_string,
                );

                return \hash_equals( $known_string, $user_string );
        }
}

namespace FBM\Tests\Token;

use FoodBankManager\Token\TokenRepository;
use FoodBankManager\Token\TokenService;
use PHPUnit\Framework\TestCase;

use function array_filter;
use function hash_hmac;
use function preg_match;
use function strpos;
use function substr_replace;

/**
 * Token service unit tests.
 *
 * @covers \FoodBankManager\Token\TokenService
 * @covers \FoodBankManager\Token\Token
 */
final class TokenServiceTest extends TestCase {

        /**
         * System under test.
         *
         * @var TokenService
         */
        private TokenService $service;

        /**
         * Repository under test.
         *
         * @var TokenRepository
         */
        private TokenRepository $repository;

        /**
         * Lightweight wpdb stub.
         *
         * @var \wpdb
         */
        private \wpdb $wpdb;

        /**
         * Prepare shared fixtures.
         */
        protected function setUp(): void {
                parent::setUp();

                $this->wpdb        = new \wpdb();
                $this->repository  = new TokenRepository( $this->wpdb );
                $this->service     = new TokenService( $this->repository, 'unit-current-secret', 'unit-previous-secret' );
                $GLOBALS['fbm_token_hash_equals_calls'] = array();
        }

        /**
         * Ensure issued tokens round-trip via verification.
         */
        public function test_issue_and_verify_round_trip(): void {
                $member_id = 101;
                $token     = $this->service->issue( $member_id );

                $this->assertSame( 1, preg_match( '/^FBM1:[A-Za-z0-9_-]{8,}$/', $token ) );
                $this->assertArrayHasKey( $member_id, $this->wpdb->tokens );
                $this->assertSame(
                        hash_hmac( 'sha256', $token, 'unit-current-secret' ),
                        $this->wpdb->tokens[ $member_id ]['token_hash']
                );

                $this->assertSame( $member_id, $this->service->verify( $token . "\r\n" ) );
        }

        /**
         * Tampering with a single byte must invalidate the token.
         */
        public function test_verify_rejects_single_byte_mutation(): void {
                $member_id = 202;
                $token     = $this->service->issue( $member_id );

                $separator = strpos( $token, ':' );
                $index     = false === $separator ? 0 : $separator + 1;
                $original  = $token[ $index ];
                $swap      = 'A' === $original ? 'B' : 'A';
                $mutated   = substr_replace( $token, $swap, $index, 1 );

                $this->assertNull( $this->service->verify( $mutated ) );
        }

        /**
         * Revoked tokens must fail verification.
         */
        public function test_revoked_token_fails_verification(): void {
                $member_id = 303;
                $token     = $this->service->issue( $member_id );

                $this->assertTrue( $this->service->revoke( $member_id ) );
                $this->assertNull( $this->service->verify( $token ) );
        }

        /**
         * Valid verification exercises constant-time equality checks.
         */
        public function test_verify_exercises_constant_time_path(): void {
                $member_id = 404;
                $token     = $this->service->issue( $member_id );

                $this->assertSame( $member_id, $this->service->verify( $token ) );

                $calls = $GLOBALS['fbm_token_hash_equals_calls'] ?? array();
                $this->assertNotEmpty( $calls, 'Expected hash_equals calls to be recorded.' );

                $version_checks = array_filter(
                        $calls,
                        static function ( array $call ): bool {
                                return isset( $call['known'], $call['user'] )
                                        && ( 'v1' === $call['known'] || 'v1' === $call['user'] );
                        }
                );

                $this->assertNotEmpty( $version_checks, 'Expected token version comparison to use hash_equals.' );

                $token_hash = $this->wpdb->tokens[ $member_id ]['token_hash'] ?? '';

                $hash_checks = array_filter(
                        $calls,
                        static function ( array $call ) use ( $token_hash ): bool {
                                return isset( $call['known'], $call['user'] )
                                        && ( $token_hash === $call['known'] || $token_hash === $call['user'] );
                        }
                );

                $this->assertNotEmpty( $hash_checks, 'Expected stored token hash comparison to use hash_equals.' );
        }

        /**
         * Tokens issued with a previous secret remain valid via fallback verification.
         */
        public function test_previous_secret_tokens_verify_successfully(): void {
                $member_id = 505;

                $legacy_service = new TokenService( $this->repository, 'unit-previous-secret', '' );
                $token          = $legacy_service->issue( $member_id );

                $this->assertSame( $member_id, $this->service->verify( $token ) );
        }
}
