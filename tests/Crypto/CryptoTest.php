<?php
/**
 * Crypto primitives tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Crypto;

use FoodBankManager\Crypto\Crypto;
use PHPUnit\Framework\TestCase;

/**
 * @covers \FoodBankManager\Crypto\Crypto
 */
final class CryptoTest extends TestCase {
        public function test_encrypt_and_decrypt_round_trip(): void {
                $envelope = Crypto::encrypt( 'Plain Member', 'fbm_members', 'first_name', '42' );

                $this->assertTrue( Crypto::is_envelope( $envelope ) );
                $this->assertSame( 'Plain Member', Crypto::decrypt( $envelope, 'fbm_members', 'first_name', '42' ) );
        }

        public function test_decrypt_returns_null_when_ciphertext_tampered(): void {
                $envelope = Crypto::encrypt( 'Sensitive', 'fbm_members', 'first_name', '77' );
                $decoded  = json_decode( $envelope, true );

                $this->assertIsArray( $decoded );
                $this->assertArrayHasKey( 'ct', $decoded );

                $ciphertext = (string) $decoded['ct'];
                $decoded['ct'] = strrev( $ciphertext );

                $mutated = json_encode( $decoded );
                $this->assertIsString( $mutated );

                $this->assertNull( Crypto::decrypt( $mutated, 'fbm_members', 'first_name', '77' ) );
        }

        public function test_decrypt_returns_null_for_wrong_context(): void {
                $envelope = Crypto::encrypt( 'Mismatch', 'fbm_members', 'first_name', '13' );

                $this->assertNull( Crypto::decrypt( $envelope, 'fbm_members', 'first_name', '14' ) );
                $this->assertNull( Crypto::decrypt( $envelope, 'fbm_members', 'last_initial', '13' ) );
                $this->assertNull( Crypto::decrypt( $envelope, 'fbm_tokens', 'token', '13' ) );
        }

        public function test_constant_time_equals_proxies_hash_equals(): void {
                $this->assertTrue( Crypto::constant_time_equals( 'abc', 'abc' ) );
                $this->assertFalse( Crypto::constant_time_equals( 'abc', 'def' ) );
        }
}
