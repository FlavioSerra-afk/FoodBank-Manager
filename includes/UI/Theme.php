<?php // phpcs:ignoreFile
/**
 * Theme token helpers.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\UI {

use FoodBankManager\Core\Options;
use function filter_var;
use function sanitize_hex_color;
use function sanitize_key;
use function is_rtl;
use function absint;
use function get_option;
use function add_settings_error;
use function wp_json_encode;
use function strlen;
use function __;
use function array_key_first;
use function wp_strip_all_tags;

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
         * Schema stub – use existing global helper if available.
         *
         * @return array<string,array<string,mixed>>
         */
        public static function schema(): array {
                return function_exists('fbm_theme_schema') ? fbm_theme_schema() : array();
        }

	/**
	 * Retrieve sanitized theme settings.
	 *
	 * @return array<string,mixed>
	 */
        public static function get(): array {
                $raw = get_option('fbm_theme', array());
                return self::sanitize(is_array($raw) ? $raw : array());
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
         * @param array<string,mixed>|null $raw Raw values.
         * @return array<string,mixed>
         */
    public static function sanitize(?array $raw = null): array
    {
        $raw      = $raw ?? [];
        $defaults = self::defaults();
        $out      = [];

        $clamp = static function ($v, $min, $max, $step = null) {
            if (!is_numeric($v)) {
                return $min;
            }
            $v = (float) $v;
            if ($step && $step > 0) {
                $v = round($v / $step) * $step;
            }
            return max($min, min($max, $v));
        };

        foreach (['admin', 'front'] as $section) {
            $src = isset($raw[$section]) && is_array($raw[$section]) ? $raw[$section] : [];
            $out[$section] = [
                'style'  => in_array($src['style'] ?? '', ['glass', 'basic'], true) ? $src['style'] : $defaults[$section]['style'],
                'preset' => in_array($src['preset'] ?? '', ['light', 'dark', 'high_contrast'], true) ? $src['preset'] : $defaults[$section]['preset'],
                'accent' => is_string($src['accent'] ?? null) && preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $src['accent']) ? $src['accent'] : $defaults[$section]['accent'],
                'glass'  => [
                    'alpha'  => $clamp($src['glass']['alpha'] ?? $defaults[$section]['glass']['alpha'], 0, 1, 0.01),
                    'blur'   => (int) $clamp($src['glass']['blur'] ?? $defaults[$section]['glass']['blur'], 0, 12, 1),
                    'elev'   => (int) $clamp($src['glass']['elev'] ?? $defaults[$section]['glass']['elev'], 0, 24, 1),
                    'radius' => (int) $clamp($src['glass']['radius'] ?? $defaults[$section]['glass']['radius'], 6, 20, 1),
                    'border' => (int) $clamp($src['glass']['border'] ?? $defaults[$section]['glass']['border'], 1, 2, 1),
                ],
                'aliases' => [],
            ];
            if (!empty($src['aliases']) && is_array($src['aliases'])) {
                foreach ($src['aliases'] as $k => $v) {
                    $col = sanitize_hex_color((string) $v);
                    if ('' !== $col) {
                        $out[$section]['aliases'][sanitize_key((string) $k)] = $col;
                    }
                }
            }
            if ('front' === $section) {
                $en = filter_var($src['enabled'] ?? $defaults['front']['enabled'], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
                $out['front']['enabled'] = null === $en ? $defaults['front']['enabled'] : (bool) $en;
            }
        }

        $match = filter_var($raw['match_front_to_admin'] ?? $defaults['match_front_to_admin'], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        $out['match_front_to_admin'] = null === $match ? $defaults['match_front_to_admin'] : (bool) $match;

        $adm = filter_var($raw['apply_admin'] ?? ($raw['apply_admin_chrome'] ?? $defaults['apply_admin']), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        $out['apply_admin'] = null === $adm ? $defaults['apply_admin'] : (bool) $adm;
        $out['apply_admin_chrome'] = $out['apply_admin'];

        $front_menus = filter_var($raw['apply_front_menus'] ?? $defaults['apply_front_menus'], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        $out['apply_front_menus'] = null === $front_menus ? $defaults['apply_front_menus'] : (bool) $front_menus;

        foreach (['menu', 'typography', 'tabs'] as $grp) {
            $out[$grp] = isset($raw[$grp]) && is_array($raw[$grp]) ? array_replace_recursive($defaults[$grp], $raw[$grp]) : $defaults[$grp];
        }

        if ($out['match_front_to_admin']) {
            $enabled = $out['front']['enabled'];
            $out['front'] = $out['admin'];
            $out['front']['enabled'] = $enabled;
        }

        return array_replace_recursive($defaults, $out);
    }

        /**
         * Build CSS variables for menu tokens.
         *
         * @param array<string,mixed> $menu Menu settings.
         * @return array<string,string>
         */
        private static function menu_tokens( array $menu ): array {
                return array(
                        '--fbm-menu-item-h'       => $menu['item_height'] . 'px',
                        '--fbm-menu-item-px'      => $menu['item_px'] . 'px',
                        '--fbm-menu-item-py'      => $menu['item_py'] . 'px',
                        '--fbm-menu-gap'          => $menu['gap'] . 'px',
                        '--fbm-menu-radius'       => $menu['radius'] . 'px',
                        '--fbm-menu-icon-size'    => $menu['icon_size'] . 'px',
                        '--fbm-menu-icon-opacity' => (string) $menu['icon_opacity'],
                        '--fbm-menu-bg'           => $menu['bg'],
                        '--fbm-menu-color'        => $menu['color'],
                        '--fbm-menu-hover-bg'     => $menu['hover_bg'],
                        '--fbm-menu-hover-color'  => $menu['hover_color'],
                        '--fbm-menu-active-bg'    => $menu['active_bg'],
                        '--fbm-menu-active-color' => $menu['active_color'],
                        '--fbm-menu-divider'      => $menu['divider'],
                );
        }

        /**
         * Build CSS variables for typography tokens.
         *
         * @param array<string,mixed> $typo Typography settings.
         * @return array<string,string>
         */
        private static function typography_tokens( array $typo ): array {
                $out = array(
                        '--fbm-body'       => $typo['body']['size'] . 'px',
                        '--fbm-body-lh'    => (string) $typo['body']['lh'],
                        '--fbm-body-w'     => isset( $typo['body']['weight'] ) ? (string) $typo['body']['weight'] : '400',
                        '--fbm-body-trk'   => ( $typo['body']['track'] ?? 0 ) . 'px',
                        '--fbm-small'      => $typo['small']['size'] . 'px',
                        '--fbm-small-lh'   => (string) $typo['small']['lh'],
                        '--fbm-color-text'     => $typo['color']['text'],
                        '--fbm-color-headings' => $typo['color']['headings'],
                        '--fbm-color-muted'    => $typo['color']['muted'],
                        '--fbm-link'           => $typo['link']['normal'],
                        '--fbm-link-hover'     => $typo['link']['hover'],
                        '--fbm-link-active'    => $typo['link']['active'],
                        '--fbm-link-visited'   => $typo['link']['visited'],
                );
                foreach ( array( 'h1','h2','h3','h4','h5','h6' ) as $tag ) {
                        $item = $typo[ $tag ];
                        $out[ '--fbm-' . $tag ]       = $item['size'] . 'px';
                        $out[ '--fbm-' . $tag . '-lh' ] = (string) $item['lh'];
                        $out[ '--fbm-' . $tag . '-w' ]  = (string) $item['weight'];
                        $out[ '--fbm-' . $tag . '-trk' ] = ( $item['track'] ?? 0 ) . 'px';
                }
                return $out;
        }

        /**
         * Build CSS variables for tabs tokens.
         *
         * @param array<string,mixed> $tabs Tabs settings.
         * @return array<string,string>
         */
        private static function tabs_tokens( array $tabs ): array {
                return array(
                        '--fbm-tabs-h'               => $tabs['height'] . 'px',
                        '--fbm-tabs-px'              => $tabs['px'] . 'px',
                        '--fbm-tabs-py'              => $tabs['py'] . 'px',
                        '--fbm-tabs-gap'             => $tabs['gap'] . 'px',
                        '--fbm-tabs-radius'          => $tabs['radius'] . 'px',
                        '--fbm-tabs-color'           => $tabs['color'],
                        '--fbm-tabs-hover-color'     => $tabs['hover_color'],
                        '--fbm-tabs-active-color'    => $tabs['active_color'],
                        '--fbm-tabs-hover-bg'        => $tabs['hover_bg'],
                        '--fbm-tabs-active-bg'       => $tabs['active_bg'],
                        '--fbm-tabs-indicator-h'     => $tabs['indicator_h'] . 'px',
                        '--fbm-tabs-indicator-offset'=> $tabs['indicator_offset'] . 'px',
                        '--fbm-tabs-indicator-color' => $tabs['indicator_color'] ?? 'var(--fbm-accent)',
                );
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
         * Scoped CSS variables for admin theme.
         */
        public static function css_variables_scoped( ?array $opts = null ): string {
                $theme  = $opts ?? self::get();
                $tokens = self::section_to_css( $theme['admin'] ?? self::admin() );
                $menu   = self::menu_tokens( $theme['menu'] ?? array() );
                $typo   = self::typography_tokens( $theme['typography'] ?? array() );
                $tabs   = self::tabs_tokens( $theme['tabs'] ?? array() );
                $css    = '';
                foreach ( array_merge( $tokens, $menu, $typo, $tabs ) as $key => $val ) {
                        $css .= $key . ':' . $val . ';';
                }

                $selectors  = '.fbm-scope{color:var(--fbm-color-text);font: var(--fbm-body-w) var(--fbm-body)/var(--fbm-body-lh) system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;letter-spacing:var(--fbm-body-trk,0);}';
                $selectors .= '.fbm-scope h1{font-size:var(--fbm-h1);line-height:var(--fbm-h1-lh);font-weight:var(--fbm-h1-w);letter-spacing:var(--fbm-h1-trk,0);color:var(--fbm-color-headings);}';
                $selectors .= '.fbm-scope h2{font-size:var(--fbm-h2);line-height:var(--fbm-h2-lh);font-weight:var(--fbm-h2-w);letter-spacing:var(--fbm-h2-trk,0);color:var(--fbm-color-headings);}';
                $selectors .= '.fbm-scope h3{font-size:var(--fbm-h3);line-height:var(--fbm-h3-lh);font-weight:var(--fbm-h3-w);letter-spacing:var(--fbm-h3-trk,0);color:var(--fbm-color-headings);}';
                $selectors .= '.fbm-scope h4{font-size:var(--fbm-h4);line-height:var(--fbm-h4-lh);font-weight:var(--fbm-h4-w);letter-spacing:var(--fbm-h4-trk,0);color:var(--fbm-color-headings);}';
                $selectors .= '.fbm-scope h5{font-size:var(--fbm-h5);line-height:var(--fbm-h5-lh);font-weight:var(--fbm-h5-w);letter-spacing:var(--fbm-h5-trk,0);color:var(--fbm-color-headings);}';
                $selectors .= '.fbm-scope h6{font-size:var(--fbm-h6);line-height:var(--fbm-h6-lh);font-weight:var(--fbm-h6-w);letter-spacing:var(--fbm-h6-trk,0);color:var(--fbm-color-headings);}';
                $selectors .= '.fbm-scope small{font-size:var(--fbm-small);line-height:var(--fbm-small-lh);}';
                $selectors .= '.fbm-scope .fbm-text--muted{color:var(--fbm-color-muted);}';
                $selectors .= '.fbm-scope a{color:var(--fbm-link);}';
                $selectors .= '.fbm-scope a:hover{color:var(--fbm-link-hover);}';
                $selectors .= '.fbm-scope a:active{color:var(--fbm-link-active);}';
                $selectors .= '.fbm-scope a:visited{color:var(--fbm-link-visited);}';

                $selectors .= '.fbm-scope .fbm-tabs{}';
                $selectors .= '.fbm-scope .fbm-tablist[role="tablist"]{display:flex;gap:var(--fbm-tabs-gap);}';
                $selectors .= '.fbm-scope [role="tab"]{min-height:var(--fbm-tabs-h);padding:var(--fbm-tabs-py) var(--fbm-tabs-px);border-radius:var(--fbm-tabs-radius);color:var(--fbm-tabs-color);position:relative;}';
                $selectors .= '.fbm-scope [role="tab"]:hover{color:var(--fbm-tabs-hover-color);background:var(--fbm-tabs-hover-bg);}';
                $selectors .= '.fbm-scope [role="tab"][aria-selected="true"]{color:var(--fbm-tabs-active-color);background:var(--fbm-tabs-active-bg);}';
                $selectors .= '.fbm-scope [role="tab"][aria-selected="true"]::after{content:"";position:absolute;left:var(--fbm-tabs-px);right:var(--fbm-tabs-px);height:var(--fbm-tabs-indicator-h);bottom:var(--fbm-tabs-indicator-offset);background:var(--fbm-tabs-indicator-color);border-radius:999px;}';
                $selectors .= '.fbm-scope [role="tab"]:focus-visible{outline:2px solid var(--fbm-accent);outline-offset:2px;}';
                $selectors .= '.fbm-scope [role="tabpanel"]{padding-block:12px;}';

                return '@layer fbm {.fbm-scope{' . $css . '}' . $selectors . self::glass_support_css() . '}';
        }

        /**
         * @deprecated Use css_variables_scoped().
         */
        public static function css_variables(): string {
                return self::css_variables_scoped();
        }

        /**
         * CSS variables preview – always use fully merged data.
         */
        public static function css_variables_preview( array $opts ): string {
                $data = array_replace_recursive( self::defaults(), $opts );

                // Example variables – adapt to your actual token map
                $accent = $data['core']['accent'] ?? '#2b6cb0';
                $link   = $data['core']['link'] ?? $accent;

                $radius = (string) ( $data['core']['radius'] ?? 8 );
                $elev   = (string) ( $data['core']['elevation'] ?? 8 );

                return <<<CSS
:root .fbm-scope{
  --fbm-accent: {$accent};
  --fbm-link: {$link};
  --fbm-radius: {$radius}px;
  --fbm-elevation: {$elev};
}
CSS;
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
                        '--fbm-base'                => '16px',
                        '--fbm-input-h'             => '38px',
                        '--fbm-radius'              => $radius . 'px',
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
                        '--fbm-input-focus'         => $aliases['input_focus_border'] ?? 'var(--fbm-accent)',
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
                        '--fbm-table-row-gap'       => $aliases['table_row_gap'] ?? '2px',
                        '--fbm-note-info'           => $aliases['note_info'] ?? 'color-mix(in srgb,var(--fbm-accent) 10%, var(--fbm-surface))',
                        '--fbm-note-success'        => $aliases['note_success'] ?? 'color-mix(in srgb,#46b450 20%, var(--fbm-surface))',
                        '--fbm-note-warn'           => $aliases['note_warn'] ?? 'color-mix(in srgb,#ffb900 20%, var(--fbm-surface))',
                        '--fbm-note-error'          => $aliases['note_error'] ?? 'color-mix(in srgb,#dc3232 20%, var(--fbm-surface))',
                        '--fbm-h1'                  => '2rem',
                        '--fbm-h2'                  => '1.75rem',
                        '--fbm-h3'                  => '1.5rem',
                        '--fbm-h4'                  => '1.25rem',
                        '--fbm-h5'                  => '1.125rem',
                        '--fbm-h6'                  => '1rem',
                        '--fbm-body'                => '1rem',
                        '--fbm-small'               => '0.875rem',
                        '--fbm-icon-color'          => $aliases['icon_color'] ?? 'currentColor',
                        '--fbm-icon-muted'          => $aliases['icon_muted'] ?? 'var(--fbm-color-border)',
                );
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
                        $targets = '.fbm-scope .fbm-card--glass,.fbm-scope .fbm-button--glass';
                        return '@supports (backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px)){' . $targets . '{backdrop-filter:blur(var(--fbm-glass-blur));-webkit-backdrop-filter:blur(var(--fbm-glass-blur));}}' // phpcs:ignore WordPress.Files.LineLength.MaxExceeded
                                        . '@supports not ((backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px))){' . $targets . '{background:var(--fbm-color-surface,#fff);}}' // phpcs:ignore WordPress.Files.LineLength.MaxExceeded
                                        . '@media (prefers-reduced-transparency: reduce){' . $targets . '{background:var(--fbm-color-surface,#fff);}}'
                                        . '@media (forced-colors: active){' . $targets . '{background:Canvas;color:CanvasText;border-color:ButtonText;box-shadow:none;}}'
                                        . '@media (prefers-reduced-motion: reduce){' . $targets . '{transition:none;}}';
        }
}

}

namespace {

    add_action(
        'admin_init',
        static function (): void {
            register_setting(
                'fbm_theme',
                'fbm_theme',
                array(
                    'type'              => 'array',
                    'default'           => array(),
                    'sanitize_callback' => [ \FoodBankManager\UI\Theme::class, 'sanitize' ],
                )
            );
        }
    );

    function fbm_theme_defaults(): array {
        return \FoodBankManager\UI\Theme::defaults();
    }

    /**
     * Schema for theme settings.
     *
     * @return array<string,array<string,mixed>>
     */
    function fbm_theme_schema(): array {
        $defaults = \FoodBankManager\UI\Theme::defaults();

        $schema = array(
            // Core tokens.
            'style'       => array(
                'type'    => 'string',
                'default' => $defaults['admin']['style'],
                'control' => 'radio',
                'options' => array(
                    'glass' => 'Glass',
                    'basic' => 'Basic',
                ),
            ),
            'preset'      => array(
                'type'    => 'string',
                'default' => $defaults['admin']['preset'],
                'control' => 'radio',
                'options' => array(
                    'light'         => 'Light',
                    'dark'          => 'Dark',
                    'high_contrast' => 'High Contrast',
                ),
            ),
            'accent'      => array(
                'type'    => 'string',
                'default' => $defaults['admin']['accent'],
                'control' => 'color',
            ),
            'glass_alpha' => array(
                'type'    => 'number',
                'default' => $defaults['admin']['glass']['alpha'],
                'control' => 'number',
                'min'     => 0,
                'max'     => 1,
                'step'    => 0.01,
            ),
            'glass_blur'  => array(
                'type'    => 'number',
                'default' => $defaults['admin']['glass']['blur'],
                'control' => 'number',
                'min'     => 0,
                'max'     => 20,
                'step'    => 1,
            ),
            'glass_elev'  => array(
                'type'    => 'number',
                'default' => $defaults['admin']['glass']['elev'],
                'control' => 'number',
                'min'     => 0,
                'max'     => 24,
                'step'    => 1,
            ),
            'glass_radius' => array(
                'type'    => 'number',
                'default' => $defaults['admin']['glass']['radius'],
                'control' => 'number',
                'min'     => 0,
                'max'     => 40,
                'step'    => 1,
            ),
            'glass_border' => array(
                'type'    => 'number',
                'default' => $defaults['admin']['glass']['border'],
                'control' => 'number',
                'min'     => 0,
                'max'     => 4,
                'step'    => 1,
            ),

            // Menu tokens.
            'menu_item_height'   => array(
                'type'    => 'number',
                'default' => $defaults['menu']['item_height'],
                'control' => 'number',
                'min'     => 40,
                'max'     => 64,
                'step'    => 1,
            ),
            'menu_item_px'       => array(
                'type'    => 'number',
                'default' => $defaults['menu']['item_px'],
                'control' => 'number',
                'min'     => 8,
                'max'     => 24,
                'step'    => 1,
            ),
            'menu_item_py'       => array(
                'type'    => 'number',
                'default' => $defaults['menu']['item_py'],
                'control' => 'number',
                'min'     => 6,
                'max'     => 16,
                'step'    => 1,
            ),
            'menu_gap'           => array(
                'type'    => 'number',
                'default' => $defaults['menu']['gap'],
                'control' => 'number',
                'min'     => 8,
                'max'     => 16,
                'step'    => 1,
            ),
            'menu_radius'        => array(
                'type'    => 'number',
                'default' => $defaults['menu']['radius'],
                'control' => 'number',
                'min'     => 8,
                'max'     => 16,
                'step'    => 1,
            ),
            'menu_icon_size'     => array(
                'type'    => 'number',
                'default' => $defaults['menu']['icon_size'],
                'control' => 'number',
                'min'     => 16,
                'max'     => 24,
                'step'    => 1,
            ),
            'menu_icon_opacity'  => array(
                'type'    => 'number',
                'default' => $defaults['menu']['icon_opacity'],
                'control' => 'number',
                'min'     => 0.6,
                'max'     => 1,
                'step'    => 0.05,
            ),
            'menu_bg'            => array(
                'type'    => 'string',
                'default' => $defaults['menu']['bg'],
                'control' => 'color',
            ),
            'menu_color'         => array(
                'type'    => 'string',
                'default' => $defaults['menu']['color'],
                'control' => 'color',
            ),
            'menu_hover_bg'      => array(
                'type'    => 'string',
                'default' => $defaults['menu']['hover_bg'],
                'control' => 'color',
            ),
            'menu_hover_color'   => array(
                'type'    => 'string',
                'default' => $defaults['menu']['hover_color'],
                'control' => 'color',
            ),
            'menu_active_bg'     => array(
                'type'    => 'string',
                'default' => $defaults['menu']['active_bg'],
                'control' => 'color',
            ),
            'menu_active_color'  => array(
                'type'    => 'string',
                'default' => $defaults['menu']['active_color'],
                'control' => 'color',
            ),
            'menu_divider'       => array(
                'type'    => 'string',
                'default' => $defaults['menu']['divider'],
                'control' => 'text',
            ),
        );

        // Typography tokens.
        $typo = $defaults['typography'];
        $tags = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' );
        foreach ( $tags as $tag ) {
            $schema[ $tag . '_size' ] = array(
                'type'    => 'number',
                'default' => $typo[ $tag ]['size'],
                'control' => 'number',
                'min'     => 10,
                'max'     => 64,
                'step'    => 1,
            );
            $schema[ $tag . '_lh' ]   = array(
                'type'    => 'number',
                'default' => $typo[ $tag ]['lh'],
                'control' => 'number',
                'min'     => 1,
                'max'     => 2.5,
                'step'    => 0.05,
            );
            $schema[ $tag . '_weight' ] = array(
                'type'    => 'number',
                'default' => $typo[ $tag ]['weight'],
                'control' => 'number',
                'min'     => 100,
                'max'     => 900,
                'step'    => 100,
            );
            $schema[ $tag . '_track' ]  = array(
                'type'    => 'number',
                'default' => $typo[ $tag ]['track'],
                'control' => 'number',
                'min'     => -1,
                'max'     => 2,
                'step'    => 0.1,
            );
        }

        $schema['body_size']  = array(
            'type'    => 'number',
            'default' => $typo['body']['size'],
            'control' => 'number',
            'min'     => 10,
            'max'     => 64,
            'step'    => 1,
        );
        $schema['body_lh']    = array(
            'type'    => 'number',
            'default' => $typo['body']['lh'],
            'control' => 'number',
            'min'     => 1,
            'max'     => 2.5,
            'step'    => 0.05,
        );
        $schema['body_weight'] = array(
            'type'    => 'number',
            'default' => $typo['body']['weight'],
            'control' => 'number',
            'min'     => 100,
            'max'     => 900,
            'step'    => 100,
        );
        $schema['body_track']  = array(
            'type'    => 'number',
            'default' => $typo['body']['track'],
            'control' => 'number',
            'min'     => -1,
            'max'     => 2,
            'step'    => 0.1,
        );
        $schema['small_size'] = array(
            'type'    => 'number',
            'default' => $typo['small']['size'],
            'control' => 'number',
            'min'     => 10,
            'max'     => 64,
            'step'    => 1,
        );
        $schema['small_lh']   = array(
            'type'    => 'number',
            'default' => $typo['small']['lh'],
            'control' => 'number',
            'min'     => 1,
            'max'     => 2.5,
            'step'    => 0.05,
        );

        // Typography colors.
        $schema['color_text']     = array(
            'type'    => 'string',
            'default' => $typo['color']['text'],
            'control' => 'color',
        );
        $schema['color_headings'] = array(
            'type'    => 'string',
            'default' => $typo['color']['headings'],
            'control' => 'color',
        );
        $schema['color_muted']    = array(
            'type'    => 'string',
            'default' => $typo['color']['muted'],
            'control' => 'color',
        );
        $schema['link_normal']    = array(
            'type'    => 'string',
            'default' => $typo['link']['normal'],
            'control' => 'color',
        );
        $schema['link_hover']     = array(
            'type'    => 'string',
            'default' => $typo['link']['hover'],
            'control' => 'color',
        );
        $schema['link_active']    = array(
            'type'    => 'string',
            'default' => $typo['link']['active'],
            'control' => 'color',
        );
        $schema['link_visited']   = array(
            'type'    => 'string',
            'default' => $typo['link']['visited'],
            'control' => 'color',
        );

        // Forms tokens (aliases, defaults empty for overrides).
        $schema += array(
            'button_bg'         => array( 'type' => 'string', 'default' => '', 'control' => 'color' ),
        );
        $schema += array(
            'button_fg'         => array( 'type' => 'string', 'default' => '', 'control' => 'color' ),
            'button_border'     => array( 'type' => 'string', 'default' => '', 'control' => 'color' ),
            'button_hover_bg'   => array( 'type' => 'string', 'default' => '', 'control' => 'color' ),
            'button_hover_fg'   => array( 'type' => 'string', 'default' => '', 'control' => 'color' ),
            'link_fg'           => array( 'type' => 'string', 'default' => '', 'control' => 'color' ),
            'link_hover_fg'     => array( 'type' => 'string', 'default' => '', 'control' => 'color' ),
            'link_visited_fg'   => array( 'type' => 'string', 'default' => '', 'control' => 'color' ),
            'link_underline'    => array( 'type' => 'string', 'default' => '', 'control' => 'text' ),
            'input_bg'          => array( 'type' => 'string', 'default' => '', 'control' => 'color' ),
            'input_fg'          => array( 'type' => 'string', 'default' => '', 'control' => 'color' ),
            'input_border'      => array( 'type' => 'string', 'default' => '', 'control' => 'color' ),
            'input_placeholder' => array( 'type' => 'string', 'default' => '', 'control' => 'color' ),
            'input_focus_border'=> array( 'type' => 'string', 'default' => '', 'control' => 'color' ),
            'control_accent'    => array( 'type' => 'string', 'default' => '', 'control' => 'color' ),
        );

        // Cards & Tables tokens.
        $schema += array(
            'card_bg'           => array( 'type' => 'string', 'default' => '', 'control' => 'color' ),
            'card_fg'           => array( 'type' => 'string', 'default' => '', 'control' => 'color' ),
            'card_border'       => array( 'type' => 'string', 'default' => '', 'control' => 'color' ),
            'card_shadow'       => array( 'type' => 'string', 'default' => '', 'control' => 'text' ),
            'tooltip_bg'        => array( 'type' => 'string', 'default' => '', 'control' => 'color' ),
            'tooltip_fg'        => array( 'type' => 'string', 'default' => '', 'control' => 'color' ),
            'table_header_bg'   => array( 'type' => 'string', 'default' => '', 'control' => 'color' ),
            'table_header_fg'   => array( 'type' => 'string', 'default' => '', 'control' => 'color' ),
            'table_row_hover_bg'=> array( 'type' => 'string', 'default' => '', 'control' => 'color' ),
            'table_row_gap'     => array( 'type' => 'string', 'default' => '2px', 'control' => 'text' ),
        );

        // Notices & Alerts tokens.
        $schema += array(
            'alert_info_bg'     => array( 'type' => 'string', 'default' => '', 'control' => 'color' ),
            'alert_info_fg'     => array( 'type' => 'string', 'default' => '', 'control' => 'color' ),
            'alert_info_border' => array( 'type' => 'string', 'default' => '', 'control' => 'color' ),
            'note_info'         => array( 'type' => 'string', 'default' => '', 'control' => 'color' ),
            'note_success'      => array( 'type' => 'string', 'default' => '', 'control' => 'color' ),
            'note_warn'         => array( 'type' => 'string', 'default' => '', 'control' => 'color' ),
            'note_error'        => array( 'type' => 'string', 'default' => '', 'control' => 'color' ),
        );

        // Tabs tokens.
        $tabs = $defaults['tabs'];
        $schema += array(
            'tabs_height'          => array( 'type' => 'number', 'default' => $tabs['height'], 'control' => 'number', 'min' => 0, 'max' => 64, 'step' => 1 ),
            'tabs_px'              => array( 'type' => 'number', 'default' => $tabs['px'], 'control' => 'number', 'min' => 0, 'max' => 64, 'step' => 1 ),
            'tabs_py'              => array( 'type' => 'number', 'default' => $tabs['py'], 'control' => 'number', 'min' => 0, 'max' => 64, 'step' => 1 ),
            'tabs_gap'             => array( 'type' => 'number', 'default' => $tabs['gap'], 'control' => 'number', 'min' => 0, 'max' => 64, 'step' => 1 ),
            'tabs_radius'          => array( 'type' => 'number', 'default' => $tabs['radius'], 'control' => 'number', 'min' => 0, 'max' => 64, 'step' => 1 ),
            'tabs_color'           => array( 'type' => 'string', 'default' => $tabs['color'], 'control' => 'color' ),
            'tabs_hover_color'     => array( 'type' => 'string', 'default' => $tabs['hover_color'], 'control' => 'color' ),
            'tabs_active_color'    => array( 'type' => 'string', 'default' => $tabs['active_color'], 'control' => 'color' ),
            'tabs_hover_bg'        => array( 'type' => 'string', 'default' => $tabs['hover_bg'], 'control' => 'color' ),
            'tabs_active_bg'       => array( 'type' => 'string', 'default' => $tabs['active_bg'], 'control' => 'color' ),
            'tabs_indicator_h'     => array( 'type' => 'number', 'default' => $tabs['indicator_h'], 'control' => 'number', 'min' => 1, 'max' => 6, 'step' => 1 ),
            'tabs_indicator_offset'=> array( 'type' => 'number', 'default' => $tabs['indicator_offset'], 'control' => 'number', 'min' => 0, 'max' => 8, 'step' => 1 ),
            'tabs_indicator_color' => array( 'type' => 'string', 'default' => $tabs['indicator_color'] ?? '', 'control' => 'color' ),
            'tab_active_fg'        => array( 'type' => 'string', 'default' => '', 'control' => 'color' ),
            'tab_active_border'    => array( 'type' => 'string', 'default' => '', 'control' => 'color' ),
            'tab_inactive_fg'      => array( 'type' => 'string', 'default' => '', 'control' => 'color' ),
        );

        return $schema;
    }

    /**
     * Field groups keyed by slug.
     *
     * @return array<string,array{title:string,fields:array<int,string>}> Groups.
     */
    function fbm_theme_groups(): array {
        return array(
            'core' => array(
                'title'  => 'Core',
                'fields' => array( 'style', 'preset', 'accent', 'glass_alpha', 'glass_blur', 'glass_elev', 'glass_radius', 'glass_border' ),
            ),
            'menu' => array(
                'title'  => 'Menu',
                'fields' => array( 'menu_item_height', 'menu_item_px', 'menu_item_py', 'menu_gap', 'menu_radius', 'menu_icon_size', 'menu_icon_opacity', 'menu_bg', 'menu_color', 'menu_hover_bg', 'menu_hover_color', 'menu_active_bg', 'menu_active_color', 'menu_divider' ),
            ),
            'typography' => array(
                'title'  => 'Typography',
                'fields' => array( 'h1_size', 'h1_lh', 'h1_weight', 'h1_track', 'h2_size', 'h2_lh', 'h2_weight', 'h2_track', 'h3_size', 'h3_lh', 'h3_weight', 'h3_track', 'h4_size', 'h4_lh', 'h4_weight', 'h4_track', 'h5_size', 'h5_lh', 'h5_weight', 'h5_track', 'h6_size', 'h6_lh', 'h6_weight', 'h6_track', 'body_size', 'body_lh', 'body_weight', 'body_track', 'small_size', 'small_lh', 'color_text', 'color_headings', 'color_muted', 'link_normal', 'link_hover', 'link_active', 'link_visited' ),
            ),
            'forms' => array(
                'title'  => 'Forms',
                'fields' => array( 'button_bg', 'button_fg', 'button_border', 'button_hover_bg', 'button_hover_fg', 'link_fg', 'link_hover_fg', 'link_visited_fg', 'link_underline', 'input_bg', 'input_fg', 'input_border', 'input_placeholder', 'input_focus_border', 'control_accent' ),
            ),
            'cards' => array(
                'title'  => 'Cards & Tables',
                'fields' => array( 'card_bg', 'card_fg', 'card_border', 'card_shadow', 'tooltip_bg', 'tooltip_fg', 'table_header_bg', 'table_header_fg', 'table_row_hover_bg', 'table_row_gap' ),
            ),
            'alerts' => array(
                'title'  => 'Notices & Alerts',
                'fields' => array( 'alert_info_bg', 'alert_info_fg', 'alert_info_border', 'note_info', 'note_success', 'note_warn', 'note_error' ),
            ),
            'tabs' => array(
                'title'  => 'Tabs',
                'fields' => array( 'tabs_height', 'tabs_px', 'tabs_py', 'tabs_gap', 'tabs_radius', 'tabs_color', 'tabs_hover_color', 'tabs_active_color', 'tabs_hover_bg', 'tabs_active_bg', 'tabs_indicator_h', 'tabs_indicator_offset', 'tabs_indicator_color', 'tab_active_fg', 'tab_active_border', 'tab_inactive_fg' ),
            ),
            'admin' => array(
                'title'  => 'Admin',
                'fields' => array(),
            ),
        );
    }

    /**
     * Render a single field.
     *
     * @param string               $key Field key.
     * @param array<string,mixed>  $def Field definition.
     * @param mixed                $val Current value.
     */
    function fbm_field( string $key, array $def, mixed $val ): void {
        $id   = 'fbm_theme_' . $key;
        $name = 'fbm_theme[' . $key . ']';
        $ctrl = $def['control'] ?? 'text';

        if ( 'radio' === $ctrl && isset( $def['options'] ) && is_array( $def['options'] ) ) {
            foreach ( $def['options'] as $opt_val => $label ) {
                $opt_id = $id . '_' . $opt_val;
                echo '<label for="' . esc_attr( $opt_id ) . '"><input type="radio" id="' . esc_attr( $opt_id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( (string) $opt_val ) . '"' . checked( $val, $opt_val, false ) . ' /> ' . esc_html( (string) $label ) . '</label> ';
            }
            return;
        }

        if ( 'number' === $ctrl ) {
            $attrs = '';
            foreach ( array( 'min', 'max', 'step' ) as $attr ) {
                if ( isset( $def[ $attr ] ) ) {
                    $attrs .= ' ' . $attr . '="' . esc_attr( (string) $def[ $attr ] ) . '"';
                }
            }
            echo '<input type="number" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( (string) $val ) . '"' . $attrs . ' />';
            return;
        }

        if ( 'color' === $ctrl ) {
            echo '<input type="color" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( (string) $val ) . '" />';
            return;
        }

        echo '<input type="text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( (string) $val ) . '" />';
    }

    /**
     * Render grouped theme controls as accordion sections.
     *
     * @param array<string,mixed> $opts Current option values.
     */
    function fbm_render_theme_controls( array $opts ): void {
        $schema = fbm_theme_schema();
        foreach ( fbm_theme_groups() as $g ) {
            echo '<details><summary>' . esc_html( $g['title'] ) . '</summary><div class="fbm-group">';
            foreach ( $g['fields'] as $field ) {
                if ( ! isset( $schema[ $field ] ) ) {
                    continue;
                }
                $def = $schema[ $field ];
                $val = $opts[ $field ] ?? $def['default'];
                echo '<div class="fbm-field">';
                echo '<label for="fbm_theme_' . esc_attr( $field ) . '">' . esc_html( ucwords( str_replace( '_', ' ', $field ) ) ) . '</label>';
                fbm_field( $field, $def, $val );
                echo '</div>';
            }
            echo '</div></details>';
        }
    }

    function fbm_render_theme_vertical_nav( array $groups ): void {
        echo '<nav class="fbm-vtabs" role="tablist" aria-orientation="vertical">';
        $first = true;
        foreach ( $groups as $gid => $g ) {
            $label = esc_html( $g['title'] );
            $sel   = $first ? 'true' : 'false';
            $tabi  = $first ? '0' : '-1';
            printf(
                '<button type="button" class="fbm-vtab" role="tab" id="fbm-tab-%1$s" aria-controls="fbm-panel-%1$s" aria-selected="%2$s" tabindex="%3$s">%4$s</button>',
                esc_attr( $gid ),
                $sel,
                $tabi,
                $label
            );
            $first = false;
        }
        echo '</nav>';
    }

    function fbm_render_all_group_panels( array $opts ): void {
        $schema = fbm_theme_schema();
        $groups = fbm_theme_groups();
        foreach ( $groups as $gid => $g ) {
            $hidden = $gid === array_key_first( $groups ) ? '' : ' hidden';
            printf( '<section id="fbm-panel-%1$s" class="fbm-group-panel"%2$s role="tabpanel" aria-labelledby="fbm-tab-%1$s">', esc_attr( $gid ), $hidden );
            echo '<div class="fbm-panel-inner">';
            echo '<h3 class="fbm-panel-title">' . esc_html( $g['title'] ) . '</h3>';
            echo '<div class="fbm-group-grid">';
            foreach ( $g['fields'] as $key ) {
                $def = $schema[ $key ] ?? null;
                if ( ! $def ) {
                    continue;
                }
                fbm_field( $key, $def, $opts[ $key ] ?? ( $def['default'] ?? '' ) );
            }
            echo '</div></div></section>';
        }
    }

    /**
     * Build CSS variables for preview scope.
     *
     * @param array<string,mixed> $o Option values.
     */
    function fbm_css_variables_preview( array $o ): string {
        return \FoodBankManager\UI\Theme::css_variables_preview( $o );
    }

    /**
     * Sanitize theme settings based on schema.
     *
     * @param array<string,mixed> $in Raw input.
     * @return array<string,mixed>
     */
}

namespace FoodBankManager\UI {

use function add_action;
use function check_ajax_referer;
use function json_decode;
use function stripslashes;
use function wp_die;
use function wp_send_json;

add_action('wp_ajax_fbm_css_preview', __NAMESPACE__ . '\\fbm_ajax_css_preview');
add_action('wp_ajax_fbm_theme_defaults', __NAMESPACE__ . '\\fbm_ajax_theme_defaults');

/**
 * AJAX handler: live CSS preview.
 */
function fbm_ajax_css_preview(): void {
    check_ajax_referer('fbm_theme');
    $raw = json_decode(stripslashes((string)($_POST['payload'] ?? '')), true);
    $san = \FoodBankManager\UI\Theme::sanitize(is_array($raw) ? $raw : []);
    $opts = array_replace_recursive(fbm_theme_defaults(), $san);
    echo fbm_css_variables_preview($opts);
    wp_die();
}

/**
 * AJAX handler: return default token values.
 */
function fbm_ajax_theme_defaults(): void {
    $schema   = fbm_theme_schema();
    $defaults = array();
    foreach ($schema as $key => $spec) {
        $defaults[$key] = $spec['default'] ?? null;
    }
    wp_send_json($defaults);
}

}
