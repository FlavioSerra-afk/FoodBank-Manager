<?php
/**
 * Token core service.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Token;

use Exception;
use RuntimeException;

use function base64_encode;
use function function_exists;
use function gmdate;
use function hash;
use function hash_hmac;
use function is_string;
use function preg_match;
use function random_bytes;
use function rtrim;
use function str_replace;
use function strtr;
use function trim;

/**
 * Issues, canonicalizes, verifies, and revokes opaque member tokens.
 */
final class Token {

	private const PAYLOAD_PREFIX = 'FBM';
	private const PAYLOAD_VERSION = '1';
	private const STORAGE_VERSION = 'v1';
	private const SALT_CURRENT = 'fbm-token-sign';
	private const SALT_PREVIOUS = 'fbm-token-sign-prev';
	private const FALLBACK_CURRENT = 'fbm-token-current';
	private const FALLBACK_PREVIOUS = 'fbm-token-previous';
	private const HASH_ALGO = 'sha256';

	/**
	 * Token repository.
	 *
	 * @var TokenRepository
	 */
	private TokenRepository $repository;

	/**
	 * Current HMAC secret.
	 *
	 * @var string
	 */
	private string $current_secret;

	/**
	 * Previous HMAC secret (optional).
	 *
	 * @var string
	 */
	private string $previous_secret;

	/**
	 * Constructor.
	 *
	 * @param TokenRepository $repository      Token repository implementation.
	 * @param string|null     $current_secret  Active signing secret.
	 * @param string|null     $previous_secret Previous signing secret (optional).
	 */
	public function __construct( TokenRepository $repository, ?string $current_secret = null, ?string $previous_secret = null ) {
		$this->repository      = $repository;
		$this->current_secret  = $current_secret ?? self::derive_secret( self::SALT_CURRENT, self::FALLBACK_CURRENT, false );
		$this->previous_secret = $previous_secret ?? self::derive_secret( self::SALT_PREVIOUS, self::FALLBACK_PREVIOUS, true );
	}

	/**
	 * Issue a new opaque token for a member.
	 *
	 * @param int                  $member_id Member identifier.
	 * @param array<string, mixed> $meta      Optional issuance metadata.
	 *
	 * @return array{
	 *     payload:string,
	 *     token_hash:string,
	 *     version:string,
	 *     issued_at:string,
	 *     meta:array<string,mixed>
	 * }
	 *
	 * @throws RuntimeException When token persistence fails or entropy generation fails.
	 */
	public function issue( int $member_id, array $meta = array() ): array {
		$payload   = $this->generate_payload();
		$issued_at = gmdate( 'Y-m-d H:i:s' );
		$token_hash = $this->hash_payload( $payload, $this->current_secret );

		if ( ! $this->repository->persist_active( $member_id, $token_hash, $issued_at, self::STORAGE_VERSION, $meta ) ) {
			throw new RuntimeException( 'Unable to persist member token.' );
		}

		return array(
			'payload'    => $payload,
			'token_hash' => $token_hash,
			'version'    => self::STORAGE_VERSION,
			'issued_at'  => $issued_at,
			'meta'       => $meta,
		);
	}

	/**
	 * Canonicalize incoming token payloads.
	 *
	 * @param string $raw Raw token payload provided by the caller.
	 */
	public static function canonicalize( string $raw ): ?string {
                $normalized = str_replace( array( "\r\n", "\r" ), "\n", $raw );
		$trimmed    = rtrim( $normalized );

		if ( '' === $trimmed ) {
			return null;
		}

		if ( 1 !== preg_match( '/^FBM(\d+):[A-Za-z0-9_-]{8,}$/', $trimmed ) ) {
			return null;
		}

		return $trimmed;
	}

