<?php
/**
 * Shortcode registry.
 *
 * @package FBM\Shortcodes
 */

declare(strict_types=1);

namespace FBM\Shortcodes;

use function add_action;
use function add_shortcode;

/**
 * Handles registration of shortcodes.
 */
final class Shortcodes {
        /**
         * Bootstrap shortcode registration.
         */
        public static function register(): void {
                static $boot = false;
                if ( $boot ) {
                        return;
                }
                $boot = true;

                if ( function_exists( 'add_action' ) ) {
                        add_action( 'init', array( __CLASS__, 'add_shortcodes' ) );
                }
                self::add_shortcodes();
        }

        /**
         * Actually register the shortcodes.
         */
        public static function add_shortcodes(): void {
                static $done = false;
                if ( $done ) {
                        return;
                }
                $done = true;

                add_shortcode( 'fbm_form', array( FormShortcode::class, 'render' ) );
                add_shortcode( 'fbm_dashboard', array( DashboardShortcode::class, 'render' ) );
        }
}
