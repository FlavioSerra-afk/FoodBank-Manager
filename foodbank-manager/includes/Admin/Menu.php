<?php

declare(strict_types=1);

namespace FoodBankManager\Admin;

class Menu
{
    public static function register(): void
    {
        add_menu_page(
            __('FoodBank', 'foodbank-manager'),
            __('FoodBank', 'foodbank-manager'),
            'fb_read_entries',
            'fbm_dashboard',
            [self::class, 'render_dashboard'],
            'dashicons-carrot',
            20
        );
        add_submenu_page('fbm_dashboard', __('Dashboard', 'foodbank-manager'), __('Dashboard', 'foodbank-manager'), 'fb_read_entries', 'fbm_dashboard', [self::class, 'render_dashboard']);
        add_submenu_page('fbm_dashboard', __('Attendance', 'foodbank-manager'), __('Attendance', 'foodbank-manager'), 'attendance_view', 'fbm_attendance', [self::class, 'render_attendance']);
        add_submenu_page('fbm_dashboard', __('Database', 'foodbank-manager'), __('Database', 'foodbank-manager'), 'fb_read_entries', 'fbm_database', [self::class, 'render_database']);
        add_submenu_page('fbm_dashboard', __('Forms', 'foodbank-manager'), __('Forms', 'foodbank-manager'), 'fb_manage_forms', 'fbm_forms', [self::class, 'render_forms']);
        add_submenu_page('fbm_dashboard', __('Email Templates', 'foodbank-manager'), __('Email Templates', 'foodbank-manager'), 'fb_manage_emails', 'fbm_emails', [self::class, 'render_emails']);
        add_submenu_page('fbm_dashboard', __('Settings', 'foodbank-manager'), __('Settings', 'foodbank-manager'), 'fb_manage_settings', 'fbm_settings', [self::class, 'render_settings']);
        add_submenu_page('fbm_dashboard', __('Diagnostics', 'foodbank-manager'), __('Diagnostics', 'foodbank-manager'), 'fb_manage_settings', 'fbm_diagnostics', [self::class, 'render_diagnostics']);
    }

    private static function render_template(string $template): void
    {
        $path = dirname(__DIR__, 2) . '/templates/admin/' . $template . '.php';
        if (file_exists($path)) {
            include $path;
        } else {
            echo \esc_html__('Template missing.', 'foodbank-manager');
        }
    }

    public static function render_dashboard(): void
    {
        self::render_template('dashboard');
    }

    public static function render_attendance(): void
    {
        self::render_template('attendance');
    }

    public static function render_database(): void
    {
        self::render_template('database');
    }

    public static function render_forms(): void
    {
        self::render_template('forms');
    }

    public static function render_emails(): void
    {
        self::render_template('emails');
    }

    public static function render_settings(): void
    {
        self::render_template('settings');
    }

    public static function render_diagnostics(): void
    {
        self::render_template('diagnostics');
    }
}
