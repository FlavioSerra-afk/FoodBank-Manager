<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Core;

final class Install {
    private const TRANSIENT = 'fbm_install_scan';
    private const CANONICAL = 'foodbank-manager/foodbank-manager.php';
    private const TTL = 300; // 5 minutes

    /** @var array<string,mixed>|null */
    private static ?array $scanCache = null;

    /**
     * Perform a fresh scan of installed plugins.
     *
     * @return array{canonical:string,duplicates:array<int,array{basename:string,version:string,dir:string}>}
     */
    public static function detectDuplicates(): array {
        if (!function_exists('get_plugins')) {
            return ['canonical' => self::CANONICAL, 'duplicates' => []];
        }
        $plugins = get_plugins();
        $dups    = [];
        foreach ($plugins as $basename => $data) {
            $name = (string)($data['Name'] ?? '');
            if ($name !== 'FoodBank Manager') {
                continue;
            }
            if ($basename === self::CANONICAL) {
                continue;
            }
            $dir = explode('/', (string) $basename)[0];
            $dups[] = [
                'basename' => (string) $basename,
                'version'  => (string)($data['Version'] ?? ''),
                'dir'      => (string) $dir,
            ];
        }
        return [
            'canonical'  => self::CANONICAL,
            'duplicates' => $dups,
        ];
    }

    /**
     * Return cached scan results, performing a scan if needed.
     *
     * @return array{canonical:string,duplicates:array<int,array{basename:string,version:string,dir:string}>}
     */
    public static function getCachedScan(): array {
        if (is_array(self::$scanCache)) {
            return self::$scanCache;
        }
        $scan = get_transient(self::TRANSIENT);
        if (!is_array($scan)) {
            $scan = self::detectDuplicates();
            $exp  = defined('MINUTE_IN_SECONDS') ? 5 * MINUTE_IN_SECONDS : self::TTL;
            set_transient(self::TRANSIENT, $scan, $exp);
        }
        self::$scanCache = $scan;
        return $scan;
    }

    /**
     * Deactivate duplicates and optionally delete their plugin folders.
     *
     * @param bool $delete Whether to delete duplicate folders after deactivation.
     * @return array{timestamp:int,deactivated:int,deleted:int,items:array<int,string>,count:int}
     */
    public static function consolidate(bool $delete = true): array {
        $scan = self::$scanCache;
        if (!is_array($scan)) {
            $scan = get_transient(self::TRANSIENT);
            if (!is_array($scan)) {
                $scan = ['canonical' => self::CANONICAL, 'duplicates' => []];
            }
        }
        $items = array_map(static fn(array $d) => $d['basename'], $scan['duplicates']);
        if (!$items) {
            $log = [
                'timestamp'   => time(),
                'deactivated' => 0,
                'deleted'     => 0,
                'items'       => [],
                'count'       => 0,
            ];
            update_option('fbm_last_consolidation', $log, false); // @phpstan-ignore-line
            return $log;
        }
        deactivate_plugins($items);
        $deleted = 0;
        if ($delete && current_user_can('delete_plugins')) {
            delete_plugins($items);
            $deleted = count($items);
        }
        $log = [
            'timestamp'   => time(),
            'deactivated' => count($items),
            'deleted'     => $deleted,
            'items'       => $items,
            'count'       => count($items),
        ];
        update_option('fbm_last_consolidation', $log, false); // @phpstan-ignore-line
        delete_transient(self::TRANSIENT);
        self::$scanCache = null;
        return $log;
    }

    public static function onActivate(): void {
        $result = self::consolidate(true);
        update_option('fbm_last_activation_consolidation', [
            'timestamp'   => time(),
            'deactivated' => (int)$result['deactivated'],
            'deleted'     => (int)$result['deleted'],
            'items'       => (array)$result['items'],
        ], false); // @phpstan-ignore-line
    }

    // --- Legacy wrappers -------------------------------------------------

    /** @deprecated */
    public static function detect_duplicates(): void {
        $scan = self::detectDuplicates();
        $exp  = defined('MINUTE_IN_SECONDS') ? 5 * MINUTE_IN_SECONDS : self::TTL;
        set_transient(self::TRANSIENT, $scan, $exp);
        self::$scanCache = $scan;
    }

    /**
     * @deprecated
     * @return array<int,string>
     */
    public static function duplicates(): array {
        $scan = self::getCachedScan();
        return array_values(array_map(static fn(array $d): string => $d['basename'], $scan['duplicates']));
    }
}
