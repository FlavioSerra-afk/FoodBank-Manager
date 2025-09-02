<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Attendance\AttendanceRepo;

final class AttendanceRepoTest extends TestCase {
	public function testPeopleSummaryExcludesVoidedByDefault(): void {
		global $wpdb;
		$wpdb = new class() {
			public $prefix   = 'wp_';
			public $prepared = array();
			public function prepare( $sql, $args ) {
				$this->prepared[] = $sql;
				return $sql; }
			public function get_results( $sql, $output ) {
				return array(); }
			public function get_var( $sql ) {
				return 0; }
		};
		AttendanceRepo::peopleSummary(
			array(
				'range_from' => '2024-01-01 00:00:00',
				'range_to'   => '2024-01-31 00:00:00',
			)
		);
		$this->assertStringContainsString( 't.is_void = 0', $wpdb->prepared[0] );
		$wpdb->prepared = array();
		AttendanceRepo::peopleSummary(
			array(
				'range_from'     => '2024-01-01 00:00:00',
				'range_to'       => '2024-01-31 00:00:00',
				'include_voided' => true,
			)
		);
		$this->assertStringNotContainsString( 't.is_void = 0', $wpdb->prepared[0] );
	}
}
