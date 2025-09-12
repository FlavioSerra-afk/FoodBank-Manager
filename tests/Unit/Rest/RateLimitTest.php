<?php
declare(strict_types=1);

namespace Tests\Unit\Rest;

use FBM\Security\RateLimiter;
use PHPUnit\Framework\TestCase;
use function delete_transient;
use function sanitize_key;

final class RateLimitTest extends TestCase {
    public function testBlocksAndResets(): void {
        $key = 'rate-test';
        $this->assertTrue(RateLimiter::allow($key, 2, 30));
        $this->assertTrue(RateLimiter::allow($key, 2, 30));
        $this->assertFalse(RateLimiter::allow($key, 2, 30));
        delete_transient('fbm_rl_' . sanitize_key($key));
        $this->assertTrue(RateLimiter::allow($key, 2, 30));
    }
}
