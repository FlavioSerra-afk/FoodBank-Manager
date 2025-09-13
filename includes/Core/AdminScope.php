<?php // phpcs:ignoreFile
/**
 * Admin page scope helpers.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Core;

use function sanitize_key;

final class AdminScope {
/** Exact slugs that may be themed under admin.php?page= */
public const FBM_PAGE_SLUGS = array(
'fbm',
'fbm_attendance',
'fbm_reports',
'fbm_jobs',
'fbm_scan',
'fbm_database',
'fbm_forms',
'fbm_emails',
'fbm_settings',
'fbm_permissions',
'fbm_diagnostics',
'fbm_theme',
'fbm_shortcodes',
);

/** Returns the sanitized ?page= slug or '' */
public static function current_page_slug(): string {
return isset( $_GET['page'] ) ? sanitize_key( (string) $_GET['page'] ) : '';
}

/** True if the current admin request is an allowed FBM page */
public static function is_fbm_admin_request(): bool {
$slug = self::current_page_slug();
return $slug && in_array( $slug, self::FBM_PAGE_SLUGS, true );
}

/** Adds scoping class for CSS targeting (string, not array) */
public static function add_admin_body_class( string $classes ): string {
if ( self::is_fbm_admin_request() ) {
$classes .= ' fbm-themed ';
}
return $classes;
}
}
