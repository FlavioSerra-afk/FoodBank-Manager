<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Security;

if ( ! \extension_loaded( 'sodium' ) && \class_exists( '\\ParagonIE_Sodium_Compat' ) ) {
        // sodium_compat provides the sodium_* functions
}

class Crypto {

	private const KEK_CONSTANT = 'FBM_KEK_BASE64';

	private static function get_kek(): ?string {
		$kek_b64 = defined( self::KEK_CONSTANT ) ? constant( self::KEK_CONSTANT ) : null;
		if ( ! $kek_b64 ) {
			\do_action( 'fbm_crypto_missing_kek' );
			return null;
		}
		$kek = base64_decode( (string) $kek_b64, true );
		if ( $kek === false || strlen( $kek ) !== SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES ) {
			\do_action( 'fbm_crypto_missing_kek' );
			return null;
		}
		return $kek;
	}

        private static function ensure_sodium(): void {
                if ( ! \function_exists( 'sodium_crypto_aead_xchacha20poly1305_ietf_encrypt' ) ) {
                        if ( \function_exists( 'add_action' ) ) {
                                \add_action(
                                        'admin_notices',
                                        static function () {
                                                echo '<div class="notice notice-error"><p>' . \esc_html__( 'Sodium extension not available; encryption disabled.', 'foodbank-manager' ) . '</p></div>';
                                        }
                                );
                        }
                        throw new \RuntimeException( 'Sodium library not available' );
                }
        }

        public static function encryptSensitive( array $map ): string {
                $kek = self::get_kek();
                if ( ! $kek ) {
                        return '';
                }
                self::ensure_sodium();
                $dek       = random_bytes( SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES );
                $nonce     = random_bytes( SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES );
                $plaintext = wp_json_encode( $map );
                $cipher    = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt( $plaintext, '', $nonce, $dek );
                $wrapped   = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt( $dek, '', $nonce, $kek );
                return base64_encode( $nonce . $wrapped . $cipher );
        }

	public static function decryptSensitive( string $blob ): array {
		$kek = self::get_kek();
		if ( ! $kek || $blob === '' ) {
			return array();
		}
                $decoded = base64_decode( $blob, true );
                if ( $decoded === false ) {
                        return array();
                }
                self::ensure_sodium();
                $nonce          = substr( $decoded, 0, SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES );
                $offset         = SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES;
                $wrapped_length = SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES + SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_ABYTES;
		$wrapped        = substr( $decoded, $offset, $wrapped_length );
		$cipher         = substr( $decoded, $offset + $wrapped_length );
		$dek            = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt( $wrapped, '', $nonce, $kek );
		if ( $dek === false ) {
			return array();
		}
		$plain = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt( $cipher, '', $nonce, $dek );
		if ( $plain === false ) {
			return array();
		}
		$decoded_map = json_decode( $plain, true );
		return is_array( $decoded_map ) ? $decoded_map : array();
	}
}
