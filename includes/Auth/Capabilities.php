<?php // phpcs:ignoreFile
declare(strict_types=1);

namespace FBM\Auth;

use FBM\Core\Capabilities as CoreCaps;

final class Capabilities {
    /** @var bool Ensurer flag for tests */
    public static bool $ensured = false;

    /**
     * Caps required by the plugin.
     *
     * @var string[]
     */
    private const REQUIRED = CoreCaps::ALL;

    /**
     * Role → caps mapping (administrator always receives all caps).
     *
     * @var array<string, string[]>
     */
    private const ROLE_MAP = [
        'administrator' => self::REQUIRED,
    ];

    /**
     * Return all plugin capabilities.
     *
     * @return string[]
     */
    public static function all(): array {
        return self::REQUIRED;
    }

    /** @return string[] */
    public static function managerRoleCaps(): array {
        return self::REQUIRED;
    }

    /** @return string[] */
    public static function viewerRoleCaps(): array {
        return [
            'fb_manage_dashboard',
            'fb_manage_attendance',
            'fb_manage_database',
        ];
    }

    /**
     * Ensure mapped roles receive all required caps (idempotent).
     *
     * @return void
     */
    public static function ensure_for_admin(): void {
        if (self::$ensured) {
            return;
        }
        if (!function_exists('get_role')) {
            return;
        }
        foreach (self::ROLE_MAP as $role_name => $caps) {
            $role = get_role($role_name);
            if (!$role) {
                continue;
            }
            foreach ($caps as $cap) {
                $role->add_cap($cap, true);
            }
        }
        self::$ensured = true;
    }

    /**
     * Resolve effective caps for a user ID.
     *
     * @return array<string,bool>
     */
    public static function effective_caps_for_user(int $user_id): array {
        $caps = [];
        if (function_exists('get_user_by')) {
            $user = get_user_by('ID', $user_id);
            if ($user && isset($user->roles) && is_array($user->roles)) {
                foreach ($user->roles as $role) {
                    $r = function_exists('get_role') ? get_role((string) $role) : null;
                    if ($r && is_array($r->caps)) {
                        foreach ($r->caps as $cap => $grant) {
                            if ($grant) {
                                $caps[(string) $cap] = true;
                            }
                        }
                    }
                }
            }
        }
        $meta = function_exists('get_user_meta') ? get_user_meta($user_id, 'fbm_user_caps', true) : [];
        if (is_array($meta)) {
            foreach ($meta as $cap => $grant) {
                if (in_array($cap, self::REQUIRED, true)) {
                    if ($grant) {
                        $caps[$cap] = true;
                    } else {
                        unset($caps[$cap]);
                    }
                }
            }
        }
        return $caps;
    }
}

if (defined('FBM_TESTS')) {
    \add_action('fbm_test_reset_caps', static function (): void {
        Capabilities::$ensured = false;
    });
}

\class_alias(__NAMESPACE__ . '\\Capabilities', 'FoodBankManager\\Auth\\Capabilities');

