<?php // phpcs:ignoreFile
declare(strict_types=1);

namespace FBM\Auth;

final class Capabilities {
    /**
     * List of plugin capabilities.
     *
     * @return string[]
     */
    public static function all(): array {
        return [
            'fb_manage_dashboard',
            'fb_manage_attendance',
            'fb_manage_database',
            'fb_manage_forms',
            'fb_manage_emails',
            'fb_manage_settings',
            'fb_manage_diagnostics',
            'fb_manage_permissions',
            'fb_manage_theme',
            'fb_view_sensitive',
        ];
    }

    /**
     * Ensure the Administrator role has all FBM caps (idempotent).
     */
    public static function ensure_for_admin(): void {
        if (!function_exists('get_role')) {
            return;
        }
        $role = get_role('administrator');
        if (!$role) {
            return;
        }
        foreach (self::all() as $cap) {
            $role->add_cap($cap);
        }
    }

    /** @return string[] */
    public static function managerRoleCaps(): array {
        return self::all();
    }

    /** @return string[] */
    public static function viewerRoleCaps(): array {
        return [
            'fb_manage_dashboard',
            'fb_manage_attendance',
            'fb_manage_database',
        ];
    }
}

\class_alias(__NAMESPACE__ . '\\Capabilities', 'FoodBankManager\\Auth\\Capabilities');