	/**
	 * Verify a raw token payload.
	 *
	 * @param string $raw Raw token payload provided by the caller.
	 *
	 * @return array{ok:bool,member_id:?int,reason:string}
	 */
	public function verify( string $raw ): array {
		$canonical = self::canonicalize( $raw );

		if ( null === $canonical ) {
			return array(
				'ok'        => false,
				'member_id' => null,
				'reason'    => 'invalid',
			);
		}

		$storage_version = $this->storage_version_for_payload( $canonical );

		if ( null === $storage_version ) {
			return array(
				'ok'        => false,
				'member_id' => null,
				'reason'    => 'invalid',
			);
		}

		foreach ( $this->hashes_to_attempt( $canonical ) as $token_hash ) {
			$record = $this->repository->find_by_hash( $token_hash );

			if ( null === $record ) {
				continue;
			}

			if ( ! hash_equals( $record['token_hash'], $token_hash ) ) {
				continue;
			}

			if ( ! hash_equals( $record['version'], $storage_version ) ) {
				continue;
			}

			if ( null !== $record['revoked_at'] ) {
				return array(
					'ok'        => false,
					'member_id' => (int) $record['member_id'],
					'reason'    => 'revoked',
				);
			}

			return array(
				'ok'        => true,
				'member_id' => (int) $record['member_id'],
				'reason'    => 'ok',
			);
		}

		return array(
			'ok'        => false,
			'member_id' => null,
			'reason'    => 'invalid',
		);
	}

	/**
	 * Revoke all active tokens for a member.
	 *
	 * @param int $member_id Member identifier.
	 */
	public function revoke( int $member_id ): bool {
		$revoked_at = gmdate( 'Y-m-d H:i:s' );

		return $this->repository->revoke_member( $member_id, $revoked_at );
	}

	/**
	 * Generate a signed payload string.
	 */
	private function generate_payload(): string {
		try {
			$opaque = $this->encode_base64url( random_bytes( 32 ) );
		} catch ( Exception $exception ) {
			throw new RuntimeException( 'Unable to generate token payload.', 0, $exception );
		}

		return self::PAYLOAD_PREFIX . self::PAYLOAD_VERSION . ':' . $opaque;
	}

	/**
	 * Encode binary data using a URL-safe base64 alphabet.
	 *
	 * @param string $data Binary data to encode.
	 */
	private function encode_base64url( string $data ): string {
		return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Portable token encoding.
	}

	/**
	 * Compute a hash for the provided payload using the supplied secret.
	 *
	 * @param string $payload Canonical token payload.
	 * @param string $secret  HMAC secret.
	 */
	private function hash_payload( string $payload, string $secret ): string {
		return hash_hmac( self::HASH_ALGO, $payload, $secret );
	}

	/**
	 * Determine the storage version for a canonical payload.
	 *
	 * @param string $canonical Canonical token payload.
	 */
	private function storage_version_for_payload( string $canonical ): ?string {
		if ( 1 !== preg_match( '/^FBM(\d+):/', $canonical, $matches ) ) {
			return null;
		}

                $version = (string) $matches[1];

		if ( self::PAYLOAD_VERSION !== $version ) {
			return null;
		}

		return self::STORAGE_VERSION;
	}

	/**
	 * Compute hashes to attempt during verification.
	 *
	 * @param string $canonical Canonical token payload.
	 *
	 * @return array<int,string>
	 */
	private function hashes_to_attempt( string $canonical ): array {
		$hashes   = array();
		$hashes[] = $this->hash_payload( $canonical, $this->current_secret );

		if ( '' !== $this->previous_secret && $this->previous_secret !== $this->current_secret ) {
			$hashes[] = $this->hash_payload( $canonical, $this->previous_secret );
		}

		return $hashes;
	}

	/**
	 * Resolve a secret key using WordPress salts when available.
	 *
	 * @param string $scheme      WordPress salt scheme.
	 * @param string $fallback    Fallback seed when salts are unavailable.
	 * @param bool   $allow_empty Allow returning an empty secret when salts are unavailable.
	 */
	private static function derive_secret( string $scheme, string $fallback, bool $allow_empty ): string {
		if ( function_exists( 'wp_salt' ) ) {
			$salt = wp_salt( $scheme );

			if ( is_string( $salt ) ) {
				$trimmed = trim( $salt );

				if ( '' !== $trimmed ) {
					return $trimmed;
				}
			}
		}

		if ( $allow_empty ) {
			return '';
		}

		return hash( self::HASH_ALGO, $fallback . '|' . __FILE__ );
	}
}
