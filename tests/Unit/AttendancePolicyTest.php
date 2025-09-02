<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Attendance\Policy;

final class AttendancePolicyTest extends TestCase {
    public function testBreachWithinDays(): void {
        $now = '2024-01-08 12:00:00';
        $recent = '2024-01-05 12:00:00'; // 3 days ago
        $old = '2023-12-20 12:00:00';
        $this->assertTrue( Policy::is_breach( $recent, 7, $now ) );
        $this->assertFalse( Policy::is_breach( $old, 7, $now ) );
        $this->assertFalse( Policy::is_breach( null, 7, $now ) );
    }
}
