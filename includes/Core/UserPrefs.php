<?php
/**
 * User dashboard preference helpers.
 *
 * @package FBM\Core
 */

declare(strict_types=1);

namespace FBM\Core;

use function get_user_meta;
use function update_user_meta;
use function array_intersect_key;
use function array_flip;

/**
 * Tiny helper for per-user dashboard filter persistence.
 */
final class UserPrefs {
	/**
	 * Get saved dashboard filters for a user.
	 *
	 * @param int $user_id User ID.
	 * @return array<string,mixed>
	 */
	public static function get_dashboard_filters( int $user_id ): array {
		$vals = get_user_meta( $user_id, 'fbm_dashboard_filters', true );
		return is_array( $vals ) ? $vals : array();
	}

	/**
	 * Persist dashboard filters for a user.
	 *
	 * @param int   $user_id User ID.
	 * @param array<string,mixed> $filters Filters to store.
	 * @return void
	 */
	public static function set_dashboard_filters( int $user_id, array $filters ): void {
		$allow = array( 'date_from', 'date_to', 'preset', 'tags', 'compare', 'event', 'type', 'policy_only' );
		$clean = array_intersect_key( $filters, array_flip( $allow ) );
		update_user_meta( $user_id, 'fbm_dashboard_filters', $clean );
	}
}
