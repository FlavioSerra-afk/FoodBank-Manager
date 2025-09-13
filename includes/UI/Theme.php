<?php // phpcs:ignoreFile
/**
 * Theme token helpers.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\UI;

use FoodBankManager\Core\Options;
use function filter_var;
use function sanitize_hex_color;
use function sanitize_key;
use function is_rtl;
use function get_option;
use function add_settings_error;
use function wp_json_encode;
use function strlen;
use function __;

/**
 * Provide sanitized design tokens for admin and front-end.
 */
final class Theme {
        /** Default accent colour. */
        public const DEFAULT_ACCENT = '#0B5FFF';

		/**
		 * No-op for backwards compatibility.
		 *
		 * @deprecated
		 */
	public static function enqueue_front(): void {}

		/**
		 * No-op for backwards compatibility.
		 *
		 * @deprecated
		 */
	public static function enqueue_admin(): void {}

	/**
	 * Default theme settings.
	 *
	 * @return array<string,mixed>
	 */
	public static function defaults(): array {
		$defaults = Options::defaults();
		return $defaults['theme'];
	}

	/**
	 * Retrieve sanitized theme settings.
	 *
	 * @return array<string,mixed>
	 */
        public static function get(): array {
                $raw = get_option( 'fbm_theme', array() );
                return self::sanitize( is_array( $raw ) ? $raw : array() );
        }

	/**
	 * Retrieve sanitized admin section.
	 *
	 * @return array<string,mixed>
	 */
	public static function admin(): array {
		return self::get()['admin'];
	}

	/**
	 * Retrieve sanitized front section.
	 *
	 * @return array<string,mixed>
	 */
	public static function front(): array {
		return self::get()['front'];
	}

	/**
	 * Sanitize raw theme settings.
	 *
	 * @param array<string,mixed> $raw Raw values.
	 * @return array<string,mixed>
	 */
        public static function sanitize( array $raw ): array {
                $defaults = self::defaults();
                $json     = wp_json_encode( $raw );
                if ( is_string( $json ) && strlen( $json ) > 65536 ) {
                        add_settings_error( 'fbm_theme', 'fbm_theme', __( 'Theme payload too large.', 'foodbank-manager' ), 'error' );
                        return $defaults;
                }
                $admin = self::sanitize_section( $raw['admin'] ?? array(), $defaults['admin'] );
                $front = self::sanitize_section( $raw['front'] ?? array(), $defaults['front'] );

		$front_enabled    = filter_var( $raw['front']['enabled'] ?? $defaults['front']['enabled'], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE );
		$front['enabled'] = null === $front_enabled ? $defaults['front']['enabled'] : (bool) $front_enabled;

		$match = filter_var( $raw['match_front_to_admin'] ?? $defaults['match_front_to_admin'], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE );
		$match = (bool) ( null === $match ? $defaults['match_front_to_admin'] : $match );

		$admin_chrome = filter_var( $raw['apply_admin_chrome'] ?? $defaults['apply_admin_chrome'], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE );
		$admin_chrome = null === $admin_chrome ? $defaults['apply_admin_chrome'] : (bool) $admin_chrome;

		$front_menus = filter_var( $raw['apply_front_menus'] ?? $defaults['apply_front_menus'], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE );
		$front_menus = null === $front_menus ? $defaults['apply_front_menus'] : (bool) $front_menus;

		if ( $match ) {
			$front_copy = $front;
			unset( $front_copy['enabled'] );
			if ( $front_copy !== $admin ) {
				$enabled          = $front['enabled'];
				$front            = $admin;
				$front['enabled'] = $enabled;
			}
		}

		return array(
			'admin'                => $admin,
			'front'                => $front,
			'match_front_to_admin' => $match,
			'apply_admin_chrome'   => $admin_chrome,
			'apply_front_menus'    => $front_menus,
		);
	}

