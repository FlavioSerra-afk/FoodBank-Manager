<?php
/**
 * Report caching behaviour tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Reports;

use DateTimeImmutable;
use DateTimeZone;
use FoodBankManager\Attendance\AttendanceRepository;
use FoodBankManager\Core\Cache;
use FoodBankManager\Reports\ReportsRepository;
use FoodBankManager\Reports\SummaryBuilder;
use FoodBankManager\Registration\MembersRepository;
use PHPUnit\Framework\TestCase;

/**
 * @covers \FoodBankManager\Reports\SummaryBuilder
 * @covers \FoodBankManager\Reports\ReportsRepository
 * @covers \FoodBankManager\Core\Cache
 */
final class ReportsCacheTest extends TestCase {
        protected function setUp(): void {
                parent::setUp();

                unset( $GLOBALS['fbm_transients'] );
                Cache::purge_group( 'reports' );
        }

        public function test_summary_results_are_cached_between_calls(): void {
                $builder = $this->bootstrap_builder();
                $range   = $this->range_for_january();

                $first  = $builder->get_summary( $range['start'], $range['end'] );
                $second = $builder->get_summary( $range['start'], $range['end'] );

                $this->assertFalse( $first['cache_hit'] );
                $this->assertTrue( $second['cache_hit'] );
                $this->assertSame( $first['data'], $second['data'] );
        }

        public function test_range_variation_bypasses_cached_summary(): void {
                $builder = $this->bootstrap_builder();
                $jan     = $this->range_for_january();
                $feb     = $this->range_for_february();

                $builder->get_summary( $jan['start'], $jan['end'] );
                $result = $builder->get_summary( $feb['start'], $feb['end'] );

                $this->assertFalse( $result['cache_hit'] );
        }

        public function test_cache_invalidation_purges_stored_results(): void {
                $builder = $this->bootstrap_builder();
                $range   = $this->range_for_january();

                $builder->get_summary( $range['start'], $range['end'] );

                $cached = $builder->get_summary( $range['start'], $range['end'] );
                $this->assertTrue( $cached['cache_hit'] );

                Cache::purge_group( 'reports' );

                $after = $builder->get_summary( $range['start'], $range['end'] );
                $this->assertFalse( $after['cache_hit'] );
        }

        public function test_row_pagination_is_cached_per_page(): void {
                $builder = $this->bootstrap_builder();
                $range   = $this->range_for_january();

                $page_one = $builder->get_rows( $range['start'], $range['end'], array(), 1, 1 );
                $this->assertFalse( $page_one['cache_hit'] );

                $page_one_cached = $builder->get_rows( $range['start'], $range['end'], array(), 1, 1 );
                $this->assertTrue( $page_one_cached['cache_hit'] );

                $page_two = $builder->get_rows( $range['start'], $range['end'], array(), 2, 1 );
                $this->assertFalse( $page_two['cache_hit'] );
        }

        /**
         * Bootstrap a summary builder with seeded attendance data.
         */
        private function bootstrap_builder(): SummaryBuilder {
                $wpdb            = new \wpdb();
                $members_repo    = new MembersRepository( $wpdb );
                $attendance_repo = new AttendanceRepository( $wpdb );

                $members_repo->insert_active_member( 'FBM100', 'Alice', 'A', 'alice@example.com', 1 );
                $members_repo->insert_active_member( 'FBM200', 'Bob', 'B', 'bob@example.com', 2 );

                $timezone = new DateTimeZone( 'UTC' );

                $attendance_repo->record( 'FBM100', 'qr', 1, new DateTimeImmutable( '2024-01-10 10:00:00', $timezone ) );
                $attendance_repo->record( 'FBM200', 'manual', 2, new DateTimeImmutable( '2024-02-05 11:15:00', $timezone ) );

                return new SummaryBuilder( new ReportsRepository( $wpdb ) );
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

        /**
         * Provide a canonical February date range.
         *
         * @return array{start:DateTimeImmutable,end:DateTimeImmutable}
         */
        private function range_for_february(): array {
                $timezone = new DateTimeZone( 'UTC' );

                return array(
                        'start' => new DateTimeImmutable( '2024-02-01', $timezone ),
                        'end'   => new DateTimeImmutable( '2024-02-29', $timezone ),
                );
        }
}
