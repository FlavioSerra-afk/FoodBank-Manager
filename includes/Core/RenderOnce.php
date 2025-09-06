<?php
/**
 * Global render-once guard for admin pages.
 *
 * @package FoodBankManager
 */

declare( strict_types=1 );

namespace FBM\Core;

/**
 * Global render-once registry.
 */
final class RenderOnce {
	/**
	 * Render counts keyed by screen.
	 *
	 * @var array<string,int>
	 */
	private static $counts = array();

	/**
	 * Whether the given screen has already been rendered.
	 *
	 * @param string $screen_key Screen identifier.
	 */
	public static function already( string $screen_key ): bool {
		return isset( self::$counts[ $screen_key ] );
	}

	/**
	 * Mark a screen as entered and increment its count.
	 *
	 * @param string $screen_key Screen identifier.
	 */
	public static function enter( string $screen_key ): void {
		self::$counts[ $screen_key ] = ( self::$counts[ $screen_key ] ?? 0 ) + 1;
	}

	/**
	 * Get all render counts for this request.
	 *
	 * @return array<string,int>
	 */
	public static function all(): array {
		return self::$counts;
	}
}
