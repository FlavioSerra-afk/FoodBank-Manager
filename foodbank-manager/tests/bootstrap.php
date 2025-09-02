<?php
declare(strict_types=1);
// Minimal bootstrap for pure unit tests (no WordPress).
$vendor = __DIR__ . '/../vendor/autoload.php';
if (file_exists($vendor)) {
    require $vendor;
}

if (! defined('FBM_KEK_BASE64')) {
    if (! defined('SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES')) {
        define('SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES', 32);
    }
    if (! defined('SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES')) {
        define('SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES', 24);
    }
    if (! defined('SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_ABYTES')) {
        define('SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_ABYTES', 16);
    }
    define('FBM_KEK_BASE64', base64_encode(random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES)));
}

if (! function_exists('wp_salt')) {
    function wp_salt(string $scheme = 'auth'): string {
        return 'test-salt';
    }
}

if (! function_exists('wp_json_encode')) {
    function wp_json_encode($data) {
        return json_encode($data);
    }
}
