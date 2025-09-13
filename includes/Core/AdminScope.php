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
    public const SLUGS = array(
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
    public static function slug(): string {
        return isset( $_GET['page'] ) ? sanitize_key( (string) $_GET['page'] ) : '';
    }

    /** True if the current admin request is an allowed FBM page */
    public static function is_fbm_admin(): bool {
        $slug = self::slug();
        return $slug && in_array( $slug, self::SLUGS, true );
    }

    /** Alias for is_fbm_admin() */
    public static function is_fbm_admin_request(): bool {
        return self::is_fbm_admin();
    }

}
