<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Core;

final class Install {
    private const TRANSIENT = 'fbm_dup_plugins';
    private const CANONICAL_DIR = 'foodbank-manager';

    public static function detect_duplicates(): void {
        if (!function_exists('get_plugins') || !current_user_can('manage_options')) {
            return;
        }
        $plugins = get_plugins();
        $dups = [];
        foreach ($plugins as $basename => $data) {
            $name = (string)($data['Name'] ?? '');
            $dir  = explode('/', (string)$basename)[0];
            if ($name === 'FoodBank Manager' && $dir !== self::CANONICAL_DIR) {
                $dups[] = (string)$basename;
            }
        }
        if ($dups) {
            $exp = defined('MINUTE_IN_SECONDS') ? MINUTE_IN_SECONDS : 60;
            set_transient(self::TRANSIENT, $dups, $exp);
        } else {
            delete_transient(self::TRANSIENT);
        }
    }

    /**
     * @return array<int,string>
     */
    public static function duplicates(): array {
        $v = get_transient(self::TRANSIENT);
        return is_array($v) ? array_values(array_map('strval', $v)) : [];
        }

    public static function consolidate(): int {
        $dups = self::duplicates();
        if (!$dups) {
            return 0;
        }
        deactivate_plugins($dups);
        if (current_user_can('delete_plugins')) {
            delete_plugins($dups);
        }
        update_option('fbm_last_consolidation', ['ts' => time(), 'count' => count($dups)]);
        delete_transient(self::TRANSIENT);
        return count($dups);
    }
}
