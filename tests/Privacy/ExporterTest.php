<?php
/**
 * Coverage for the FoodBank Manager privacy exporter integration.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Privacy;

use FoodBankManager\Privacy\Exporter;
use PHPUnit\Framework\TestCase;
use function array_column;

/**
 * @covers \FoodBankManager\Privacy\Exporter
 */
final class ExporterTest extends TestCase {
        protected function setUp(): void {
                parent::setUp();

                $GLOBALS['wpdb']          = new \wpdb();
                $GLOBALS['wpdb']->members = array(
                        1 => array(
                                'id'                  => 1,
                                'member_reference'    => 'FBM0001',
                                'first_name'          => 'Rosa',
                                'last_initial'        => 'L',
                                'email'               => 'rosa@example.test',
                                'status'              => 'active',
                                'household_size'      => 3,
                                'created_at'          => '2024-01-01 09:00:00',
                                'updated_at'          => '2024-02-01 10:00:00',
                                'activated_at'        => '2024-01-02 12:00:00',
                                'consent_recorded_at' => '2024-01-01 09:05:00',
                        ),
                );

                $GLOBALS['wpdb']->attendance = array(
                        1 => array(
                                'id'               => 1,
                                'member_reference' => 'FBM0001',
                                'collected_at'     => '2024-03-04 12:10:00',
                                'collected_date'   => '2024-03-04',
                                'method'           => 'qr',
                                'note'             => 'Internal note',
                                'recorded_by'      => 42,
                        ),
                );

                $GLOBALS['wpdb']->attendance_overrides = array(
                        1 => array(
                                'id'               => 1,
                                'attendance_id'    => 1,
                                'member_reference' => 'FBM0001',
                                'override_by'      => 99,
                                'override_note'    => 'Approved extra visit',
                                'override_at'      => '2024-03-05 09:00:00',
                        ),
                );
        }

        public function test_export_by_email_returns_member_and_attendance(): void {
                $result = Exporter::export( 'rosa@example.test' );

			$this->assertTrue( $result['done'] );
			$this->assertCount( 3, $result['data'] );

			$member_group = $result['data'][0];
			$this->assertSame( 'foodbank-manager-member', $member_group['group_id'] );
			$member_map = array_column( $member_group['data'], 'value', 'name' );
			$this->assertSame( 'FBM0001', $member_map['Member Reference'] );
			$this->assertSame( 'rosa@example.test', $member_map['Email'] );

			$attendance_group = $result['data'][1];
			$this->assertSame( 'foodbank-manager-attendance', $attendance_group['group_id'] );
			$attendance_fields = array_column( $attendance_group['data'], 'name' );
			$this->assertSame(
				array(
					'Collection Date',
					'Collection Time',
					'Method',
				),
				$attendance_fields
			);

			$override_group = $result['data'][2];
			$override_map   = array_column( $override_group['data'], 'value', 'name' );
			$this->assertSame( 'Approved extra visit', $override_map['Override Note'] );
			$this->assertSame( '2024-03-05 09:00:00', $override_map['Override Timestamp'] );
			$this->assertArrayHasKey( 'Attendance Record ID', $override_map );
			$this->assertArrayNotHasKey( 'Override By (User ID)', $override_map );
	}

	public function test_export_returns_empty_when_identifier_unknown(): void {
			$result = Exporter::export( 'unknown@example.test' );

			$this->assertTrue( $result['done'] );
			$this->assertSame( array(), $result['data'] );
	}

	public function test_export_allows_member_reference_lookup(): void {
			$result = Exporter::export( 'FBM0001' );

			$this->assertTrue( $result['done'] );
			$this->assertNotSame( array(), $result['data'] );
	}
}
