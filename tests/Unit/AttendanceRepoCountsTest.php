<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Attendance\AttendanceRepo;
use FBM\Tests\Support\WPDBStub;

final class AttendanceRepoCountsTest extends TestCase {
    public function setUp(): void {
        global $wpdb;
        $wpdb = new WPDBStub();
    }

    public function testCountsReturnInts(): void {
        $this->assertSame( 0, AttendanceRepo::count_present( '2025-09-01 00:00:00' ) );
        $this->assertSame( 0, AttendanceRepo::count_unique_households( '2025-09-01 00:00:00' ) );
        $this->assertSame( 0, AttendanceRepo::count_no_shows( '2025-09-01 00:00:00' ) );
        $types = AttendanceRepo::count_by_type( '2025-09-01 00:00:00' );
        $this->assertSame( array( 'in_person' => 0, 'delivery' => 0 ), $types );
        $this->assertSame( 0, AttendanceRepo::count_voided( '2025-09-01 00:00:00' ) );
    }
}
