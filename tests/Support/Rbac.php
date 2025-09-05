<?php declare(strict_types=1);

/** Deterministic helpers for setting capabilities in tests. */
function fbm_grant_caps(array $caps): void {
    $GLOBALS['fbm_user_caps'] = [];
    foreach ($caps as $key => $cap) {
        $name = is_int($key) ? $cap : $key;
        $GLOBALS['fbm_user_caps'][(string)$name] = true;
    }
}
function fbm_grant_admin_only(): void {
    // emulate a real admin with core powers but *no* FBM caps
    fbm_grant_caps(['manage_options' => true]);
}
function fbm_grant_fbm_all(): void {
    fbm_grant_caps([
        'fb_manage_dashboard','fb_manage_attendance','fb_manage_database','fb_manage_forms',
        'fb_manage_emails','fb_manage_settings','fb_manage_diagnostics','fb_manage_permissions',
        'fb_manage_theme','fb_view_sensitive'
    ]);
}
function fbm_clear_caps(): void { $GLOBALS['fbm_user_caps'] = []; }
