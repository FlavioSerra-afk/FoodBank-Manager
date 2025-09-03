<?php
declare(strict_types=1);
// Minimal bootstrap for pure unit tests (no WordPress).
ob_start();
$vendor = __DIR__ . '/../vendor/autoload.php';
if ( file_exists( $vendor ) ) {
	require $vendor;
}

if ( ! defined( 'FBM_KEK_BASE64' ) ) {
	if ( ! defined( 'SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES' ) ) {
		define( 'SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES', 32 );
	}
	if ( ! defined( 'SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES' ) ) {
		define( 'SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES', 24 );
	}
	if ( ! defined( 'SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_ABYTES' ) ) {
		define( 'SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_ABYTES', 16 );
	}
	define( 'FBM_KEK_BASE64', base64_encode( random_bytes( SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES ) ) );
}

if ( ! function_exists( 'wp_salt' ) ) {
	function wp_salt( string $scheme = 'auth' ): string {
		return 'test-salt';
	}
}

if ( ! function_exists( 'wp_json_encode' ) ) {
	function wp_json_encode( $data ) {
		return json_encode( $data );
	}
}

if ( ! function_exists( 'current_time' ) ) {
	function current_time( string $type, bool $gmt = false ) {
		return gmdate( 'Y-m-d H:i:s' );
	}
}

if ( ! function_exists( 'get_option' ) ) {
	function get_option( string $key, $default = false ) {
		global $fbm_test_options;
		return $fbm_test_options[ $key ] ?? $default;
	}
}

if ( ! function_exists( 'update_option' ) ) {
	function update_option( string $key, $value ) {
		global $fbm_test_options;
		$fbm_test_options[ $key ] = $value;
		return true;
	}
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
	function sanitize_text_field( $str ) {
		return trim( strip_tags( (string) $str ) );
	}
}

if ( ! function_exists( 'sanitize_email' ) ) {
	function sanitize_email( $email ) {
		return filter_var( $email, FILTER_SANITIZE_EMAIL );
	}
}

if ( ! function_exists( 'is_email' ) ) {
	function is_email( $email ) {
		return (bool) filter_var( $email, FILTER_VALIDATE_EMAIL );
	}
}

if ( ! function_exists( 'sanitize_key' ) ) {
        function sanitize_key( $key ) {
                return preg_replace( '/[^a-z0-9_]/', '', strtolower( (string) $key ) );
        }
}

if ( ! function_exists( 'absint' ) ) {
        function absint( $value ) {
                return abs( (int) $value );
        }
}

if ( ! function_exists( 'wp_kses_post' ) ) {
	function wp_kses_post( $data ) {
		return $data;
	}
}
