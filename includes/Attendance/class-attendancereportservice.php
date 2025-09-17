<?php
/**
 * Attendance reporting service.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Attendance;

use DateTimeImmutable;
use function get_transient;
use function is_array;
use function is_numeric;
use function is_string;
use function md5;
use function set_transient;

/**
 * Provides cached attendance summaries and exports.
 */
final class AttendanceReportService {
        private const CACHE_TTL    = 300;
        private const VERSION_TTL  = 86400;
        private const VERSION_KEY  = 'fbm_attendance_report_cache_version';
        private const CACHE_PREFIX = 'fbm_attendance_report_';

        /**
         * Attendance repository dependency.
         *
         * @var AttendanceRepository
         */
        private AttendanceRepository $attendance;

        /**
         * Class constructor.
         *
         * @param AttendanceRepository $attendance Attendance repository instance.
         */
        public function __construct( AttendanceRepository $attendance ) {
                $this->attendance = $attendance;
        }

        /**
         * Retrieve a cached summary for the requested range.
         *
         * @param DateTimeImmutable $start Inclusive start date (UTC).
         * @param DateTimeImmutable $end   Inclusive end date (UTC).
         *
         * @return array{start:string,end:string,total:int,active:int,revoked:int,other:int}
         */
        public function summarize( DateTimeImmutable $start, DateTimeImmutable $end ): array {
                $cache_key = $this->cache_key( 'summary', $start, $end );
                $cached    = get_transient( $cache_key );

                if ( is_array( $cached ) ) {
                        return $cached;
                }

                $summary = $this->attendance->summarize_range( $start, $end );

                set_transient( $cache_key, $summary, self::CACHE_TTL );

                return $summary;
        }

        /**
         * Retrieve attendance rows for export.
         *
         * @param DateTimeImmutable $start Inclusive start date (UTC).
         * @param DateTimeImmutable $end   Inclusive end date (UTC).
         *
         * @return array<int,array{member_reference:string,collected_at:string,collected_date:string,method:string,note:?string,recorded_by:?int,status:string}>
         */
        public function export( DateTimeImmutable $start, DateTimeImmutable $end ): array {
                $cache_key = $this->cache_key( 'export', $start, $end );
                $cached    = get_transient( $cache_key );

                if ( is_array( $cached ) ) {
                        return $cached;
                }

                $rows = $this->attendance->fetch_range( $start, $end );

                set_transient( $cache_key, $rows, self::CACHE_TTL );

                return $rows;
        }

        /**
         * Invalidate cached summaries and exports.
         */
        public static function invalidate_cache(): void {
                $current = get_transient( self::VERSION_KEY );
                $version = is_numeric( $current ) ? (int) $current : 0;
                $version++;

                set_transient( self::VERSION_KEY, (string) $version, self::VERSION_TTL );
        }

        /**
         * Compose a cache key using the current version and range.
         *
         * @param string            $type  Cache namespace.
         * @param DateTimeImmutable $start Range start.
         * @param DateTimeImmutable $end   Range end.
         */
        private function cache_key( string $type, DateTimeImmutable $start, DateTimeImmutable $end ): string {
                $version = get_transient( self::VERSION_KEY );

                if ( ! is_string( $version ) || '' === $version ) {
                        $version = '1';
                        set_transient( self::VERSION_KEY, $version, self::VERSION_TTL );
                }

                $range = md5( $start->format( 'Y-m-d' ) . '|' . $end->format( 'Y-m-d' ) );

                return self::CACHE_PREFIX . $version . '_' . $type . '_' . $range;
        }
}
