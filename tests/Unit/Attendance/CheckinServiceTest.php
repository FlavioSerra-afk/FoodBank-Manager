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
use PHPUnit\Framework\TestCase;

/**
 * @covers \FoodBankManager\Attendance\CheckinService
 */
final class CheckinServiceTest extends TestCase {
        protected function tearDown(): void {
                CheckinService::set_current_time_override( null );

                parent::tearDown();
        }

        public function test_record_returns_duplicate_day_status_when_member_already_checked_in(): void {
                $repository = new AttendanceRepository( new \wpdb() );

                CheckinService::set_current_time_override(
                        new DateTimeImmutable( '2023-08-17 12:15:00', new DateTimeZone( 'Europe/London' ) )
                );

                $service       = new CheckinService( $repository );
                $initialResult = $service->record( 'FBM123', 'qr', 1 );

                $this->assertSame( CheckinService::STATUS_SUCCESS, $initialResult['status'] );

                $duplicate = $service->record( 'FBM123', 'qr', 1 );

                $this->assertSame( CheckinService::STATUS_DUPLICATE_DAY, $duplicate['status'] );
                $this->assertSame( 'Member already collected today.', $duplicate['message'] );
                $this->assertSame( 'FBM123', $duplicate['member_ref'] );
                $this->assertSame( '2023-08-17T11:15:00+00:00', $duplicate['time'] );
        }

        public function test_record_returns_out_of_window_status_when_not_available(): void {
                $repository = new AttendanceRepository( new \wpdb() );

                CheckinService::set_current_time_override(
                        new DateTimeImmutable( '2023-08-16 10:00:00', new DateTimeZone( 'Europe/London' ) )
                );

                $service = new CheckinService( $repository );

                $result = $service->record( 'FBM456', 'qr', 2 );

                $this->assertSame( CheckinService::STATUS_OUT_OF_WINDOW, $result['status'] );
                $this->assertSame(
                        'Collections are only available on Thursdays between 11:00 and 14:30.',
                        $result['message']
                );
                $this->assertSame( 'FBM456', $result['member_ref'] );
                $this->assertNull( $result['time'] );
        }

        public function test_record_returns_warning_when_previous_collection_within_week(): void {
                $repository = new AttendanceRepository( new \wpdb() );

                CheckinService::set_current_time_override(
                        new DateTimeImmutable( '2023-08-17 12:15:00', new DateTimeZone( 'Europe/London' ) )
                );

                $service = new CheckinService( $repository );

                $initial = $service->record( 'FBM789', 'qr', 3 );

                $this->assertSame( CheckinService::STATUS_SUCCESS, $initial['status'] );

                CheckinService::set_current_time_override(
                        new DateTimeImmutable( '2023-08-24 12:14:00', new DateTimeZone( 'Europe/London' ) )
                );

                $warning = $service->record( 'FBM789', 'qr', 3 );

                $this->assertSame( CheckinService::STATUS_RECENT_WARNING, $warning['status'] );
                $this->assertSame(
                        'Collection recorded, but member collected less than a week ago.',
                        $warning['message']
                );
        }

        public function test_record_allows_override_to_bypass_recent_warning(): void {
                $repository = new AttendanceRepository( new \wpdb() );

                CheckinService::set_current_time_override(
                        new DateTimeImmutable( '2023-08-17 12:15:00', new DateTimeZone( 'Europe/London' ) )
                );

                $service = new CheckinService( $repository );

                $service->record( 'FBM880', 'manual', 4 );

                CheckinService::set_current_time_override(
                        new DateTimeImmutable( '2023-08-24 12:14:00', new DateTimeZone( 'Europe/London' ) )
                );

                $result = $service->record( 'FBM880', 'manual', 4, 'override reason' );

                $this->assertSame( CheckinService::STATUS_SUCCESS, $result['status'] );
                $this->assertSame( 'Collection recorded.', $result['message'] );
        }
}
