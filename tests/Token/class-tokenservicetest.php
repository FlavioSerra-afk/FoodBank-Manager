<?php
/**
 * Token service tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Token;

if ( ! function_exists( __NAMESPACE__ . '\\hash_equals' ) ) {
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
use function explode;
use function hash_hmac;
use function rtrim;
use function strrev;
use function strtr;

/**
 * Token service unit tests.
 *
 * @covers \FoodBankManager\Token\TokenService
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
		 * @var wpdb
		 */
	private \wpdb $wpdb;

		/**
		 * Prepare shared fixtures.
		 */
        protected function setUp(): void {
                        parent::setUp();

                                $this->wpdb   = new \wpdb();
                        $this->repository = new TokenRepository( $this->wpdb );
                        $this->service    = new TokenService( $this->repository, 'unit-signing-secret', 'unit-storage-secret' );

                        $GLOBALS['fbm_token_hash_equals_calls'] = array();
        }

		/**
		 * Ensure issued tokens include signatures and persisted hashes.
		 */
	public function test_issue_persists_signed_token(): void {
			$member_id = 123;

			$token = $this->service->issue( $member_id );

			$this->assertArrayHasKey( $member_id, $this->wpdb->tokens );
			$this->assertSame( hash_hmac( 'sha256', $token, 'unit-storage-secret' ), $this->wpdb->tokens[ $member_id ]['token_hash'] );
			$this->assertSame( 'v1', $this->wpdb->tokens[ $member_id ]['version'] );

			$parts = explode( '.', $token );
			$this->assertCount( 3, $parts );
			$this->assertSame( 'v1', $parts[0] );

		$expected_signature = $this->encode_base64url( hash_hmac( 'sha256', $parts[1], 'unit-signing-secret', true ) );
		$this->assertSame( $expected_signature, $parts[2] );
	}

	/**
	 * Verifies that a valid token resolves its member identifier.
	 */
	public function test_verify_returns_member_for_valid_token(): void {
		$member_id = 678;
		$token     = $this->service->issue( $member_id );

		$this->assertSame( $member_id, $this->service->verify( $token ) );
	}

	/**
	 * Confirm tampered payloads or signatures are rejected.
	 */
	public function test_verify_rejects_tampered_payload(): void {
			$member_id = 44;
			$token     = $this->service->issue( $member_id );

			$parts  = explode( '.', $token );
		$tampered   = $parts[0] . '.' . strrev( $parts[1] ) . '.' . $parts[2];
		$tampered_2 = $parts[0] . '.' . $parts[1] . '.' . strrev( $parts[2] );

		$this->assertNull( $this->service->verify( $tampered ) );
		$this->assertNull( $this->service->verify( $tampered_2 ) );
	}

        /**
         * Tokens should not verify after being revoked.
         */
        public function test_revoke_prevents_future_verification(): void {
                $member_id = 512;
                $token     = $this->service->issue( $member_id );

                $this->assertSame( $member_id, $this->service->verify( $token ) );

                $this->assertTrue( $this->service->revoke( $member_id ) );

                $this->assertNull( $this->service->verify( $token ) );
        }

        /**
         * Revoking a member without active tokens should report failure.
         */
        public function test_revoke_returns_false_when_no_tokens_found(): void {
                $this->assertFalse( $this->service->revoke( 999 ) );
        }

        /**
         * Ensure verification compares secrets using constant-time checks.
         */
        public function test_verify_uses_constant_time_comparisons(): void {
                $member_id = 2048;
                $token     = $this->service->issue( $member_id );

                $this->assertSame( $member_id, $this->service->verify( $token ) );

                $calls = $GLOBALS['fbm_token_hash_equals_calls'] ?? array();

                $this->assertNotEmpty( $calls, 'Expected hash_equals calls to be recorded.' );

                $version_checks = array_filter(
                        $calls,
                        static function ( array $call ): bool {
                                return 'v1' === $call['known'] || 'v1' === $call['user'];
                        }
                );

                $this->assertNotEmpty( $version_checks, 'Expected token version comparison to use hash_equals.' );

                $token_hash = $this->wpdb->tokens[ $member_id ]['token_hash'] ?? '';

                $hash_checks = array_filter(
                        $calls,
                        static function ( array $call ) use ( $token_hash ): bool {
                                return $token_hash === $call['known'] || $token_hash === $call['user'];
                        }
                );

                $this->assertNotEmpty( $hash_checks, 'Expected stored token hash comparison to use hash_equals.' );
        }

        /**
         * Encode binary data using base64url for assertions.
         *
         * @param string $data Raw binary fixture payload.
	 */
	private function encode_base64url( string $data ): string {
		return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Test helper to mirror production encoding.
	}
}
