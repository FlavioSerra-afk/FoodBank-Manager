<?php
// phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Auth;

final class Roles {
    /**
     * Return all plugin capabilities.
     *
     * @return string[]
     */
    public static function caps(): array {
        return Capabilities::all();
    }

    /**
     * Activate roles and grant capabilities to Administrator.
     */
    public static function activate(): void {
        // Viewer role.
        add_role(
            'foodbank_viewer',
            'FoodBank Viewer',
            [
                'read'               => true,
                'fb_read_entries'    => true,
                'fb_export_entries'  => true,
                'attendance_checkin' => true,
                'attendance_view'    => true,
                'attendance_export'  => true,
            ]
        );

        // Manager role.
        add_role(
            'foodbank_manager',
            'FoodBank Manager',
            [
                'read'               => true,
                'fb_read_entries'    => true,
                'fb_edit_entries'    => true,
                'fb_delete_entries'  => true,
                'fb_export_entries'  => true,
                'attendance_checkin' => true,
                'attendance_view'    => true,
                'attendance_export'  => true,
                'attendance_admin'   => true,
            ]
        );

        self::grantCapsToAdmin();
    }

    /**
     * Ensure Administrator role always has all plugin capabilities.
     */
    public static function grantCapsToAdmin(): void {
        $admin = get_role('administrator');
        if (! $admin) {
            return;
        }
        foreach (self::caps() as $cap) {
            if (! $admin->has_cap($cap)) {
                $admin->add_cap($cap);
            }
        }
    }
}
