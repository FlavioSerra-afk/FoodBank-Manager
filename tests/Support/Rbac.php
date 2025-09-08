<?php declare(strict_types=1);

/** Deterministic caps for tests */
function fbm_grant_caps(array $caps): void {
    $GLOBALS['fbm_user_caps'] = [];
    foreach ($caps as $cap) {
        $GLOBALS['fbm_user_caps'][(string) $cap] = true;
    }
}

function fbm_grant_viewer(): void {
    fbm_grant_caps(['fb_manage_dashboard']);
}

function fbm_grant_manager(): void {
    fbm_grant_caps([
        'fb_manage_dashboard',
        'fb_manage_attendance',
        'fb_manage_database',
        'fb_manage_forms',
        'fb_manage_emails',
        'fb_manage_settings',
        'fb_manage_diagnostics',
        'fb_manage_permissions',
        'fb_manage_theme',
    ]);
}

function fbm_grant_admin(): void {
    fbm_grant_caps([
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
        'manage_options',
    ]);
}

/** Minimal page→cap mapping used by admin page tests */
function fbm_required_cap_for_page(string $slug): string {
    static $map = [
        'fbm'               => 'fb_manage_dashboard',
        'fbm_attendance'    => 'fb_manage_attendance',
        'fbm_database'      => 'fb_manage_database',
        'fbm_forms'         => 'fb_manage_forms',
        'fbm_emails'        => 'fb_manage_emails',
        'fbm_settings'      => 'fb_manage_settings',
        'fbm_diagnostics'   => 'fb_manage_diagnostics',
        'fbm_permissions'   => 'fb_manage_permissions',
        'fbm_theme'         => 'fb_manage_theme',
        'fbm_shortcodes'    => 'fb_manage_forms', // shortcodes builder is admin-only; tie to forms
    ];
    return $map[$slug] ?? 'fb_manage_dashboard';
}

/** Grant exactly what a given page needs */
function fbm_grant_for_page(string $page_slug): void {
    fbm_grant_caps([fbm_required_cap_for_page($page_slug)]);
}
