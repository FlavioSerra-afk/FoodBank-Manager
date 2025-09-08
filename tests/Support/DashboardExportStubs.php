<?php
declare(strict_types=1);

namespace FoodBankManager\Attendance {
    class AttendanceRepo {
        public static function period_totals($since, array $filters): array { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
            return array('total' => 1);
        }
        public static function daily_present_counts($since, array $filters): array { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
            return array();
        }
    }
}

namespace FoodBankManager\Shortcodes {
    class Dashboard {
        public static function sanitize_period(string $period): string { return $period; }
        public static function since_from_period(string $period): \DateTimeImmutable { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
            return new \DateTimeImmutable('2025-09-01');
        }
        public static function sanitize_event(string $event): ?string { return '' === $event ? null : $event; }
        public static function sanitize_type(string $type): string { return $type; }
    }
}

namespace FoodBankManager\Exports {
    class DashboardCsv {
        public static function render($totals, $series, $period, $filters): string { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
            return "Metric,Count\n";
        }
    }
}
