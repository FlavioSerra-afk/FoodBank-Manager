<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Attendance\AttendanceRepo;
use FBM\Tests\Support\WPDBStub;

final class AttendanceRepoCompatTest extends TestCase {
    protected function setUp(): void {
        global $wpdb;
        $wpdb = new WPDBStub();
    }

    public function testCamelCaseProxies(): void {
        $since   = new DateTimeImmutable('2025-09-01 00:00:00');
        $filters = array( 'event' => '42' );

        $this->assertSame(
            AttendanceRepo::counts( $since, $filters ),
            AttendanceRepo::getCounts( $since, $filters )
        );

        $this->assertSame(
            AttendanceRepo::daily_counts( $since, $filters ),
            AttendanceRepo::getDailyCounts( $since, $filters )
        );

        $this->assertSame(
            AttendanceRepo::filters_prepared( $filters ),
            AttendanceRepo::filtersPrepared( $filters )
        );

        $args = array(
            'range_from' => '2025-01-01',
            'range_to'   => '2025-01-31',
        );

        $this->assertSame(
            AttendanceRepo::people_summary( $args ),
            AttendanceRepo::peopleSummary( $args )
        );
    }
}

