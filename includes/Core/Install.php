<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Core;

final class Install {
    private const TRANSIENT = 'fbm_dup_plugins';
    private const CANONICAL_DIR = 'foodbank-manager';

    /** @var array<string,mixed>|null */
    private static ?array $cache = null;

    /**
     * Detect duplicate plugin copies and cache the result.
     *
     * @return array{canonical:string,duplicates:array<int,array{basename:string,version:string,dir:string}>}
     */
    public static function detectDuplicates(): array {
        if (self::$cache !== null) {
            return self::$cache;
        }
        $cached = get_transient(self::TRANSIENT);
        if (is_array($cached) && isset($cached['canonical'], $cached['duplicates'])) {
            return self::$cache = $cached;
        }
        $canonical = self::CANONICAL_DIR . '/foodbank-manager.php';
        $dups      = [];
        if (function_exists('get_plugins') && current_user_can('manage_options')) {
            $plugins = get_plugins();
            foreach ($plugins as $basename => $data) {
                $name    = (string)($data['Name'] ?? '');
                if ($name !== 'FoodBank Manager') {
                    continue;
                }
                $dir     = explode('/', (string) $basename)[0];
                $version = (string) ($data['Version'] ?? '');
                if ($dir === self::CANONICAL_DIR) {
                    $canonical = (string) $basename;
                    continue;
                }
                $dups[] = [
                    'basename' => (string) $basename,
                    'version'  => $version,
                    'dir'      => $dir,
                ];
            }
        }
        $result = [
            'canonical'  => $canonical,
            'duplicates' => $dups,
        ];
        if ($dups) {
            $exp = defined('MINUTE_IN_SECONDS') ? 5 * MINUTE_IN_SECONDS : 300;
            set_transient(self::TRANSIENT, $result, $exp);
        } else {
            delete_transient(self::TRANSIENT);
        }
        return self::$cache = $result;
    }

    /** @deprecated Back-compat for older hooks. */
    public static function detect_duplicates(): void { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
        self::detectDuplicates();
    }

    /**
     * Get list of non-canonical plugin duplicates.
     *
     * @return array<int,array{basename:string,version:string,dir:string}>
     */
    public static function duplicates(): array {
        $v = self::detectDuplicates();
        return $v['duplicates'];
    }

    /**
     * Deactivate and optionally delete duplicate installs.
     *
     * @param bool $delete Whether to delete duplicates when allowed.
     * @return array{deactivated:int,deleted:int,items:array<int,string>}
     */
    public static function consolidate(bool $delete = true): array {
        $dups = self::duplicates();
        $basenames = array_map(static fn($d) => $d['basename'], $dups);
        if (!$basenames) {
            $log = [
                'ts'          => time(),
                'deactivated' => 0,
                'deleted'     => 0,
                'items'       => [],
            ];
            self::log($log);
            return ['deactivated' => 0, 'deleted' => 0, 'items' => []];
        }
        deactivate_plugins($basenames);
        $deleted = 0;
        if ($delete && current_user_can('delete_plugins')) {
            delete_plugins($basenames);
            $deleted = count($basenames);
        }
        $log = [
            'ts'          => time(),
            'deactivated' => count($basenames),
            'deleted'     => $deleted,
            'items'       => $basenames,
        ];
        self::log($log);
        delete_transient(self::TRANSIENT);
        self::$cache = null;
        return ['deactivated' => count($basenames), 'deleted' => $deleted, 'items' => $basenames];
    }

    /**
     * Record or fetch the last consolidation log.
     *
     * @param array{ts:int,deactivated:int,deleted:int,items:array<int,string>}|null $entry Log entry.
     * @return array{ts:int,deactivated:int,deleted:int,items:array<int,string>}
     */
    public static function log(?array $entry = null): array {
        $key = 'fbm_last_consolidation';
        if ($entry !== null) {
            update_option($key, $entry);
            return $entry;
        }
        $v = get_option($key, ['ts' => 0, 'deactivated' => 0, 'deleted' => 0, 'items' => []]);
        return [
            'ts'          => (int) ($v['ts'] ?? 0),
            'deactivated' => (int) ($v['deactivated'] ?? 0),
            'deleted'     => (int) ($v['deleted'] ?? 0),
            'items'       => is_array($v['items'] ?? null) ? array_map('strval', $v['items']) : [],
        ];
    }
}