	/**
	 * Sanitize one section (admin/front).
	 *
	 * @param array<string,mixed> $raw Raw values.
	 * @param array<string,mixed> $defaults Defaults.
	 * @return array<string,mixed>
	 */
	private static function sanitize_section( array $raw, array $defaults ): array {
		$style = sanitize_key( (string) ( $raw['style'] ?? $defaults['style'] ) );
		if ( ! in_array( $style, array( 'glass', 'basic' ), true ) ) {
			$style = $defaults['style'];
		}

		$preset = sanitize_key( (string) ( $raw['preset'] ?? $defaults['preset'] ) );
		if ( ! in_array( $preset, array( 'light', 'dark', 'high_contrast' ), true ) ) {
			$preset = $defaults['preset'];
		}

		$accent = sanitize_hex_color( (string) ( $raw['accent'] ?? $defaults['accent'] ) );
		if ( '' === $accent ) {
			$accent = self::DEFAULT_ACCENT;
		}

                $glass_raw = is_array( $raw['glass'] ?? null ) ? $raw['glass'] : array();
                $aliases   = array();
                if ( is_array( $raw['aliases'] ?? null ) ) {
                        foreach ( $raw['aliases'] as $k => $v ) {
                                $val = sanitize_hex_color( (string) $v );
                                if ( '' !== $val ) {
                                        $aliases[ sanitize_key( (string) $k ) ] = $val;
                                }
                        }
                }
		$alpha     = (float) ( $glass_raw['alpha'] ?? $defaults['glass']['alpha'] );
		$alpha     = 'dark' === $preset ? self::clamp( $alpha, 0.18, 0.35 ) : self::clamp( $alpha, 0.08, 0.20 );
		$blur      = (int) ( $glass_raw['blur'] ?? $defaults['glass']['blur'] );
		$blur      = self::clamp( $blur, 0, 12 );
		$elev      = (int) ( $glass_raw['elev'] ?? $defaults['glass']['elev'] );
		$elev      = self::clamp( $elev, 0, 24 );
		$radius    = (int) ( $glass_raw['radius'] ?? $defaults['glass']['radius'] );
		$radius    = self::clamp( $radius, 6, 20 );
		$border    = (int) ( $glass_raw['border'] ?? $defaults['glass']['border'] );
		$border    = self::clamp( $border, 1, 2 );

		if ( 'glass' !== $style ) {
			$alpha = 0.0;
			$blur  = 0;
		}
		if ( 'high_contrast' === $preset ) {
			$alpha = 0.0;
			$blur  = 0;
		}

                return array(
                        'style'   => $style,
                        'preset'  => $preset,
                        'accent'  => $accent,
                        'glass'   => array(
                                'alpha'  => $alpha,
                                'blur'   => $blur,
                                'elev'   => $elev,
                                'radius' => $radius,
                                'border' => $border,
                        ),
                        'aliases' => $aliases,
                );
        }

	/**
	 * Clamp a numeric value.
	 *
	 * @param float|int $v Value.
	 * @param float|int $min Minimum.
	 * @param float|int $max Maximum.
	 * @return float|int
	 */
	private static function clamp( $v, $min, $max ) {
		if ( $v < $min ) {
			return $min;
		}
		if ( $v > $max ) {
			return $max;
		}
		return $v;
	}

	/**
	 * Convert tokens for a section into CSS vars.
	 *
	 * @param array<string,mixed> $section Section tokens.
	 * @param string              $selector CSS selector.
	 * @return string
	 */
        public static function css_vars( array $section, string $selector ): string {
                $tokens = self::section_to_css( $section );
                $css    = '';
                foreach ( $tokens as $key => $val ) {
                        $css .= $key . ':' . $val . ';';
                }
                return $selector . '{' . $css . '}';
        }

        /**
         * CSS variables for admin theme.
         */
        public static function css_variables(): string {
                return self::css_vars( self::admin(), ':root' ) . self::glass_support_css();
        }

