<?php
/**
 * Check-in service unit tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Unit\Attendance;

use DateTimeImmutable;
use DateTimeZone;
use FoodBankManager\Attendance\AttendanceRepository;
use FoodBankManager\Attendance\CheckinService;
use FoodBankManager\Core\Schedule;
use PHPUnit\Framework\TestCase;
use function update_option;

/**
 * @covers \FoodBankManager\Attendance\CheckinService
 */
final class CheckinServiceTest extends TestCase {
        protected function setUp(): void {
                        parent::setUp();

                        unset( $GLOBALS['fbm_transients'] );

                        $GLOBALS['fbm_options'] = array();
                        update_option( 'fbm_schedule_window', $this->default_window() );

                        Schedule::set_current_window_override( null );
        }

	protected function tearDown(): void {
			CheckinService::set_current_time_override( null );
			Schedule::set_current_window_override( null );

			parent::tearDown();
	}

		/**
		 * Provide the default schedule window for tests.
		 *
		 * @return array{day:string,start:string,end:string,timezone:string}
		 */
	private function default_window(): array {
			return array(
				'day'      => 'thursday',
				'start'    => '11:00',
				'end'      => '14:30',
				'timezone' => 'Europe/London',
			);
	}

	public function test_record_returns_duplicate_day_status_when_member_already_checked_in(): void {
			$wpdb       = new \wpdb();
			$repository = new AttendanceRepository( $wpdb );

			CheckinService::set_current_time_override(
				new DateTimeImmutable( '2023-08-17 12:15:00', new DateTimeZone( 'Europe/London' ) )
			);

			$service       = new CheckinService( $repository, new Schedule() );
			$initialResult = $service->record( 'FBM123', 'qr', 1 );

			$this->assertSame( CheckinService::STATUS_SUCCESS, $initialResult['status'] );

			$duplicate = $service->record( 'FBM123', 'qr', 1 );

			$this->assertSame( CheckinService::STATUS_DUPLICATE_DAY, $duplicate['status'] );
			$this->assertSame( 'Member already collected today.', $duplicate['message'] );
			$this->assertSame( 'FBM123', $duplicate['member_ref'] );
			$this->assertSame( '2023-08-17T11:15:00+00:00', $duplicate['time'] );
	}

        public function test_record_returns_out_of_window_status_when_not_available(): void {
                        $wpdb       = new \wpdb();
                        $repository = new AttendanceRepository( $wpdb );

                        $schedule = new Schedule();
                        $window   = $schedule->current_window();

                        CheckinService::set_current_time_override(
                                new DateTimeImmutable( '2023-08-16 10:00:00', new DateTimeZone( 'Europe/London' ) )
                        );

                        $service = new CheckinService( $repository, $schedule );

                        $result = $service->record( 'FBM456', 'qr', 2 );

                        $this->assertSame( CheckinService::STATUS_OUT_OF_WINDOW, $result['status'] );
                        $this->assertSame( Schedule::window_notice( $window ), $result['message'] );
                        $this->assertSame( 'FBM456', $result['member_ref'] );
                        $this->assertNull( $result['time'] );
                        $this->assertSame( $window, $result['window'] );
        }

	public function test_record_returns_warning_when_previous_collection_within_week(): void {
			$wpdb       = new \wpdb();
			$repository = new AttendanceRepository( $wpdb );

			CheckinService::set_current_time_override(
				new DateTimeImmutable( '2023-08-17 12:15:00', new DateTimeZone( 'Europe/London' ) )
			);

			$service = new CheckinService( $repository, new Schedule() );

			$initial = $service->record( 'FBM789', 'qr', 3 );

			$this->assertSame( CheckinService::STATUS_SUCCESS, $initial['status'] );
			$this->assertCount( 1, $wpdb->attendance );

			CheckinService::set_current_time_override(
				new DateTimeImmutable( '2023-08-24 12:14:00', new DateTimeZone( 'Europe/London' ) )
			);

			$warning = $service->record( 'FBM789', 'qr', 3 );

			$this->assertSame( CheckinService::STATUS_RECENT_WARNING, $warning['status'] );
			$this->assertSame(
				'Member collected less than a week ago. Only managers can continue with a justified override.',
				$warning['message']
			);
			$this->assertSame( 'FBM789', $warning['member_ref'] );
			$this->assertSame( '2023-08-17T11:15:00+00:00', $warning['time'] );
			$this->assertCount( 1, $wpdb->attendance );
	}

