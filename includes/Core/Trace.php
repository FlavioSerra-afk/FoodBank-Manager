<?php
/**
 * Render trace helper.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Core;

/**
 * Simple trace helper.
 */
final class Trace {
	/**
	 * Render counts.
	 *
	 * @var array<string,int>
	 */
	private static array $counts = array();

	/**
	 * Emit a trace comment and increment count.
	 *
	 * @param string $key Trace key.
	 */
	public static function mark( string $key ): void {
		$count                = ( self::$counts[ $key ] ?? 0 ) + 1;
		self::$counts[ $key ] = $count;
		// Harmless HTML comment, safe to echo.
		echo "\n<!-- fbm-render {$key} pass={$count} -->\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Get render counts.
	 *
	 * @return array<string,int>
	 */
	public static function counts(): array {
		return self::$counts;
	}
}