	/**
	 * Build CSS tokens for a section.
	 *
	 * @param array<string,mixed> $section Section tokens.
	 * @return array<string,string>
	 */
	private static function section_to_css( array $section ): array {
		$preset_map = array(
			'light'         => array(
				'surface'  => '#ffffff',
				'text'     => '#000000',
				'border'   => 'rgba(0,0,0,0.1)',
				'contrast' => '1',
			),
			'dark'          => array(
				'surface'  => '#1f2937',
				'text'     => '#f3f4f6',
				'border'   => 'rgba(255,255,255,0.1)',
				'contrast' => '1',
			),
			'high_contrast' => array(
				'surface'  => '#000000',
				'text'     => '#ffffff',
				'border'   => '#ffffff',
				'contrast' => '1.5',
			),
		);
		$preset     = $section['preset'];
		$base       = $preset_map[ $preset ] ?? $preset_map['light'];

                $glass   = $section['glass'];
                $alpha   = (float) $glass['alpha'];
                $blur    = (int) $glass['blur'];
                $elev    = (int) $glass['elev'];
                $radius  = (int) $glass['radius'];
                $border  = (int) $glass['border'];
                $aliases = is_array( $section['aliases'] ?? null ) ? $section['aliases'] : array();

                return array(
                        '--fbm-color-accent'        => $section['accent'],
                        '--fbm-color-text'          => $base['text'],
                        '--fbm-color-surface'       => $base['surface'],
                        '--fbm-color-border'        => $base['border'],
                        '--fbm-accent'              => $section['accent'],
                        '--fbm-text'                => $base['text'],
                        '--fbm-bg'                  => $base['surface'],
                        '--fbm-surface'             => $base['surface'],
                        '--fbm-shadow-rgb'          => '0 0 0',
                        '--fbm-glass-alpha'         => sprintf( '%.2f', $alpha ),
                        '--fbm-blur-max'            => '12px',
                        '--fbm-glass-blur'          => $blur . 'px',
                        '--fbm-card-radius'         => $radius . 'px',
                        '--fbm-border-w'            => $border . 'px',
                        '--fbm-control-radius'      => $radius . 'px',
                        '--fbm-input-height'        => '38px',
                        '--fbm-elev-shadow'         => '0 8px 32px rgba(var(--fbm-shadow-rgb)/0.10)',
                        '--fbm-inset-top'           => 'inset 0 1px 0 rgba(255 255 255 / 0.50)',
                        '--fbm-inset-bottom'        => 'inset 0 -1px 0 rgba(255 255 255 / 0.10)',
                        '--fbm-inset-glow'          => 'inset 0 0 20px 10px rgba(255 255 255 / 0.60)',
                        '--fbm-contrast-multiplier' => $base['contrast'],
                        '--fbm-button-bg'           => $aliases['button_bg'] ?? 'var(--fbm-accent)',
                        '--fbm-button-fg'           => $aliases['button_fg'] ?? '#ffffff',
                        '--fbm-button-border'       => $aliases['button_border'] ?? 'var(--fbm-accent)',
                        '--fbm-button-hover-bg'     => $aliases['button_hover_bg'] ?? 'color-mix(in srgb, var(--fbm-accent) 90%, black 10%)',
                        '--fbm-button-hover-fg'     => $aliases['button_hover_fg'] ?? '#ffffff',
                        '--fbm-link-fg'             => $aliases['link_fg'] ?? 'var(--fbm-accent)',
                        '--fbm-link-hover-fg'       => $aliases['link_hover_fg'] ?? 'color-mix(in srgb, var(--fbm-accent) 90%, black 10%)',
                        '--fbm-link-visited-fg'     => $aliases['link_visited_fg'] ?? 'var(--fbm-accent)',
                        '--fbm-link-underline'      => $aliases['link_underline'] ?? 'underline',
                        '--fbm-input-bg'            => $aliases['input_bg'] ?? 'var(--fbm-surface)',
                        '--fbm-input-fg'            => $aliases['input_fg'] ?? 'var(--fbm-text)',
                        '--fbm-input-border'        => $aliases['input_border'] ?? 'var(--fbm-color-border)',
                        '--fbm-input-placeholder'   => $aliases['input_placeholder'] ?? 'var(--fbm-color-border)',
                        '--fbm-input-focus-border'  => $aliases['input_focus_border'] ?? 'var(--fbm-accent)',
                        '--fbm-control-accent'      => $aliases['control_accent'] ?? 'var(--fbm-accent)',
                        '--fbm-alert-info-bg'       => $aliases['alert_info_bg'] ?? 'color-mix(in srgb,var(--fbm-accent) 10%, var(--fbm-surface))',
                        '--fbm-alert-info-fg'       => $aliases['alert_info_fg'] ?? 'var(--fbm-text)',
                        '--fbm-alert-info-border'   => $aliases['alert_info_border'] ?? 'var(--fbm-accent)',
                        '--fbm-card-bg'             => $aliases['card_bg'] ?? 'var(--fbm-surface)',
                        '--fbm-card-fg'             => $aliases['card_fg'] ?? 'var(--fbm-text)',
                        '--fbm-card-border'         => $aliases['card_border'] ?? 'var(--fbm-color-border)',
                        '--fbm-card-shadow'         => $aliases['card_shadow'] ?? 'var(--fbm-elev-shadow)',
                        '--fbm-tooltip-bg'          => $aliases['tooltip_bg'] ?? 'var(--fbm-text)',
                        '--fbm-tooltip-fg'          => $aliases['tooltip_fg'] ?? 'var(--fbm-surface)',
                        '--fbm-tab-active-fg'       => $aliases['tab_active_fg'] ?? 'var(--fbm-text)',
                        '--fbm-tab-active-border'   => $aliases['tab_active_border'] ?? 'var(--fbm-accent)',
                        '--fbm-tab-inactive-fg'     => $aliases['tab_inactive_fg'] ?? 'var(--fbm-color-border)',
                        '--fbm-table-header-bg'     => $aliases['table_header_bg'] ?? 'var(--fbm-color-border)',
                        '--fbm-table-header-fg'     => $aliases['table_header_fg'] ?? 'var(--fbm-text)',
                        '--fbm-table-row-hover-bg'  => $aliases['table_row_hover_bg'] ?? 'color-mix(in srgb,var(--fbm-accent) 5%, var(--fbm-surface))',
                        '--fbm-icon-color'          => $aliases['icon_color'] ?? 'currentColor',
                        '--fbm-icon-muted'          => $aliases['icon_muted'] ?? 'var(--fbm-color-border)',
                );
        }

