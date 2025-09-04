<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Attendance\AttendanceRepo;
use FBM\Tests\Support\WPDBStub;

final class AttendanceRepoFilterTest extends TestCase {
    public function setUp(): void {
        global $wpdb;
        $wpdb = new WPDBStub();
    }

    public function testFiltersPrepared(): void {
        AttendanceRepo::count_present( '2025-09-01 00:00:00', array(
            'event' => '42',
            'type' => 'delivery',
            'policy_only' => true,
        ) );
        global $wpdb;
        $this->assertStringContainsString( 'event_id = %d', $wpdb->last_sql );
        $this->assertStringContainsString( 'type = %s', $wpdb->last_sql );
        $this->assertStringContainsString( 'policy_breach = 1', $wpdb->last_sql );
        $this->assertSame( array( '2025-09-01 00:00:00', 42, 'delivery' ), $wpdb->last_args );
    }
}
