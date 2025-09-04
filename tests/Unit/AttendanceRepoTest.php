<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Attendance\AttendanceRepo;
use FBM\Tests\Support\WPDBStub;

final class AttendanceRepoTest extends TestCase {
	public function testPeopleSummaryExcludesVoidedByDefault(): void {
		global $wpdb;
                $wpdb = new WPDBStub();
                AttendanceRepo::people_summary(
                        array(
                                'range_from' => '2024-01-01',
                                'range_to'   => '2024-01-31',
                        )
                );
                $this->assertNotEmpty( $wpdb->prepared );
                $this->assertStringContainsString( 't.is_void = 0', end( $wpdb->prepared ) );
                $wpdb->prepared = array();
                AttendanceRepo::people_summary(
                        array(
                                'range_from'     => '2024-01-01',
                                'range_to'       => '2024-01-31',
                                'include_voided' => true,
                        )
                );
                $this->assertNotEmpty( $wpdb->prepared );
                $this->assertStringNotContainsString( 't.is_void = 0', end( $wpdb->prepared ) );
        }
}
