<?php

declare(strict_types=1);

namespace FoodBankManager\Auth;

class Roles
{
    private const ROLES = [
        'foodbank_viewer' => [
            'label' => 'FoodBank Viewer',
            'caps'  => [
                'read' => true,
                'fb_read_entries' => true,
                'fb_export_entries' => true,
                'attendance_checkin' => true,
                'attendance_view' => true,
                'attendance_export' => true,
            ],
        ],
        'foodbank_manager' => [
            'label' => 'FoodBank Manager',
            'caps'  => [
                'read' => true,
                'fb_read_entries' => true,
                'fb_edit_entries' => true,
                'fb_delete_entries' => true,
                'fb_export_entries' => true,
                'attendance_checkin' => true,
                'attendance_view' => true,
                'attendance_export' => true,
                'attendance_admin' => true,
            ],
        ],
    ];

    private const ADMIN_CAPS = [
        'fb_manage_forms',
        'fb_manage_settings',
        'fb_manage_emails',
        'fb_manage_encryption',
    ];

    public function register(): void
    {
        foreach (self::ROLES as $role => $data) {
            add_role($role, $data['label'], $data['caps']);
        }
        $admin = get_role('administrator');
        if ($admin) {
            foreach (self::ADMIN_CAPS as $cap) {
                $admin->add_cap($cap);
            }
            foreach (self::ROLES['foodbank_manager']['caps'] as $cap => $grant) {
                $admin->add_cap($cap);
            }
        }
    }

    public static function uninstall(): void
    {
        foreach (array_keys(self::ROLES) as $role) {
            remove_role($role);
        }
        $admin = get_role('administrator');
        if ($admin) {
            foreach (self::ADMIN_CAPS as $cap) {
                $admin->remove_cap($cap);
            }
            foreach (self::ROLES['foodbank_manager']['caps'] as $cap => $grant) {
                $admin->remove_cap($cap);
            }
        }
    }
}
