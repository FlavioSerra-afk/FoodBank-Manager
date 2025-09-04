<?php
/**
 * Shortcodes admin page.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FoodBankManager\Shortcodes\Metadata;

/**
 * Shortcodes admin page.
 */
final class ShortcodesPage {
		/**
		 * Register menu item.
		 */
	public static function register_menu(): void {
						add_submenu_page(
							'fbm',
							esc_html__( 'Shortcodes', 'foodbank-manager' ),
							esc_html__( 'Shortcodes', 'foodbank-manager' ),
							'fb_manage_forms',
							'fbm_shortcodes',
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
			$map        = array();
		foreach ( $shortcodes as $sc ) {
					$map[ $sc['tag'] ] = $sc['atts'];
		}
			$current_tag  = '';
			$current_atts = array();
		$preview_html     = '';
		if ( isset( $_POST['fbm_action'] ) && 'shortcode_preview' === $_POST['fbm_action'] ) {
			check_admin_referer( 'fbm_shortcodes_preview' );
			$tag = sanitize_key( (string) ( $_POST['tag'] ?? '' ) );
			if ( isset( $map[ $tag ] ) ) {
				$raw_atts               = isset( $_POST['atts'] ) && is_array( $_POST['atts'] ) ? wp_unslash( $_POST['atts'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized in sanitize_atts
				$atts                   = self::sanitize_atts( $map[ $tag ], $raw_atts );
				$atts['mask_sensitive'] = 'true';
				$current_tag            = $tag;
				$current_atts           = $atts;
				$shortcode              = self::build_shortcode( $tag, $atts );
				$preview_html           = wp_kses_post( do_shortcode( $shortcode ) );
			} else {
				$preview_html = '<div class="notice notice-error"><p>' . esc_html__( 'Invalid shortcode.', 'foodbank-manager' ) . '</p></div>';
			}
		}
				/* @psalm-suppress UnresolvableInclude */
				require FBM_PATH . 'templates/admin/shortcodes.php';
	}

		/**
		 * Discover shortcode metadata.
		 *
		 * @return array<int,array{tag:string,atts:array<string,array{type:string,default:string,options?:array<int,string>}>}>
		 */
	public static function discover(): array {
			return Metadata::discover();
	}

		/**
		 * Sanitize attributes based on metadata.
		 *
		 * @param array<string,array{type:string,default:string,options?:array<int,string>}> $meta Metadata.
		 * @param array<string,mixed>                                                        $raw Raw input.
		 * @return array<string,string>
		 */
	private static function sanitize_atts( array $meta, array $raw ): array {
			$out = array();
		foreach ( $meta as $name => $info ) {
				$val  = $raw[ $name ] ?? $info['default'];
				$type = $info['type'];
			switch ( $type ) {
				case 'bool':
						$val = filter_var( $val, FILTER_VALIDATE_BOOLEAN ) ? 'true' : 'false';
					break;
				case 'int':
									$val = (string) (int) $val;
					break;
				case 'enum':
									$val = sanitize_text_field( (string) $val );
					if ( empty( $info['options'] ) || ! in_array( $val, $info['options'], true ) ) {
							continue 2;
					}
					break;
				default:
					$val = sanitize_text_field( (string) $val );
			}
			if ( strlen( $val ) > 256 ) {
						$val = substr( $val, 0, 256 );
			}
					$out[ $name ] = $val;
		}
			return $out;
	}

		/**
		 * Build shortcode string.
		 *
		 * @param string               $tag  Shortcode tag.
		 * @param array<string,string> $atts Attributes.
		 */
	private static function build_shortcode( string $tag, array $atts ): string {
			$parts = array();
		foreach ( $atts as $k => $v ) {
				$parts[] = $k . '="' . $v . '"';
		}
			return '[' . $tag . ( $parts ? ' ' . implode( ' ', $parts ) : '' ) . ']';
	}
}
