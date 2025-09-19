<?php
/**
 * Cryptography helpers for envelope encryption.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Crypto;

use RuntimeException;
use function base64_decode; // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- Envelope storage requires reversible encoding.
use function base64_encode; // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Envelope storage requires reversible encoding.
use function defined;
use function hash_equals;
use function hash_hkdf;
use function is_array;
use function is_string;
use function json_decode;
use function openssl_decrypt;
use function openssl_encrypt;
use function random_bytes;
use function site_url;
use function strlen;
use function str_starts_with;
use function substr;
use function wp_json_encode;
use const OPENSSL_RAW_DATA;

/**
 * Provides envelope encryption primitives backed by AES-256-GCM.
 */
final class Crypto {
	private const ENVELOPE_VERSION = '1';
	private const ENVELOPE_ALG     = 'AES-256-GCM';
	private const MASTER_INFO      = 'fbm-kms-v1';
	private const DEK_AAD_SUFFIX   = '|dek';

	/**
	 * Cached master key material.
	 *
	 * @var string|null
	 */
	private static ?string $master_key = null;

	/**
	 * Disallow instantiation.
	 */
	private function __construct() {}

	/**
	 * Determine whether a stored value already contains an FBM envelope.
	 *
	 * @param string $value Potentially encoded value.
	 *
	 * @return bool
	 */
	public static function is_envelope( string $value ): bool {
			return str_starts_with( $value, '{"v":"' . self::ENVELOPE_VERSION . '"' );
	}

	/**
	 * Encrypt plaintext into a JSON envelope string using per-record AEAD.
	 *
	 * @param string $plaintext  Plaintext value to encrypt.
	 * @param string $table      Logical table name for AAD binding.
	 * @param string $column     Column identifier within the table.
	 * @param string $record_id  Unique record identifier for AAD binding.
	 *
	 * @throws RuntimeException When encryption fails.
	 */
	public static function encrypt( string $plaintext, string $table, string $column, string $record_id ): string {
			$master_key = self::master_key();
			$aad        = self::aad( $table, $column, $record_id );

			$dek        = self::random_bytes( 32 );
			$data_nonce = self::random_bytes( 12 );
			$dek_nonce  = self::random_bytes( 12 );

			$data_tag = '';
			$cipher   = openssl_encrypt( $plaintext, 'aes-256-gcm', $dek, OPENSSL_RAW_DATA, $data_nonce, $data_tag, $aad );
		if ( ! is_string( $cipher ) ) {
				throw new RuntimeException( 'Unable to encrypt payload.' );
		}

			$dek_tag = '';
			$wrapped = openssl_encrypt( $dek, 'aes-256-gcm', $master_key, OPENSSL_RAW_DATA, $dek_nonce, $dek_tag, $aad . self::DEK_AAD_SUFFIX );
		if ( ! is_string( $wrapped ) ) {
				throw new RuntimeException( 'Unable to wrap data encryption key.' );
		}

			$envelope = array(
				'v'     => self::ENVELOPE_VERSION,
				'alg'   => self::ENVELOPE_ALG,
				'dek'   => self::encode_b64( $dek_nonce . $dek_tag . $wrapped ),
				'nonce' => self::encode_b64( $data_nonce ),
				'tag'   => self::encode_b64( $data_tag ),
				'ct'    => self::encode_b64( $cipher ),
				'aad'   => '',
			);

			$encoded = wp_json_encode( $envelope );
			if ( ! is_string( $encoded ) ) {
					throw new RuntimeException( 'Unable to encode envelope payload.' );
			}

			return $encoded;
	}

