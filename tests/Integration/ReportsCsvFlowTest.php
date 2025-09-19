<?php
/**
 * Integration coverage for report summaries and CSV streaming.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Integration;

use DateTimeImmutable;
use DateTimeZone;
use FoodBankManager\Attendance\AttendanceRepository;
use FoodBankManager\Registration\MembersRepository;
use FoodBankManager\Reports\CsvExporter;
use FoodBankManager\Reports\ReportsRepository;
use FoodBankManager\Reports\SummaryBuilder;
use FoodBankManager\Token\TokenRepository;
use FoodBankManager\Token\TokenService;
use PHPUnit\Framework\TestCase;

/**
 * @covers \FoodBankManager\Reports\SummaryBuilder
 * @covers \FoodBankManager\Reports\CsvExporter
 */
final class ReportsCsvFlowTest extends TestCase {
        private \wpdb $wpdb;

        private MembersRepository $members;

        private TokenService $tokens;

        private AttendanceRepository $attendance;

        protected function setUp(): void {
                parent::setUp();

                $this->wpdb               = new \wpdb();
                $GLOBALS['wpdb']          = $this->wpdb;
                $GLOBALS['fbm_transients'] = array();

                $this->members    = new MembersRepository( $this->wpdb );
                $this->tokens     = new TokenService( new TokenRepository( $this->wpdb ) );
                $this->attendance = new AttendanceRepository( $this->wpdb );
        }

        protected function tearDown(): void {
                unset( $GLOBALS['wpdb'], $GLOBALS['fbm_transients'], $GLOBALS['fbm_last_csv_stream'] );

                parent::tearDown();
        }

        public function test_summary_results_are_cached_for_common_ranges(): void {
                $this->seedAttendance();

                $repository = new ReportsRepository( $this->wpdb );
                $builder    = new SummaryBuilder( $repository );

                $start = new DateTimeImmutable( '2023-08-01 00:00:00', new DateTimeZone( 'UTC' ) );
                $end   = new DateTimeImmutable( '2023-08-31 23:59:59', new DateTimeZone( 'UTC' ) );

                $summary = $builder->get_summary( $start, $end );
                $this->assertFalse( $summary['cache_hit'] );
                $this->assertSame( 2, $summary['data']['total'] );
                $this->assertSame( 1, $summary['data']['active'] );
                $this->assertSame( 1, $summary['data']['revoked'] );

                $summary_cached = $builder->get_summary( $start, $end );
                $this->assertTrue( $summary_cached['cache_hit'] );

                $total = $builder->get_total( $start, $end );
                $this->assertFalse( $total['cache_hit'] );
                $this->assertSame( 2, $total['total'] );

                $total_cached = $builder->get_total( $start, $end );
                $this->assertTrue( $total_cached['cache_hit'] );

                $rows = $builder->get_rows( $start, $end, array(), 1, 25 );
                $this->assertFalse( $rows['cache_hit'] );
                $this->assertCount( 2, $rows['rows'] );

                $rows_cached = $builder->get_rows( $start, $end, array(), 1, 25 );
                $this->assertTrue( $rows_cached['cache_hit'] );

                $this->assertNotEmpty( $GLOBALS['fbm_transients'] );
        }

        public function test_csv_stream_emits_bom_and_expected_headers(): void {
                $this->seedAttendance();

                $repository = new ReportsRepository( $this->wpdb );
                $headers    = array();

                $emitter = static function ( string $header ) use ( &$headers ): void {
                        $headers[] = $header;
                };

                $exporter = new CsvExporter( $repository, $emitter, 'php://filter/write=fbm.capture/resource=php://temp', 1 );

                $start = new DateTimeImmutable( '2023-08-01 00:00:00', new DateTimeZone( 'UTC' ) );
                $end   = new DateTimeImmutable( '2023-08-31 23:59:59', new DateTimeZone( 'UTC' ) );

                $GLOBALS['fbm_last_csv_stream'] = '';
                $exporter->stream( $start, $end, array(), 'fbm-attendance' );

                $this->assertContains( 'Content-Type: text/csv; charset=UTF-8', $headers );
                $this->assertContains( 'Content-Disposition: attachment; filename="fbm-attendance.csv"', $headers );

                $this->assertIsString( $GLOBALS['fbm_last_csv_stream'] );
                $buffer = (string) $GLOBALS['fbm_last_csv_stream'];
                $this->assertStringStartsWith( "\xEF\xBB\xBF", $buffer );

                $lines = array_values( array_filter( explode( "\n", trim( $buffer ) ) ) );
                $this->assertNotEmpty( $lines );
                $this->assertSame(
                        'Collected Date,Collected Time,Member Reference,Member Status,Method,Note,Recorded By',
                        $lines[0]
                );
        }

        private function seedAttendance(): void {
                $active   = $this->createMember( 'FBM-RPT01', 'report-active@example.com' );
                $revoked  = $this->createMember( 'FBM-RPT02', 'report-revoked@example.com' );
                $this->members->mark_revoked( $revoked['id'] );

                $this->attendance->record(
                        $active['reference'],
                        'qr',
                        10,
                        new DateTimeImmutable( '2023-08-05 10:30:00', new DateTimeZone( 'UTC' ) ),
                        'First visit'
                );

                $this->attendance->record(
                        $revoked['reference'],
                        'manual',
                        11,
                        new DateTimeImmutable( '2023-08-07 12:45:00', new DateTimeZone( 'UTC' ) ),
                        'Override noted'
                );
        }

        /**
         * Create a member and issue a token for attendance records.
         *
         * @return array{id:int,reference:string}
         */
        private function createMember( string $reference, string $email ): array {
                $member_id = $this->members->insert_active_member( $reference, 'Alex', 'R', $email, 4, null );
                $this->tokens->issue_with_details( $member_id, array( 'context' => 'reports-test' ) );

                return array(
                        'id'        => $member_id,
                        'reference' => $reference,
                );
        }
}
