<?php
/**
 * Shortcodes admin page.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

/**
 * Shortcodes admin page.
 */
final class ShortcodesPage {
		/**
		 * Register menu item.
		 */
	public static function register_menu(): void {
			add_submenu_page(
				'fbm-dashboard',
				esc_html__( 'Shortcodes', 'foodbank-manager' ),
				esc_html__( 'Shortcodes', 'foodbank-manager' ),
				'fb_manage_forms',
				'fbm-shortcodes',
				array( self::class, 'route' )
			);
	}

		/**
		 * Render the page.
		 */
	public static function route(): void {
		if ( ! current_user_can( 'fb_manage_forms' ) ) {
				wp_die(
					esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ),
					'',
					array( 'response' => 403 )
				);
		}
		$shortcodes = self::discover();
		/* @psalm-suppress UnresolvableInclude */
		require FBM_PATH . 'templates/admin/shortcodes.php';
	}

		/**
		 * Discover shortcodes and their attributes.
		 *
		 * @return array<int,array{tag:string,atts:array<string,mixed>}> Shortcodes.
		 */
	public static function discover(): array {
			$dir = FBM_PATH . 'includes/Shortcodes';
		$files   = glob( $dir . '/*.php' );
		if ( false === $files ) {
				return array();
		}
			$out = array();
		foreach ( $files as $file ) {
				$class = basename( $file, '.php' );
				$snake = strtolower( preg_replace( '/(?<!^)[A-Z]/', '_$0', $class ) );
				$tag   = 'fbm_' . $snake;
				$atts  = self::extract_atts( $file );
				$out[] = array(
					'tag'  => $tag,
					'atts' => $atts,
				);
		}
			return $out;
	}

		/**
		 * Extract default attributes from a shortcode handler file.
		 *
		 * @param string $file File path.
		 * @return array<string,mixed>
		 */
	private static function extract_atts( string $file ): array {
		$src = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local file.
		if ( false === $src ) {
				return array();
		}
		if ( preg_match( '/shortcode_atts\s*\(\s*array\((.*?)\)\s*,/s', $src, $m ) === 1 ) {
				$code     = 'return array(' . $m[1] . ');';
				$defaults = eval( $code ); // phpcs:ignore Squiz.PHP.Eval.Discouraged -- Introspecting own code.
			if ( is_array( $defaults ) ) {
					return $defaults;
			}
		}
			return array();
	}
}
