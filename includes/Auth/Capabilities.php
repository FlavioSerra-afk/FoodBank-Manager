<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Auth;

final class Capabilities {
    /**
     * Return all plugin capabilities.
     *
     * @return string[]
     */
    public static function all(): array {
        return [
            'fb_read_entries',
            'fb_edit_entries',
            'fb_delete_entries',
            'fb_export_entries',
            'fb_manage_forms',
            'fb_manage_settings',
            'fb_manage_emails',
            'fb_manage_encryption',
            'attendance_checkin',
            'attendance_view',
            'attendance_export',
            'attendance_admin',
            'read_sensitive',
            'fb_manage_permissions',
        ];
    }
}
