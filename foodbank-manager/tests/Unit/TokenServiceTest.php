<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Attendance\TokenService;

final class TokenServiceTest extends TestCase {
    public function testValidToken(): void {
        $token = TokenService::generate(123);
        $data = TokenService::validate($token);
        $this->assertSame(123, $data['a']);
    }

    public function testBadTokenFails(): void {
        $token = TokenService::generate(123) . 'x';
        $this->assertNull(TokenService::validate($token));
    }

    public function testFreshnessWindow(): void {
        $token = TokenService::generate(123);
        $this->assertNotNull(TokenService::validate($token, 10));
        $parts = explode('.', $token);
        $payload = json_decode(base64_decode($parts[0]), true);
        $payload['t'] = time() - 20;
        $payload_json = wp_json_encode($payload);
        $expired = base64_encode($payload_json) . '.' . hash_hmac('sha256', $payload_json, wp_salt('auth'));
        $this->assertNull(TokenService::validate($expired, 10));
    }
}