	public function test_record_allows_override_to_bypass_recent_warning(): void {
			$wpdb       = new \wpdb();
			$repository = new AttendanceRepository( $wpdb );

			CheckinService::set_current_time_override(
				new DateTimeImmutable( '2023-08-17 12:15:00', new DateTimeZone( 'Europe/London' ) )
			);

			$service = new CheckinService( $repository, new Schedule() );

			$service->record( 'FBM880', 'manual', 4 );

			CheckinService::set_current_time_override(
				new DateTimeImmutable( '2023-08-24 12:14:00', new DateTimeZone( 'Europe/London' ) )
			);

			$result = $service->record( 'FBM880', 'manual', 4, null, true, 'override reason' );

			$this->assertSame( CheckinService::STATUS_SUCCESS, $result['status'] );
			$this->assertSame( 'Collection recorded.', $result['message'] );

			$this->assertCount( 2, $wpdb->attendance );
			$this->assertNotEmpty( $wpdb->attendance_overrides );
			$overrides    = array_values( $wpdb->attendance_overrides );
			$latest_audit = $overrides[ count( $overrides ) - 1 ];

			$this->assertSame( 'FBM880', $latest_audit['member_reference'] );
			$this->assertSame( 4, $latest_audit['override_by'] );
			$this->assertSame( 'override reason', $latest_audit['override_note'] );
	}

	public function test_record_keeps_warning_when_note_without_override(): void {
			$wpdb       = new \wpdb();
			$repository = new AttendanceRepository( $wpdb );

			CheckinService::set_current_time_override(
				new DateTimeImmutable( '2023-08-17 12:15:00', new DateTimeZone( 'Europe/London' ) )
			);

			$service = new CheckinService( $repository, new Schedule() );

			$service->record( 'FBM881', 'qr', 5 );

			CheckinService::set_current_time_override(
				new DateTimeImmutable( '2023-08-24 12:14:00', new DateTimeZone( 'Europe/London' ) )
			);

			$result = $service->record( 'FBM881', 'qr', 5, 'note only' );

			$this->assertSame( CheckinService::STATUS_RECENT_WARNING, $result['status'] );
			$this->assertCount( 1, $wpdb->attendance );
	}

        public function test_record_honors_custom_schedule_window(): void {
                        $wpdb       = new \wpdb();
                        $repository = new AttendanceRepository( $wpdb );

                        $window = array(
                                'day'      => 'monday',
                                'start'    => '09:00',
                                'end'      => '10:15',
                                'timezone' => 'Europe/London',
                        );

                        Schedule::set_current_window_override( $window );
                        $schedule = new Schedule();

                        CheckinService::set_current_time_override(
                                new DateTimeImmutable( '2023-08-21 09:30:00', new DateTimeZone( 'Europe/London' ) )
                        );

                        $service = new CheckinService( $repository, $schedule );
                        $result  = $service->record( 'FBM900', 'qr', 6 );

                        $this->assertSame( CheckinService::STATUS_SUCCESS, $result['status'] );

                        CheckinService::set_current_time_override(
                                new DateTimeImmutable( '2023-08-21 08:30:00', new DateTimeZone( 'Europe/London' ) )
                        );

                        $out_of_window = $service->record( 'FBM901', 'qr', 6 );

                        $this->assertSame( CheckinService::STATUS_OUT_OF_WINDOW, $out_of_window['status'] );
                        $this->assertSame( Schedule::window_notice( $window ), $out_of_window['message'] );
                        $this->assertSame( $window, $out_of_window['window'] );
        }
}
