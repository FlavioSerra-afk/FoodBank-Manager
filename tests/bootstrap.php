<?php
declare(strict_types=1);

// Composer + project autoload
require_once __DIR__ . '/../vendor/autoload.php';

// Load deterministic function shims & stubs
require_once __DIR__ . '/Support/WPStubs.php';
require_once __DIR__ . '/Support/ScreenStub.php';
require_once __DIR__ . '/Support/WPDBStub.php';

// Reset globals before each test run
$GLOBALS['fbm_test_calls'] = [
    'add_menu_page'    => [],
    'add_submenu_page' => [],
];
$GLOBALS['fbm_test_screen_id'] = null;
$GLOBALS['fbm_test_transients'] = [];
$GLOBALS['fbm_test_current_user_id'] = 0;

// Remove any stale cache file just in case
$cache = __DIR__ . '/../.phpunit.result.cache';
if (file_exists($cache)) { @unlink($cache); }

// Minimal bootstrap for pure unit tests (no WordPress).
ob_start();

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

if ( ! function_exists( 'current_user_can' ) ) {
        function current_user_can( string $cap ): bool {
                if ( $cap === 'fb_manage_dashboard' ) {
                        return (bool) ( $GLOBALS['fbm_can_dashboard'] ?? true );
                }
                return true;
        }
}

if ( ! function_exists( 'add_query_arg' ) ) {
        function add_query_arg( $key, $value = null, string $url = '' ) {
                if ( is_array( $key ) ) {
                        $url = is_string( $value ) ? $value : '';
                        return $url . '?' . http_build_query( $key );
                }
                return $url . '?' . urlencode( (string) $key ) . '=' . urlencode( (string) $value );
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

if ( ! function_exists( 'esc_url_raw' ) ) {
        function esc_url_raw( $url ) {
                return filter_var( $url, FILTER_SANITIZE_URL );
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

if ( ! function_exists( 'get_transient' ) ) {
        function get_transient( $key ) {
                return $GLOBALS['fbm_test_transients'][ $key ] ?? false;
        }
}
if ( ! function_exists( 'set_transient' ) ) {
        function set_transient( $key, $value, $expiration = 0 ) {
                $GLOBALS['fbm_test_transients'][ $key ] = $value;
                return true;
        }
}

if ( ! function_exists( 'checked' ) ) {
        function checked( $a, $b = true ) {
                return $a === $b ? ' checked="checked"' : '';
        }
}
if ( ! function_exists( 'selected' ) ) {
        function selected( $a, $b ) {
                return $a === $b ? ' selected="selected"' : '';
        }
}
if ( ! function_exists( 'number_format_i18n' ) ) {
        function number_format_i18n( $n ) {
                return (string) $n;
        }
}
if ( ! function_exists( 'esc_attr__' ) ) {
        function esc_attr__( string $text, string $domain = 'default' ): string {
                return $text;
        }
}

if ( ! function_exists( 'get_current_user_id' ) ) {
        function get_current_user_id(): int {
                return (int) ( $GLOBALS['fbm_test_current_user_id'] ?? 0 );
        }
}
