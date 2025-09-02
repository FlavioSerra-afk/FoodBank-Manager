<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Security\Crypto;

final class SecurityCryptoTest extends TestCase {
    protected function setUp(): void {
        if (! function_exists('sodium_crypto_aead_xchacha20poly1305_ietf_encrypt')) {
            $this->markTestSkipped('libsodium missing');
        }
    }
    public function testRoundTrip(): void {
        $data = ['foo' => 'bar'];
        $blob = Crypto::encryptSensitive($data);
        $this->assertNotSame('', $blob);
        $out = Crypto::decryptSensitive($blob);
        $this->assertSame($data, $out);
    }

    public function testNonDeterministic(): void {
        $data = ['foo' => 'bar'];
        $blob1 = Crypto::encryptSensitive($data);
        $blob2 = Crypto::encryptSensitive($data);
        $this->assertNotSame($blob1, $blob2);
    }

    public function testTamperDetection(): void {
        $data = ['foo' => 'bar'];
        $blob = Crypto::encryptSensitive($data);
        $blob[10] = chr(ord($blob[10]) ^ 1);
        $out = Crypto::decryptSensitive($blob);
        $this->assertSame([], $out);
    }
}
