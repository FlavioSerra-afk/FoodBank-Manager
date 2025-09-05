<?php
declare(strict_types=1);

namespace FoodBankManager\Attendance {
    class AttendanceRepo {
        public static function find_by_application_id(int $id): array {
            return array();
        }
        public static function count_present(string $date): int { return 0; }
        public static function count_unique_households(string $date): int { return 0; }
        public static function count_no_shows(string $date): int { return 0; }
        /** @return array<string,int> */
        public static function count_by_type(string $date): array { return array('in_person'=>0,'delivery'=>0); }
        public static function count_voided(string $date): int { return 0; }
        /** @return array<int,int> */
        public static function daily_present_counts(string $start, string $end): array { return array(); }
        /** @return array<int,array<string,mixed>> */
        public static function filter(array $args): array { return array(); }
    }
}
