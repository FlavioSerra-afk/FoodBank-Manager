<?php
/**
 * Shortcode metadata discovery.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Shortcodes;

/**
 * Discover shortcode attribute metadata.
 */
final class Metadata {
	/**
	 * Discover shortcodes and their attribute metadata.
	 *
	 * @return array<int,array{tag:string,atts:array<string,array{type:string,default:string,options?:array<int,string>}>}>
	 */
	public static function discover(): array {
		$dir   = FBM_PATH . 'includes/Shortcodes';
		$files = glob( $dir . '/*.php' );
		if ( false === $files ) {
			return array();
		}
		$out = array();
		foreach ( $files as $file ) {
			$base = basename( $file );
			if ( 'Metadata.php' === $base ) {
				continue;
			}
			$class    = basename( $file, '.php' );
			$snake    = strtolower( preg_replace( '/(?<!^)[A-Z]/', '_$0', $class ) );
			$tag      = 'fbm_' . $snake;
			$defaults = self::extract_atts( $file );
			$atts     = array();
			foreach ( $defaults as $name => $default ) {
				$type = self::infer_type( $default );
				$meta = array(
					'type'    => $type,
					'default' => '',
				);
				if ( 'enum' === $type ) {
					$options         = array_values( array_map( 'strval', (array) $default ) );
					$meta['options'] = $options;
					$meta['default'] = $options[0] ?? '';
				} elseif ( 'bool' === $type ) {
					$meta['default'] = $default ? 'true' : 'false';
				} elseif ( 'int' === $type ) {
					$meta['default'] = (string) (int) $default;
				} else {
					$meta['default'] = (string) $default;
				}
				$atts[ (string) $name ] = $meta;
			}
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

	/**
	 * Infer attribute type from default value.
	 *
	 * @param mixed $value Default value.
	 * @return string
	 */
	private static function infer_type( $value ): string {
		if ( is_bool( $value ) ) {
			return 'bool';
		}
		if ( is_int( $value ) || ( is_string( $value ) && preg_match( '/^-?\d+$/', $value ) === 1 ) ) {
			return 'int';
		}
		if ( is_array( $value ) ) {
			return 'enum';
		}
		return 'string';
	}
}