	/**
	 * Attempt to decrypt an FBM envelope.
	 *
	 * @param string $payload   Stored envelope JSON.
	 * @param string $table     Logical table name for AAD binding.
	 * @param string $column    Column identifier within the table.
	 * @param string $record_id Unique record identifier for AAD binding.
	 *
	 * @return string|null
	 */
	public static function decrypt( string $payload, string $table, string $column, string $record_id ): ?string {
		if ( ! self::is_envelope( $payload ) ) {
				return $payload;
		}

			$decoded = json_decode( $payload, true );
		if ( ! is_array( $decoded ) ) {
				return null;
		}

			$version = isset( $decoded['v'] ) ? (string) $decoded['v'] : '';
			$alg     = isset( $decoded['alg'] ) ? (string) $decoded['alg'] : '';

		if ( self::ENVELOPE_VERSION !== $version || self::ENVELOPE_ALG !== $alg ) {
				return null;
		}

			$dek_blob = isset( $decoded['dek'] ) ? self::decode_b64( (string) $decoded['dek'] ) : null;
			$nonce    = isset( $decoded['nonce'] ) ? self::decode_b64( (string) $decoded['nonce'] ) : null;
			$tag      = isset( $decoded['tag'] ) ? self::decode_b64( (string) $decoded['tag'] ) : null;
			$cipher   = isset( $decoded['ct'] ) ? self::decode_b64( (string) $decoded['ct'] ) : null;

		if ( null === $dek_blob || null === $nonce || null === $tag || null === $cipher ) {
				return null;
		}

		if ( strlen( $dek_blob ) < 12 + 16 + 1 ) {
				return null;
		}

			$dek_nonce = substr( $dek_blob, 0, 12 );
			$dek_tag   = substr( $dek_blob, 12, 16 );
			$dek_ct    = substr( $dek_blob, 28 );

			$aad        = self::aad( $table, $column, $record_id );
			$master_key = self::master_key();

			$dek = openssl_decrypt( $dek_ct, 'aes-256-gcm', $master_key, OPENSSL_RAW_DATA, $dek_nonce, $dek_tag, $aad . self::DEK_AAD_SUFFIX );
		if ( ! is_string( $dek ) || strlen( $dek ) !== 32 ) {
				return null;
		}

			$plaintext = openssl_decrypt( $cipher, 'aes-256-gcm', $dek, OPENSSL_RAW_DATA, $nonce, $tag, $aad );
		if ( ! is_string( $plaintext ) ) {
				return null;
		}

			return $plaintext;
	}

	/**
	 * Compare two strings using constant-time logic.
	 *
	 * @param string $known Known value.
	 * @param string $user  User-provided value.
	 *
	 * @return bool
	 */
	public static function constant_time_equals( string $known, string $user ): bool {
			return hash_equals( $known, $user );
	}

	/**
	 * Derive and cache the HKDF master key.
	 *
	 * @throws RuntimeException When key derivation fails.
	 */
	private static function master_key(): string {
		if ( null !== self::$master_key ) {
				return self::$master_key;
		}

			$auth_key = defined( 'AUTH_KEY' ) ? (string) AUTH_KEY : '';
			$secure   = defined( 'SECURE_AUTH_KEY' ) ? (string) SECURE_AUTH_KEY : '';
			$combined = $auth_key . $secure;
			$salt     = site_url();
			$derived  = hash_hkdf( 'sha256', $combined, 32, self::MASTER_INFO, $salt );

		if ( ! is_string( $derived ) ) {
				throw new RuntimeException( 'Unable to derive master encryption key.' );
		}

			self::$master_key = $derived;

			return self::$master_key;
	}

	/**
	 * Construct the authenticated data string for a field.
	 *
	 * @param string $table     Logical table name.
	 * @param string $column    Column identifier.
	 * @param string $record_id Unique record identifier.
	 *
	 * @return string
	 */
	private static function aad( string $table, string $column, string $record_id ): string {
			return site_url() . '|fbm|v1|' . $table . '|' . $column . '|' . $record_id;
	}

	/**
	 * Generate cryptographically secure random bytes.
	 *
	 * @param int $length Number of bytes to generate.
	 *
	 * @return string
	 */
	private static function random_bytes( int $length ): string {
			return random_bytes( $length );
	}

	/**
	 * Base64 encode binary payloads.
	 *
	 * @param string $value Value to encode.
	 *
	 * @return string
	 */
	private static function encode_b64( string $value ): string {
			return base64_encode( $value ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Envelope storage requires reversible encoding.
	}

	/**
	 * Strict base64 decode helper.
	 *
	 * @param string $value Value to decode.
	 *
	 * @return string|null
	 */
	private static function decode_b64( string $value ): ?string {
			$decoded = base64_decode( $value, true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- Envelope storage requires reversible encoding.

			return false === $decoded ? null : $decoded;
	}
}
