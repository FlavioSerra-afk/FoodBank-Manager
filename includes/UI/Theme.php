<?php
/**
 * Theme helpers.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\UI;

use FoodBankManager\Core\Options;
use FoodBankManager\Core\Plugin;

/**
 * Theme UI helper.
 */
class Theme {
		/**
		 * Get CSS vars for frontend.
		 *
		 * @return array<string,string>
		 */
	public static function frontend_vars(): array {
			return self::vars( 'frontend' );
	}

		/**
		 * Get CSS vars for admin.
		 *
		 * @return array<string,string>
		 */
	public static function admin_vars(): array {
			return self::vars( 'admin' );
	}

		/**
		 * Enqueue frontend theme styles.
		 */
	public static function enqueue_front(): void {
			$vars = self::frontend_vars();
			wp_register_style( 'fbm-frontend-theme', FBM_URL . 'assets/css/theme-frontend.css', array(), Plugin::FBM_VERSION );
			wp_add_inline_style( 'fbm-frontend-theme', self::to_css_vars( $vars, '.fbm-scope' ) );
			$custom = Options::get( 'theme.frontend.custom_css', '' );
		if ( is_string( $custom ) && $custom !== '' ) {
				wp_add_inline_style( 'fbm-frontend-theme', $custom );
		}
			wp_enqueue_style( 'fbm-frontend-theme' );

			self::maybe_enqueue_font( 'frontend' );
	}

		/**
		 * Enqueue admin theme styles.
		 */
	public static function enqueue_admin(): void {
			$vars = self::admin_vars();
			wp_register_style( 'fbm-admin-theme', FBM_URL . 'assets/css/theme-admin.css', array(), Plugin::FBM_VERSION );
			wp_add_inline_style( 'fbm-admin-theme', self::to_css_vars( $vars, '.fbm-scope' ) );
			$custom = Options::get( 'theme.admin.custom_css', '' );
		if ( is_string( $custom ) && $custom !== '' ) {
				wp_add_inline_style( 'fbm-admin-theme', $custom );
		}
			wp_enqueue_style( 'fbm-admin-theme' );

			self::maybe_enqueue_font( 'admin' );
	}

		/**
		 * Convert associative array to CSS variables string.
		 *
		 * @param array<string,string> $vars Variables.
		 * @param string               $selector CSS selector.
		 * @return string
		 */
	public static function to_css_vars( array $vars, string $selector ): string {
			$lines = array();
		foreach ( $vars as $k => $v ) {
				$lines[] = "--fbm-{$k}: {$v};";
		}
			return $selector . "{\n" . implode( "\n", $lines ) . "\n}";
	}

		/**
		 * Map option values to CSS variables.
		 *
		 * @param string $which frontend|admin.
		 * @return array<string,string>
		 */
	private static function vars( string $which ): array {
			$opt = Options::get( 'theme.' . $which );
		if ( ! is_array( $opt ) ) {
				$opt = array();
		}
			$shadow_map = array(
				'none' => 'none',
				'sm'   => '0 1px 2px rgba(0,0,0,.05)',
				'md'   => '0 2px 8px rgba(0,0,0,.08)',
				'lg'   => '0 4px 16px rgba(0,0,0,.1)',
			);
			$font_map   = array(
				'system'  => 'system-ui, sans-serif',
				'inter'   => '"Inter", system-ui, sans-serif',
				'roboto'  => '"Roboto", system-ui, sans-serif',
				'georgia' => 'Georgia, serif',
			);
			$vars       = array(
				'accent'      => (string) ( $opt['accent'] ?? '#3b82f6' ),
				'radius'      => (string) ( (int) ( $opt['radius'] ?? 12 ) ) . 'px',
				'shadow'      => $shadow_map[ $opt['shadow'] ?? 'md' ] ?? $shadow_map['md'],
				'font-family' => $font_map[ $opt['font_family'] ?? 'system' ] ?? $font_map['system'],
			);
			return $vars;
	}

		/**
		 * Enqueue Google Font if opted-in.
		 *
		 * @param string $which frontend|admin.
		 */
	private static function maybe_enqueue_font( string $which ): void {
			$family = Options::get( 'theme.' . $which . '.font_family', 'system' );
			$load   = Options::get( 'theme.' . $which . '.load_font', false );
		if ( $load && in_array( $family, array( 'inter', 'roboto' ), true ) ) {
				$map = array(
					'inter'  => 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap',
					'roboto' => 'https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap',
				);
				wp_enqueue_style( 'fbm-font-' . $which, $map[ $family ] );
		}
	}
}
