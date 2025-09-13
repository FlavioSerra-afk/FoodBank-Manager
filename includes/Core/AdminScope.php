<?php
/**
 * Admin page scope helpers.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Core;

use function sanitize_key;

final class AdminScope {
	/** Exact slugs allowed to receive theming under admin.php?page= */
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

	/** True if current admin request is one of our slugs */
	public static function is_fbm_page_request(): bool {
		if ( empty( $_GET['page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return false;
		}
		$page = sanitize_key( (string) $_GET['page'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return in_array( $page, self::FBM_PAGE_SLUGS, true );
	}

	/** Append a scoping class on whitelisted screens */
	public static function add_body_class( string $classes ): string {
		if ( self::is_fbm_page_request() ) {
			$classes .= ' fbm-themed ';
		}
		return $classes;
	}
}
