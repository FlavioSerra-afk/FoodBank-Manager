<?php
/**
 * CSV streaming tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Reports;

use DateTimeImmutable;
use DateTimeZone;
use FoodBankManager\Attendance\AttendanceRepository;
use FoodBankManager\Reports\CsvExporter;
use FoodBankManager\Reports\ReportsRepository;
use FoodBankManager\Registration\MembersRepository;
use PHPUnit\Framework\TestCase;
use function ob_get_clean;
use function ob_start;
use function preg_split;
use function str_getcsv;
use function substr;

/**
 * @covers \FoodBankManager\Reports\CsvExporter
 * @covers \FoodBankManager\Reports\ReportsRepository
 */
final class CsvStreamTest extends TestCase {
        protected function setUp(): void {
                parent::setUp();

                unset( $GLOBALS['fbm_transients'] );
        }

        public function test_stream_emits_bom_and_headers(): void {
                $header_log = array();
                $exporter   = $this->create_exporter( $header_log );
                $range      = $this->range_for_january();

                ob_start();
                $exporter->stream( $range['start'], $range['end'], array(), ' attendance report ' );
                $output = ob_get_clean();

                $this->assertNotFalse( $output );
                $this->assertStringStartsWith( "\xEF\xBB\xBF", (string) $output );

                $payload = substr( (string) $output, 3 );
                $lines   = preg_split( '/\r\n|\n/', trim( (string) $payload ) );

                $this->assertIsArray( $lines );
                $this->assertGreaterThanOrEqual( 2, count( $lines ) );

                $header_columns = str_getcsv( (string) $lines[0], ',', '"', '\\' );

                $this->assertSame(
                        array(
                                'Collected Date',
                                'Collected Time',
                                'Member Reference',
                                'Member Status',
                                'Method',
                                'Note',
                                'Recorded By',
                        ),
                        $header_columns
                );

                $this->assertContains( 'Content-Type: text/csv; charset=UTF-8', $header_log );
                $this->assertContains( 'Content-Disposition: attachment; filename="attendance-report.csv"', $header_log );
        }

        /**
         * Create a CSV exporter with seeded data.
         *
         * @param array<int,string> $header_log Header capture array (by reference).
         */
        private function create_exporter( array &$header_log ): CsvExporter {
                $wpdb            = new \wpdb();
                $members_repo    = new MembersRepository( $wpdb );
                $attendance_repo = new AttendanceRepository( $wpdb );

                $members_repo->insert_active_member( 'FBM100', 'Alice', 'A', 'alice@example.com', 1 );
                $members_repo->insert_active_member( 'FBM200', 'Bob', 'B', 'bob@example.com', 2 );

                $timezone = new DateTimeZone( 'UTC' );

                $attendance_repo->record( 'FBM100', 'qr', 1, new DateTimeImmutable( '2024-01-10 10:00:00', $timezone ) );
                $attendance_repo->record( 'FBM200', 'manual', 2, new DateTimeImmutable( '2024-01-17 11:30:00', $timezone ) );

                $header_emitter = static function ( string $header ) use ( &$header_log ): void {
                        $header_log[] = $header;
                };

                return new CsvExporter( new ReportsRepository( $wpdb ), $header_emitter, 'php://output', 1 );
        }

        /**
         * Provide a canonical January date range.
         *
         * @return array{start:DateTimeImmutable,end:DateTimeImmutable}
         */
        private function range_for_january(): array {
                $timezone = new DateTimeZone( 'UTC' );

                return array(
                        'start' => new DateTimeImmutable( '2024-01-01', $timezone ),
                        'end'   => new DateTimeImmutable( '2024-01-31', $timezone ),
                );
        }
}
