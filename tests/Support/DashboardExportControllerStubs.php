<?php
namespace FoodBankManager\Shortcodes {
    if ( ! class_exists( Dashboard::class, false ) ) {
        class Dashboard {
            public static function sanitize_period( string $period ): string { return $period; }
            public static function since_from_period( string $period ): \DateTimeImmutable { return new \DateTimeImmutable( '2025-09-01' ); }
            public static function sanitize_event( string $event ): ?string { return '' === $event ? null : $event; }
            public static function sanitize_type( string $type ): string { return $type; }
        }
    }
}

namespace FoodBankManager\Attendance {
    if ( ! class_exists( AttendanceRepo::class, false ) ) {
        class AttendanceRepo {
            public static function period_totals( $since, array $filters ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
                return array( 'present' => 1, 'households' => 1, 'no_shows' => 0, 'in_person' => 1, 'delivery' => 0, 'voided' => 0 );
            }
            public static function daily_present_counts( $since, array $filters ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
                return array();
            }
        }
    }
}
