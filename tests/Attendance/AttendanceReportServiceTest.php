<?php
/**
 * Attendance report service tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Attendance;

use DateTimeImmutable;
use DateTimeZone;
use FoodBankManager\Attendance\AttendanceReportService;
use FoodBankManager\Attendance\AttendanceRepository;
use FoodBankManager\Registration\MembersRepository;
use PHPUnit\Framework\TestCase;

/**
 * @covers \FoodBankManager\Attendance\AttendanceReportService
 * @covers \FoodBankManager\Attendance\AttendanceRepository::summarize_range
 * @covers \FoodBankManager\Attendance\AttendanceRepository::fetch_range
 */
final class AttendanceReportServiceTest extends TestCase {
        protected function setUp(): void {
                parent::setUp();

                unset( $GLOBALS['fbm_transients'] );
                AttendanceReportService::invalidate_cache();
        }

        public function test_summarize_counts_active_and_revoked_records(): void {
                $wpdb            = new \wpdb();
                $attendance_repo = new AttendanceRepository( $wpdb );
                $members_repo    = new MembersRepository( $wpdb );

                $members_repo->insert_active_member( 'FBM100', 'Alice', 'A', 'alice@example.com', 2 );
                $members_repo->insert_active_member( 'FBM200', 'Bob', 'B', 'bob@example.com', 3 );

                $wpdb->members[2]['status'] = 'revoked';

                $timezone = new DateTimeZone( 'UTC' );
                $service  = new AttendanceReportService( $attendance_repo );

                $attendance_repo->record( 'FBM100', 'qr', 1, new DateTimeImmutable( '2024-01-10 12:00:00', $timezone ) );
                $attendance_repo->record( 'FBM200', 'manual', 2, new DateTimeImmutable( '2024-01-11 13:30:00', $timezone ), 'Late arrival' );

                $start   = new DateTimeImmutable( '2024-01-01', $timezone );
                $end     = new DateTimeImmutable( '2024-01-31', $timezone );
                $summary = $service->summarize( $start, $end );

                $this->assertSame( '2024-01-01', $summary['start'] );
                $this->assertSame( '2024-01-31', $summary['end'] );
                $this->assertSame( 2, $summary['total'] );
                $this->assertSame( 1, $summary['active'] );
                $this->assertSame( 1, $summary['revoked'] );
                $this->assertSame( 0, $summary['other'] );
        }

        public function test_export_returns_rows_with_status_and_notes(): void {
                $wpdb            = new \wpdb();
                $attendance_repo = new AttendanceRepository( $wpdb );
                $members_repo    = new MembersRepository( $wpdb );

                $members_repo->insert_active_member( 'FBM300', 'Cara', 'C', 'cara@example.com', 2 );
                $members_repo->insert_active_member( 'FBM400', 'Dan', 'D', 'dan@example.com', 1 );

                $wpdb->members[2]['status'] = 'revoked';

                $timezone = new DateTimeZone( 'UTC' );
                $service  = new AttendanceReportService( $attendance_repo );

                $attendance_repo->record( 'FBM300', 'qr', 3, new DateTimeImmutable( '2024-02-05 10:15:00', $timezone ), 'Checked in quickly' );
                $attendance_repo->record( 'FBM400', 'manual', null, new DateTimeImmutable( '2024-02-06 11:20:00', $timezone ) );

                $start = new DateTimeImmutable( '2024-02-01', $timezone );
                $end   = new DateTimeImmutable( '2024-02-28', $timezone );

                $rows = $service->export( $start, $end );

                $this->assertCount( 2, $rows );

                $first = $rows[0];
                $this->assertSame( 'FBM300', $first['member_reference'] );
                $this->assertSame( '2024-02-05 10:15:00', $first['collected_at'] );
                $this->assertSame( 'active', $first['status'] );
                $this->assertSame( 'Checked in quickly', $first['note'] );
                $this->assertSame( 3, $first['recorded_by'] );

                $second = $rows[1];
                $this->assertSame( 'FBM400', $second['member_reference'] );
                $this->assertSame( '2024-02-06 11:20:00', $second['collected_at'] );
                $this->assertSame( 'revoked', $second['status'] );
                $this->assertNull( $second['note'] );
                $this->assertNull( $second['recorded_by'] );
        }

        public function test_cache_invalidation_triggers_on_new_record(): void {
                $wpdb            = new \wpdb();
                $attendance_repo = new AttendanceRepository( $wpdb );
                $members_repo    = new MembersRepository( $wpdb );

                $members_repo->insert_active_member( 'FBM500', 'Eve', 'E', 'eve@example.com', 4 );

                $timezone = new DateTimeZone( 'UTC' );
                $service  = new AttendanceReportService( $attendance_repo );

                $attendance_repo->record( 'FBM500', 'qr', 9, new DateTimeImmutable( '2024-03-01 12:00:00', $timezone ) );

                $start = new DateTimeImmutable( '2024-03-01', $timezone );
                $end   = new DateTimeImmutable( '2024-03-31', $timezone );

                $summary_initial = $service->summarize( $start, $end );
                $this->assertSame( 1, $summary_initial['total'] );

                $summary_cached = $service->summarize( $start, $end );
                $this->assertSame( 1, $summary_cached['total'] );

                $attendance_repo->record( 'FBM500', 'manual', 9, new DateTimeImmutable( '2024-03-15 12:30:00', $timezone ), 'Assisted' );

                $summary_after = $service->summarize( $start, $end );
                $this->assertSame( 2, $summary_after['total'] );
        }
}
