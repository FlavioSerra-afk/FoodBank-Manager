<?php declare(strict_types=1);

namespace {

/** Deterministic caps for tests */
function fbm_grant_caps(array $caps): void {
    $GLOBALS['fbm_user_caps'] = [];
    foreach ($caps as $cap) {
        $GLOBALS['fbm_user_caps'][(string) $cap] = true;
    }
    $GLOBALS['fbm_current_user_roles'] = [];
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
        'fbm_manage_forms',
        'fb_manage_emails',
        'fb_manage_settings',
        'fb_manage_diagnostics',
        'fb_manage_permissions',
        'fb_manage_theme',
        'fbm_manage_events',
    ]);
}

function fbm_grant_admin(): void {
    fbm_grant_caps([
        'fb_manage_dashboard',
        'fb_manage_attendance',
        'fb_manage_database',
        'fb_manage_forms',
        'fbm_manage_forms',
        'fb_manage_emails',
        'fb_manage_settings',
        'fb_manage_diagnostics',
        'fb_manage_permissions',
        'fb_manage_theme',
        'fbm_manage_events',
        'fb_view_sensitive',
        'manage_options',
    ]);
    $GLOBALS['fbm_current_user_roles'] = ['administrator'];
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
        'fbm_events'       => 'fbm_manage_events',
        'fbm_scan'         => 'fbm_manage_events',
        'fbm_form_builder' => 'fbm_manage_forms',
        'fbm_shortcodes'    => 'fb_manage_forms', // shortcodes builder is admin-only; tie to forms
    ];
    return $map[$slug] ?? 'fb_manage_dashboard';
}

/** Grant exactly what a given page needs */
function fbm_grant_for_page(string $page_slug): void {
    fbm_grant_caps([fbm_required_cap_for_page($page_slug)]);
}
}

namespace Tests\Support {

final class Rbac {
    public static function revokeAll(): void {
        \fbm_grant_caps(array());
    }

    public static function grantViewer(): void {
        \fbm_grant_viewer();
    }

    public static function grantManager(): void {
        \fbm_grant_manager();
    }

    public static function grantAdmin(): void {
        \fbm_grant_admin();
    }

    public static function grantForPage(string $page_slug): void {
        \fbm_grant_for_page($page_slug);
    }
}

}
