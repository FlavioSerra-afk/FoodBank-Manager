<?php

declare(strict_types=1);

namespace FoodBankManager\Security;

class Crypto
{
    private const KEK_CONSTANT = 'PCC_FBM_KEK_BASE64';

    private static function get_kek(): ?string
    {
        $kek_b64 = defined(self::KEK_CONSTANT) ? constant(self::KEK_CONSTANT) : null;
        if (! $kek_b64) {
            \do_action('fbm_crypto_missing_kek');
            return null;
        }
        $kek = base64_decode((string) $kek_b64, true);
        if ($kek === false || strlen($kek) !== SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES) {
            \do_action('fbm_crypto_missing_kek');
            return null;
        }
        return $kek;
    }

    public static function encryptSensitive(array $map): string
    {
        $kek = self::get_kek();
        if (! $kek) {
            return '';
        }
        $dek   = random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES);
        $nonce = random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);
        $plaintext = wp_json_encode($map);
        $cipher = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt($plaintext, '', $nonce, $dek);
        $wrapped = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt($dek, '', $nonce, $kek);
        return base64_encode($nonce . $wrapped . $cipher);
    }

    public static function decryptSensitive(string $blob): array
    {
        $kek = self::get_kek();
        if (! $kek || $blob === '') {
            return [];
        }
        $decoded = base64_decode($blob, true);
        if ($decoded === false) {
            return [];
        }
        $nonce = substr($decoded, 0, SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);
        $offset = SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES;
        $wrapped_length = SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES + SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_ABYTES;
        $wrapped = substr($decoded, $offset, $wrapped_length);
        $cipher = substr($decoded, $offset + $wrapped_length);
        $dek = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt($wrapped, '', $nonce, $kek);
        if ($dek === false) {
            return [];
        }
        $plain = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt($cipher, '', $nonce, $dek);
        if ($plain === false) {
            return [];
        }
        $decoded_map = json_decode($plain, true);
        return is_array($decoded_map) ? $decoded_map : [];
    }
}
