<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Attendance\AttendanceRepo;
use FBM\Tests\Support\WPDBStub;

final class AttendanceRepoDailyCountsTest extends TestCase {
    public function setUp(): void {
        global $wpdb;
        $wpdb = new WPDBStub();
    }

    public function testCountsLength(): void {
        $today = new DateTimeImmutable('today', new DateTimeZone('UTC'));
        $this->assertCount(24, AttendanceRepo::daily_present_counts( $today ));
        $seven = $today->sub(new DateInterval('P6D'));
        $this->assertCount(7, AttendanceRepo::daily_present_counts( $seven ));
        $thirty = $today->sub(new DateInterval('P29D'));
        $this->assertCount(30, AttendanceRepo::daily_present_counts( $thirty ));
    }
}
