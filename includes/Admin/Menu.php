<?php

declare(strict_types=1);

namespace FoodBankManager\Admin;

final class Menu {
    /**
     * Hook menu registration.
     */
    public static function register(): void {
        add_action('admin_menu', [__CLASS__, 'addMenu']);
    }

    /**
     * Add top-level and submenu pages.
     */
    public static function addMenu(): void {
        $cap = current_user_can('fb_read_entries') ? 'fb_read_entries' : 'manage_options';

        add_menu_page(
            __('FoodBank', 'foodbank-manager'),
            __('FoodBank', 'foodbank-manager'),
            $cap,
            'fbm-dashboard',
            [__CLASS__, 'renderDashboard'],
            'dashicons-clipboard',
            58
        );

        self::submenu($cap, 'fbm-dashboard', 'fbm-attendance',   __('Attendance', 'foodbank-manager'),   [\FoodBankManager\Admin\AttendancePage::class, 'route']);
        self::submenu($cap, 'fbm-dashboard', 'fbm-database',     __('Database', 'foodbank-manager'),     [\FoodBankManager\Admin\DatabasePage::class, 'route']);
        self::submenu($cap, 'fbm-dashboard', 'fbm-forms',        __('Forms', 'foodbank-manager'),        [__CLASS__, 'renderForms']);
        self::submenu($cap, 'fbm-dashboard', 'fbm-emails',       __('Email Templates', 'foodbank-manager'), [__CLASS__, 'renderEmails']);
        self::submenu($cap, 'fbm-dashboard', 'fbm-settings',     __('Settings', 'foodbank-manager'),     [__CLASS__, 'renderSettings']);
        self::submenu($cap, 'fbm-dashboard', 'fbm-theme',        __('Design & Theme', 'foodbank-manager'), [__CLASS__, 'renderTheme']);
        self::submenu('fb_manage_permissions', 'fbm-dashboard', 'fbm-permissions', __('Permissions', 'foodbank-manager'), [\FoodBankManager\Admin\PermissionsPage::class, 'route']);
        self::submenu($cap, 'fbm-dashboard', 'fbm-diagnostics',  __('Diagnostics', 'foodbank-manager'),  [__CLASS__, 'renderDiagnostics']);
    }

    private static function submenu(string $cap, string $parent, string $slug, string $title, callable $cb): void {
        add_submenu_page($parent, $title, $title, $cap, $slug, $cb, 10);
    }

    private static function safeInclude(string $template): void {
        $path = \FBM_PATH . 'templates/admin/' . $template;
        if (is_readable($path)) {
            require $path;
        } else {
            echo '<div class="wrap"><h1>' . esc_html__('FoodBank', 'foodbank-manager') . '</h1><p>'
               . esc_html__('Template missing:', 'foodbank-manager') . ' ' . esc_html($template) . '</p></div>';
        }
    }

    /** Render dashboard page. */
    public static function renderDashboard(): void  { self::safeInclude('dashboard.php'); }
    /** Render attendance page. */
    public static function renderAttendance(): void { self::safeInclude('attendance.php'); }
    /** Render database page. */
    public static function renderDatabase(): void   { \FoodBankManager\Admin\DatabasePage::route(); }
    /** Render forms page. */
    public static function renderForms(): void      { self::safeInclude('forms.php'); }
    /** Render email templates page. */
    public static function renderEmails(): void     { self::safeInclude('emails.php'); }
    /** Render settings page. */
    public static function renderSettings(): void   { self::safeInclude('settings.php'); }
    /** Render theme page. */
    public static function renderTheme(): void      { self::safeInclude('theme.php'); }
    /** Render diagnostics page. */
    public static function renderDiagnostics(): void{ self::safeInclude('diagnostics.php'); }
}
