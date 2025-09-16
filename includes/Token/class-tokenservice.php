<?php
/**
 * Token service.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Token;

use RuntimeException;

use function count;
use function explode;
use function function_exists;
use function gmdate;
use function hash;
use function hash_equals;
use function hash_hmac;
use function is_array;
use function is_string;
use function random_bytes;
use function rtrim;
use function strtr;

/**
 * Issues and validates signed access tokens.
 */
final class TokenService {

	private const TOKEN_VERSION  = 'v1';
	private const SIGNING_SCHEME = 'fbm-token-sign';
	private const STORAGE_SCHEME = 'fbm-token-store';

		/**
		 * Token repository.
		 *
		 * @var TokenRepository
		 */
	private TokenRepository $repository;

		/**
		 * Signing secret.
		 *
		 * @var string
		 */
	private string $signing_key;

		/**
		 * Storage hash secret.
		 *
		 * @var string
		 */
	private string $storage_key;

		/**
		 * Constructor.
		 *
		 * @param TokenRepository $repository Token repository implementation.
		 * @param string|null     $signing_key Optional signing secret override.
		 * @param string|null     $storage_key Optional storage secret override.
		 */
	public function __construct( TokenRepository $repository, ?string $signing_key = null, ?string $storage_key = null ) {
			$this->repository  = $repository;
			$this->signing_key = $signing_key ?? self::derive_key( self::SIGNING_SCHEME, 'fbm-signing-default' );
			$this->storage_key = $storage_key ?? self::derive_key( self::STORAGE_SCHEME, 'fbm-storage-default' );
	}

		/**
		 * Issue a new token for the provided member identifier.
		 *
		 * @param int $member_id Member identifier.
		 *
		 * @throws RuntimeException When the token cannot be persisted.
		 */
	public function issue( int $member_id ): string {
			$payload   = $this->encode_base64url( random_bytes( 32 ) );
			$signature = $this->sign_payload( $payload );
			$token     = self::TOKEN_VERSION . '.' . $payload . '.' . $signature;
			$issued_at = gmdate( 'Y-m-d H:i:s' );

			$hash = $this->hash_for_storage( $token );

		if ( ! $this->repository->persist_active( $member_id, $hash, $issued_at, self::TOKEN_VERSION ) ) {
				throw new RuntimeException( 'Unable to persist member token.' );
		}

			return $token;
	}

		/**
		 * Verify a raw token string and return the associated member identifier.
		 *
		 * @param string $raw_token Token provided by the caller.
		 *
		 * @return int|null Member identifier when valid; otherwise null.
		 */
	public function verify( string $raw_token ): ?int {
			$parts = explode( '.', $raw_token );
		if ( 3 !== count( $parts ) ) {
				return null;
		}

			list($version, $payload, $signature) = $parts;
		if ( '' === $version || '' === $payload || '' === $signature ) {
				return null;
		}

			$expected_signature = $this->sign_payload_for_version( $version, $payload );
		if ( null === $expected_signature ) {
				return null;
		}

		if ( ! hash_equals( $expected_signature, $signature ) ) {
				return null;
		}

			$token_hash = $this->hash_for_storage_for_version( $version, $raw_token );
		if ( null === $token_hash ) {
				return null;
		}

		$record = $this->repository->find_active_by_hash( $token_hash );

		if ( null === $record ) {
			return null;
		}

		if ( ! hash_equals( $record['version'], $version ) ) {
			return null;
		}

		if ( ! hash_equals( $record['token_hash'], $token_hash ) ) {
				return null;
		}

			return (int) $record['member_id'];
	}
		/**
		 * Revoke all active tokens for the provided member.
		 *
		 * @param int $member_id Member identifier.
		 */
	public function revoke( int $member_id ): bool {
			$revoked_at = gmdate( 'Y-m-d H:i:s' );

			return $this->repository->revoke_member( $member_id, $revoked_at );
	}

		/**
		 * Compute a base64url-safe encoding.
		 *
		 * @param string $data Binary payload to encode.
		 */
	private function encode_base64url( string $data ): string {
		return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Generate portable token segments.
	}

		/**
		 * Create a signature for the provided payload.
		 *
		 * @param string $payload Encoded payload.
		 */
	private function sign_payload( string $payload ): string {
			$signature = hash_hmac( 'sha256', $payload, $this->signing_key, true );

			return $this->encode_base64url( $signature );
	}


		/**
		 * Resolve the expected signature for the provided payload version.
		 *
		 * @param string $version Token version identifier.
		 * @param string $payload Encoded payload.
		 */
	private function sign_payload_for_version( string $version, string $payload ): ?string {
		switch ( $version ) {
			case 'v1':
				return $this->sign_payload( $payload );
		}

		return null;
	}
		/**
		 * Hash a token for storage.
		 *
		 * @param string $token Raw token.
		 */
	private function hash_for_storage( string $token ): string {
			return hash_hmac( 'sha256', $token, $this->storage_key );
	}


		/**
		 * Hash a token for storage using the scheme associated with its version.
		 *
		 * @param string $version Token version identifier.
		 * @param string $token   Raw token.
		 */
	private function hash_for_storage_for_version( string $version, string $token ): ?string {
		switch ( $version ) {
			case 'v1':
				return $this->hash_for_storage( $token );
		}

		return null;
	}
		/**
		 * Resolve a secret key from WordPress salts when available.
		 *
		 * @param string $scheme   WordPress salt scheme identifier.
		 * @param string $fallback Fallback seed when salts are unavailable.
		 */
	private static function derive_key( string $scheme, string $fallback ): string {
		if ( function_exists( 'wp_salt' ) ) {
				$salt = wp_salt( $scheme );
			if ( is_string( $salt ) && '' !== $salt ) {
				return $salt;
			}
		}

			return hash( 'sha256', $fallback . '|' . __FILE__ );
	}
}
