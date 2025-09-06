<?php // phpcs:ignoreFile
/**
 * Theme token helpers.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\UI;

use FoodBankManager\Core\Options;

/**
 * Provide sanitized design tokens.
 */
class Theme {
/**
 * @deprecated No-op kept for backwards compatibility.
 */
public static function enqueue_front(): void {
// Intentionally empty.
}

/**
 * @deprecated No-op kept for backwards compatibility.
 */
public static function enqueue_admin(): void {
// Intentionally empty.
}
	/**
	 * Default design tokens.
	 *
	 * @return array{primary_color:string,density:string,font:string,dark_mode:bool}
	 */
	public static function defaults(): array {
	    return array(
	        'primary_color' => '#3b82f6',
	        'density'       => 'comfortable',
	        'font'          => 'system',
	        'dark_mode'     => false,
	    );
	}

	/**
	 * Retrieve sanitized admin tokens.
	 *
	 * @return array{primary_color:string,density:string,font:string,dark_mode:bool}
	 */
	public static function admin(): array {
            $all = Options::all();
            $opt = $all['theme'] ?? array();
            return self::sanitize( is_array( $opt ) ? $opt : array() );
	}

	/**
	 * Sanitize raw tokens against defaults.
	 *
	 * @param array<string,mixed> $raw Raw option values.
	 * @return array{primary_color:string,density:string,font:string,dark_mode:bool}
	 */
	public static function sanitize( array $raw ): array {
	    $defaults = self::defaults();

            $color_raw = $raw['primary_color'] ?? $raw['primary'] ?? '';
            $color     = sanitize_hex_color( (string) $color_raw );
            if ( ! is_string( $color ) || '' === $color ) {
                $color = $defaults['primary_color'];
            }

            $density_raw = $raw['density'] ?? '';
            $density     = sanitize_key( (string) $density_raw );
            if ( ! in_array( $density, array( 'compact', 'comfortable' ), true ) ) {
                $density = $defaults['density'];
            }

            $font_raw = $raw['font_family'] ?? $raw['font'] ?? '';
            $font     = sanitize_key( (string) $font_raw );
            if ( ! in_array( $font, array( 'system', 'inter', 'roboto' ), true ) ) {
                $font = $defaults['font'];
            }

            $dark_raw = $raw['dark_mode_default'] ?? $raw['dark_mode'] ?? null;
            $dark_val = filter_var( $dark_raw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
            $dark     = null === $dark_val ? $defaults['dark_mode'] : (bool) $dark_val;

	    return array(
	        'primary_color' => $color,
	        'density'       => $density,
	        'font'          => $font,
	        'dark_mode'     => $dark,
	    );
	}

	/**
	 * Convert tokens to a CSS variables block.
	 *
	 * @param array{primary_color:string,density:string,font:string,dark_mode:bool} $tokens Tokens.
	 * @param string $selector CSS selector.
	 * @return string
	 */
	public static function to_css_vars( array $tokens, string $selector ): string {
            $tokens = self::sanitize( $tokens );
            $vars   = array(
                '--fbm-primary' => $tokens['primary_color'],
                '--fbm-density' => $tokens['density'],
                '--fbm-font'    => self::font_css( $tokens['font'] ),
                '--fbm-dark'    => $tokens['dark_mode'] ? '1' : '0',
            );
            $css = '';
            foreach ( $vars as $k => $v ) {
                $css .= $k . ':' . $v . ';';
            }
            return $selector . '{' . $css . '}';
	}

	/**
	 * Map font token to CSS value.
	 *
	 * @param string $font Token.
	 * @return string
	 */
	private static function font_css( string $font ): string {
	    $map = array(
	        'system' => 'system-ui, sans-serif',
	        'inter'  => '"Inter", system-ui, sans-serif',
	        'roboto' => '"Roboto", system-ui, sans-serif',
	    );
	    return $map[ $font ] ?? $map['system'];
	}
}
