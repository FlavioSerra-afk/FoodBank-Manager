<?php
/**
 * Token service tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Token;

use FoodBankManager\Token\TokenRepository;
use FoodBankManager\Token\TokenService;
use PHPUnit\Framework\TestCase;

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
	 * Encode binary data using base64url for assertions.
	 *
	 * @param string $data Raw binary fixture payload.
	 */
	private function encode_base64url( string $data ): string {
		return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Test helper to mirror production encoding.
	}
}