		/**
		 * Append admin body classes.
		 *
		 * @param string $classes Existing classes.
		 * @return string
		 */
	public static function admin_body_class( string $classes ): string {
		$all = self::get();
		if ( empty( $all['apply_admin_chrome'] ) ) {
			return $classes;
		}
		$admin    = $all['admin'];
		$classes .= ' fbm-theme--' . $admin['style'];
		$classes .= ' fbm-preset--' . $admin['preset'];
		if ( 'glass' === $admin['style'] ) {
			$classes .= ' fbm-menus--glass';
		}
		if ( is_rtl() ) {
			$classes .= ' fbm-rtl';
		}
		return $classes;
	}

	/**
	 * Append front-end body classes.
	 *
	 * @param array<int,string> $classes Classes.
	 * @return array<int,string>
	 */
	public static function body_class( array $classes ): array {
		$all = self::get();
		if ( empty( $all['apply_front_menus'] ) ) {
			return $classes;
		}
		$front     = $all['front'];
		$classes[] = 'fbm-theme--' . $front['style'];
		$classes[] = 'fbm-preset--' . $front['preset'];
		if ( 'glass' === $front['style'] ) {
			$classes[] = 'fbm-menus--glass';
		}
		if ( is_rtl() ) {
			$classes[] = 'fbm-rtl';
		}
		return $classes;
	}

	/**
	 * Glass fallback CSS.
	 */
	public static function glass_support_css(): string {
			$targets = '.fbm-card--glass,.fbm-button--glass';
			return '@supports (backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px)){' . $targets . '{backdrop-filter:blur(var(--fbm-glass-blur));-webkit-backdrop-filter:blur(var(--fbm-glass-blur));}}' // phpcs:ignore WordPress.Files.LineLength.MaxExceeded
					. '@supports not ((backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px))){' . $targets . '{background:var(--fbm-color-surface,#fff);}}' // phpcs:ignore WordPress.Files.LineLength.MaxExceeded
					. '@media (prefers-reduced-transparency: reduce){' . $targets . '{background:var(--fbm-color-surface,#fff);}}'
					. '@media (forced-colors: active){' . $targets . '{background:Canvas;color:CanvasText;border-color:ButtonText;box-shadow:none;}}'
					. '@media (prefers-reduced-motion: reduce){' . $targets . '{transition:none;}}';
	}
}
