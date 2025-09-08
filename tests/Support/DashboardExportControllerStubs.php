<?php
namespace FoodBankManager\Shortcodes {
    class Dashboard {
        public static function sanitize_period(string $period): string { return $period; }
        public static function since_from_period(string $period): \DateTimeImmutable { return new \DateTimeImmutable('2025-09-01'); }
        public static function sanitize_event(string $event): ?string { return '' === $event ? null : $event; }
        public static function sanitize_type(string $type): string { return $type; }
    }
}

namespace FoodBankManager\Exports {
    class DashboardCsv {
        public static function render($totals, $series, $period, $filters): string { return "Metric,Count\n"; }
    }
}
