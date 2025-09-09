<?php
declare(strict_types=1);

namespace FoodBankManager\Tests\Unit\Attendance;

use FBM\Attendance\TicketService;
use PHPUnit\Framework\TestCase;

if (!defined('FBM_KEK_BASE64')) {
    define('FBM_KEK_BASE64', base64_encode(str_repeat('k', 32)));
}

final class TicketServiceTest extends TestCase {
    public function testCreateAndVerify(): void {
        $data = TicketService::createToken(1, 'User@example.com', 1000);
        $this->assertNotEmpty($data['token']);
        $this->assertTrue(TicketService::verifyAgainstHash($data['token'], $data['token_hash']));
        $url = TicketService::ticketUrl($data['token']);
        $this->assertStringContainsString('action=fbm_scan', $url);
        $regen = TicketService::createToken(1, 'user@example.com', 1000);
        $this->assertNotSame($data['token'], $regen['token']);
    }
}
