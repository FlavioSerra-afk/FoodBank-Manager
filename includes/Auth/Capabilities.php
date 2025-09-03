<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Auth;

final class Capabilities {
    /** @return string[] */
    public static function all(): array {
        return [
            'fb_manage_dashboard',
            'fb_manage_attendance',
            'fb_manage_database',
            'fb_manage_forms',
            'fb_manage_settings',
            'fb_manage_diagnostics',
            'fb_manage_permissions',
            'fb_manage_theme',
            'fb_view_sensitive',
        ];
    }

    /** @return string[] */
    public static function managerRoleCaps(): array {
        // full manager access
        return self::all();
    }

    /** @return string[] */
    public static function viewerRoleCaps(): array {
        // basic read-only (adjust if you have a viewer role)
        return [
            'fb_manage_dashboard',
            'fb_manage_attendance',
            'fb_manage_database',
        ];
    }
}
