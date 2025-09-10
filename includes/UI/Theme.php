<?php
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

/**
 * Provide sanitized design tokens for admin and front-end.
 */
final class Theme {
    /** Default accent colour. */
    public const DEFAULT_ACCENT = '#3B82F6';

    /** @deprecated Backwards compatibility no-op. */
    public static function enqueue_front(): void {}

    /** @deprecated Backwards compatibility no-op. */
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
        $all = Options::all();
        $raw = $all['theme'] ?? array();
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
     * @param array<string,mixed> $raw Raw values.
     * @return array<string,mixed>
     */
    public static function sanitize(array $raw): array {
        $defaults = self::defaults();
        $admin    = self::sanitize_section($raw['admin'] ?? array(), $defaults['admin']);
        $front    = self::sanitize_section($raw['front'] ?? array(), $defaults['front']);

        $front_enabled = filter_var($raw['front']['enabled'] ?? $defaults['front']['enabled'], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        $front['enabled'] = null === $front_enabled ? $defaults['front']['enabled'] : (bool) $front_enabled;

        $match = filter_var($raw['match_front_to_admin'] ?? $defaults['match_front_to_admin'], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        $match = (bool) ( null === $match ? $defaults['match_front_to_admin'] : $match );

        if ( $match ) {
            $front = array_merge($front, $admin);
            $front['enabled'] = $front['enabled'];
        }

        return array(
            'admin'                 => $admin,
            'front'                 => $front,
            'match_front_to_admin'  => $match,
        );
    }

    /**
     * Sanitize one section (admin/front).
     *
     * @param array<string,mixed> $raw Raw values.
     * @param array<string,mixed> $defaults Defaults.
     * @return array<string,mixed>
     */
    private static function sanitize_section(array $raw, array $defaults): array {
        $style = sanitize_key((string)($raw['style'] ?? $defaults['style']));
        if (!in_array($style, array('glass', 'basic'), true)) {
            $style = $defaults['style'];
        }

        $preset = sanitize_key((string)($raw['preset'] ?? $defaults['preset']));
        if (!in_array($preset, array('light','dark','high_contrast'), true)) {
            $preset = $defaults['preset'];
        }

        $accent = sanitize_hex_color((string)($raw['accent'] ?? $defaults['accent']));
        if ('' === $accent) {
            $accent = self::DEFAULT_ACCENT;
        }

        $glass_raw = is_array($raw['glass'] ?? null) ? $raw['glass'] : array();
        $alpha = (float)($glass_raw['alpha'] ?? $defaults['glass']['alpha']);
        $alpha = 'dark' === $preset ? self::clamp($alpha, 0.18, 0.35) : self::clamp($alpha, 0.08, 0.20);
        $blur  = (int)($glass_raw['blur'] ?? $defaults['glass']['blur']);
        $blur  = self::clamp($blur, 0, 20);
        $elev  = (int)($glass_raw['elev'] ?? $defaults['glass']['elev']);
        $elev  = self::clamp($elev, 0, 24);
        $radius = (int)($glass_raw['radius'] ?? $defaults['glass']['radius']);
        $radius = self::clamp($radius, 6, 20);
        $border = (int)($glass_raw['border'] ?? $defaults['glass']['border']);
        $border = self::clamp($border, 1, 2);

        if ('glass' !== $style) {
            $alpha = 0.0;
            $blur  = 0;
        }
        if ('high_contrast' === $preset) {
            $alpha = 0.0;
            $blur  = 0;
        }

        return array(
            'style'  => $style,
            'preset' => $preset,
            'accent' => $accent,
            'glass'  => array(
                'alpha'  => $alpha,
                'blur'   => $blur,
                'elev'   => $elev,
                'radius' => $radius,
                'border' => $border,
            ),
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
    private static function clamp($v, $min, $max) {
        if ($v < $min) {
            return $min;
        }
        if ($v > $max) {
            return $max;
        }
        return $v;
    }

    /**
     * Convert tokens for a section into CSS vars.
     *
     * @param array<string,mixed> $section Section tokens.
     * @param string $selector CSS selector.
     * @return string
     */
    public static function css_vars(array $section, string $selector): string {
        $tokens = self::section_to_css($section);
        $css    = '';
        foreach ($tokens as $key => $val) {
            $css .= $key . ':' . $val . ';';
        }
        return $selector . '{' . $css . '}';
    }

    /**
     * Build CSS tokens for a section.
     *
     * @param array<string,mixed> $section Section tokens.
     * @return array<string,string>
     */
    private static function section_to_css(array $section): array {
        $preset_map = array(
            'light' => array(
                'surface' => '#ffffff',
                'text'    => '#000000',
                'border'  => 'rgba(0,0,0,0.1)',
                'contrast'=> '1',
            ),
            'dark' => array(
                'surface' => '#1f2937',
                'text'    => '#f3f4f6',
                'border'  => 'rgba(255,255,255,0.1)',
                'contrast'=> '1',
            ),
            'high_contrast' => array(
                'surface' => '#000000',
                'text'    => '#ffffff',
                'border'  => '#ffffff',
                'contrast'=> '1.5',
            ),
        );
        $preset = $section['preset'];
        $base   = $preset_map[$preset] ?? $preset_map['light'];

        $glass  = $section['glass'];
        $alpha  = (float) $glass['alpha'];
        $blur   = (int) $glass['blur'];
        $elev   = (int) $glass['elev'];
        $radius = (int) $glass['radius'];
        $border = (int) $glass['border'];

        if ( $alpha > 0 ) {
            $rgb = self::hex_to_rgb($base['surface']);
            $glass_bg = sprintf('rgba(%d,%d,%d,%.2f)', $rgb[0], $rgb[1], $rgb[2], $alpha);
        } else {
            $glass_bg = $base['surface'];
        }

        return array(
            '--fbm-color-accent'        => $section['accent'],
            '--fbm-color-text'          => $base['text'],
            '--fbm-color-surface'       => $base['surface'],
            '--fbm-color-border'        => $base['border'],
            '--fbm-glass-bg'            => $glass_bg,
            '--fbm-glass-border'        => $base['border'],
            '--fbm-glass-blur'          => $blur . 'px',
            '--fbm-card-radius'         => $radius . 'px',
            '--fbm-elev'                => (string) $elev,
            '--fbm-contrast-multiplier' => $base['contrast'],
        );
    }

    /**
     * Convert a hex colour to RGB values.
     *
     * @param string $hex Hex colour.
     * @return array{0:int,1:int,2:int}
     */
    private static function hex_to_rgb(string $hex): array {
        $hex = ltrim($hex, '#');
        if (3 === strlen($hex)) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        $int = hexdec($hex);
        return array(($int >> 16) & 255, ($int >> 8) & 255, $int & 255);
    }

    /**
     * Append admin body classes.
     */
    public static function admin_body_class(string $classes): string {
        $admin = self::admin();
        $classes .= ' fbm-theme--' . $admin['style'];
        $classes .= ' fbm-preset--' . $admin['preset'];
        if (is_rtl()) {
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
    public static function body_class(array $classes): array {
        $front = self::front();
        $classes[] = 'fbm-theme--' . $front['style'];
        $classes[] = 'fbm-preset--' . $front['preset'];
        if (is_rtl()) {
            $classes[] = 'fbm-rtl';
        }
        return $classes;
    }

    /**
     * Glass fallback CSS.
     */
    public static function glass_support_css(): string {
        $targets = '.fbm-card--glass,.fbm-button--glass';
        return '@supports (backdrop-filter: blur(1px)){' . $targets . '{background:var(--fbm-glass-bg);backdrop-filter:blur(var(--fbm-glass-blur));}}'
            . '@supports not (backdrop-filter: blur(1px)){' . $targets . '{background:var(--fbm-color-surface);}}'
            . '@media (forced-colors: active){' . $targets . '{background:var(--fbm-color-surface)!important;backdrop-filter:none!important;}}';
    }
}
