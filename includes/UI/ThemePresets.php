<?php
/**
 * Theme presets tokens.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\UI;

use function sanitize_key;

/**
 * Provide canonical theme tokens.
 */
final class ThemePresets {
    /**
     * Get preset tokens.
     *
     * @param string $preset Preset name.
     * @return array<string,string> Key => CSS value.
     */
    public static function tokens(string $preset): array {
        $preset = sanitize_key($preset);
        $presets = array(
            'light' => array(
                'color-bg' => '#ffffff',
                'color-fg' => '#000000',
                'link'      => '#2563eb',
                'focus'     => '0 0 0 2px #2563eb',
            ),
            'dark' => array(
                'color-bg' => '#1f2937',
                'color-fg' => '#f3f4f6',
                'link'      => '#93c5fd',
                'focus'     => '0 0 0 2px #93c5fd',
            ),
            'high_contrast' => array(
                'color-bg' => '#000000',
                'color-fg' => '#ffffff',
                'link'      => '#00ffff',
                'focus'     => '0 0 0 3px #ffff00',
            ),
        );
        if (!isset($presets[$preset])) {
            $preset = 'light';
        }
        return $presets[$preset];
    }

    /**
     * Build CSS variable block for preset.
     *
     * @param string $preset Preset.
     * @return string CSS variables block.
     */
    public static function css_vars(string $preset): string {
        $tokens = self::tokens($preset);
        $css = ':root .fbm-admin{';
        foreach ($tokens as $key => $value) {
            $css .= '--fbm-' . $key . ':' . $value . ';';
        }
        $css .= '}';
        return $css;
    }
}
