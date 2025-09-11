<?php // phpcs:ignoreFile
declare(strict_types=1);

namespace FBM\Core;

final class Capabilities {
    /** @var string[] Canonical capabilities list. */
    public const ALL = [
        'fb_manage_dashboard',
        'fb_manage_attendance',
        'fb_manage_database',
        'fb_manage_forms',
        'fbm_manage_forms',
        'fb_manage_emails',
        'fb_manage_reports',
        'fb_manage_settings',
        'fb_manage_diagnostics',
        'fb_manage_permissions',
        'fb_manage_theme',
        'fbm_manage_events',
        'fb_view_sensitive',
    ];

    /**
     * Return all canonical plugin capabilities.
     *
     * @return string[]
     */
    public static function all(): array {
        return self::ALL;
    }
}

\class_alias(__NAMESPACE__ . '\\Capabilities', 'FoodBankManager\\Core\\Capabilities');
