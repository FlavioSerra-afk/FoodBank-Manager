<?php
/**
 * Attendance check-in throttle integration tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Attendance;

use DateTimeImmutable;
use DateTimeZone;
use FoodBankManager\Attendance\AttendanceRepository;
use FoodBankManager\Attendance\CheckinService;
use PHPUnit\Framework\TestCase;

/**
 * @covers \FoodBankManager\Attendance\CheckinService
 */
final class CheckinServiceTest extends TestCase {
        protected function setUp(): void {
                parent::setUp();

                unset( $GLOBALS['fbm_transients'] );

                CheckinService::set_current_time_override(
                        new DateTimeImmutable( '2023-08-17 12:15:00', new DateTimeZone( 'Europe/London' ) )
                );

                $_SERVER['REMOTE_ADDR'] = '203.0.113.10';
        }

        protected function tearDown(): void {
                CheckinService::set_current_time_override( null );

                unset( $GLOBALS['fbm_transients'], $_SERVER['REMOTE_ADDR'] );

                parent::tearDown();
        }

        public function test_record_allows_requests_within_throttle_limit(): void {
                $wpdb       = new \wpdb();
                $repository = new AttendanceRepository( $wpdb );
                $service    = new CheckinService( $repository );

                for ( $i = 0; $i < 5; $i++ ) {
                        $reference = sprintf( 'FBM%d', $i );
                        $result    = $service->record( $reference, 'qr', 42, null, false, null, '198.51.100.5' );

                        $this->assertSame( CheckinService::STATUS_SUCCESS, $result['status'] );
                }
        }

        public function test_record_blocks_requests_after_throttle_limit(): void {
                $wpdb       = new \wpdb();
                $repository = new AttendanceRepository( $wpdb );
                $service    = new CheckinService( $repository );

                for ( $i = 0; $i < 5; $i++ ) {
                        $reference = sprintf( 'FBMT%d', $i );
                        $service->record( $reference, 'qr', 87, null, false, null, '198.51.100.25' );
                }

                $blocked = $service->record( 'FBMT-block', 'manual', 87, 'Retry too quickly', false, null, '198.51.100.25' );

                $this->assertSame( CheckinService::STATUS_THROTTLED, $blocked['status'] );
                $this->assertSame( 'Please wait a moment before trying again.', $blocked['message'] );
                $this->assertSame( 'FBMT-block', $blocked['member_ref'] );
                $this->assertNull( $blocked['time'] );
        }

        public function test_override_requests_are_also_throttled_after_limit(): void {
                $wpdb       = new \wpdb();
                $repository = new AttendanceRepository( $wpdb );
                $service    = new CheckinService( $repository );

                for ( $i = 0; $i < 5; $i++ ) {
                        $reference = sprintf( 'FBMO%d', $i );
                        $service->record( $reference, 'qr', null, null, false, null, '198.51.100.75' );
                }

                $override = $service->record( 'FBMO-limit', 'manual', null, null, true, 'Emergency override', '198.51.100.75' );

                $this->assertSame( CheckinService::STATUS_THROTTLED, $override['status'] );
                $this->assertNull( $override['time'] );
        }
}
