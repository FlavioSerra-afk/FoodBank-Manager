<?php
/**
 * Reports repository member history tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Reports;

use DateTimeImmutable;
use DateTimeZone;
use FoodBankManager\Attendance\AttendanceRepository;
use FoodBankManager\Registration\MembersRepository;
use FoodBankManager\Reports\ReportsRepository;
use PHPUnit\Framework\TestCase;

/**
 * @covers \FoodBankManager\Reports\ReportsRepository::get_member_history
 */
final class ReportsRepositoryTest extends TestCase {
        public function test_get_member_history_returns_reverse_chronological_rows(): void {
                $wpdb            = new \wpdb();
                $members_repo    = new MembersRepository( $wpdb );
                $attendance_repo = new AttendanceRepository( $wpdb );

                $members_repo->insert_active_member( 'FBM-HIST', 'Alex', 'H', 'alex@example.com', 3 );

                $timezone = new DateTimeZone( 'UTC' );
                $attendance_repo->record( 'FBM-HIST', 'qr', 1, new DateTimeImmutable( '2024-03-01 10:00:00', $timezone ) );
                $attendance_repo->record( 'FBM-HIST', 'manual', 2, new DateTimeImmutable( '2024-03-08 11:30:00', $timezone ) );

                $repository = new ReportsRepository( $wpdb );
                $history    = $repository->get_member_history( 'FBM-HIST' );

                $this->assertIsArray( $history['member'] );
                $this->assertSame( 'Alex', $history['member']['first_name'] );
                $this->assertCount( 2, $history['rows'] );
                $this->assertSame( 'manual', $history['rows'][0]['method'] );
                $this->assertSame( 'qr', $history['rows'][1]['method'] );
        }

        public function test_get_member_history_with_unknown_reference_returns_empty_rows(): void {
                $repository = new ReportsRepository( new \wpdb() );

                $history = $repository->get_member_history( '' );

                $this->assertNull( $history['member'] );
                $this->assertSame( array(), $history['rows'] );
        }
}
