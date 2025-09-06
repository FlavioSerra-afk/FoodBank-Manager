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
     * Get list of non-canonical plugin basenames.
     *
     * @return array<int,string>
     */
    public static function duplicates(): array {
        $v = get_transient(self::TRANSIENT);
        return is_array($v) ? array_values(array_map('strval', $v)) : [];
    }

    /**
     * Deactivate and optionally delete duplicate installs.
     */
    public static function consolidate(): int {
        $dups = self::duplicates();
        if (!$dups) {
            self::log(0);
            return 0;
        }
        deactivate_plugins($dups);
        if (current_user_can('delete_plugins')) {
            delete_plugins($dups);
        }
        self::log(count($dups));
        delete_transient(self::TRANSIENT);
        return count($dups);
    }

    /**
     * Record or fetch the last consolidation log.
     *
     * @param int|null $count Number of duplicates removed.
     * @return array{ts:int,count:int}
     */
    public static function log(?int $count = null): array {
        $key = 'fbm_last_consolidation';
        if (null !== $count) {
            $entry = ['ts' => time(), 'count' => $count];
            update_option($key, $entry);
            return $entry;
        }
        $v = get_option($key, ['ts' => 0, 'count' => 0]);
        return [
            'ts' => (int)($v['ts'] ?? 0),
            'count' => (int)($v['count'] ?? 0),
        ];
    }
}
