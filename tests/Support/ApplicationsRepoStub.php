<?php
declare(strict_types=1);

namespace FoodBankManager\Database {
    class ApplicationsRepo {
        public static array $entry = [
            'data' => ['first_name' => 'John', 'postcode' => 'AA1'],
            'pii'  => ['last_name' => 'Doe', 'email' => 'john@example.com'],
        ];
        public static function get(int $id) { return array('id' => $id); }
        public static function get_files_for_application(int $id): array { return array(); }
        public static function find_by_email(string $email): array { return array(array('id' => 1)); }
        public static function get_entry(int $id): ?array {
            $e = self::$entry;
            $e['id'] = $id;
            return $e;
        }
    }
}
