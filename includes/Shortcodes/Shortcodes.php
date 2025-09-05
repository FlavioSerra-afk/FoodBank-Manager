<?php
/**
 * Shortcode registry.
 *
 * @package FBM\Shortcodes
 */

declare(strict_types=1);

namespace FBM\Shortcodes;

use function add_shortcode;

/**
 * Handles registration of shortcodes.
 */
final class Shortcodes {
		/**
		 * Register plugin shortcodes.
		 */
	public static function register(): void {
			static $done = false;
		if ( $done ) {
				return;
		}
			$done = true;

			add_shortcode( 'fbm_form', array( FormShortcode::class, 'render' ) );
			// (If we have other shortcodes, register here too)
	}
}
