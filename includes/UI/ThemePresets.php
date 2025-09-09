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
                'color-bg'      => '#ffffff',
                'color-fg'      => '#000000',
                'link'          => '#2563eb',
                'focus'         => '0 0 0 2px #2563eb',
                'color-accent'  => '#2563eb',
                'color-surface' => '#ffffff',
                'color-text'    => '#000000',
                'color-border'  => 'rgba(0,0,0,0.1)',
                'glass-bg'      => 'rgba(255,255,255,0.08)',
                'glass-border'  => 'rgba(255,255,255,0.3)',
                'glass-blur'    => '12px',
                'card-radius'   => '0.5rem',
                'elev'          => '0 4px 8px rgba(0,0,0,0.1)',
            ),
            'dark' => array(
                'color-bg'      => '#1f2937',
                'color-fg'      => '#f3f4f6',
                'link'          => '#93c5fd',
                'focus'         => '0 0 0 2px #93c5fd',
                'color-accent'  => '#93c5fd',
                'color-surface' => '#374151',
                'color-text'    => '#f3f4f6',
                'color-border'  => 'rgba(255,255,255,0.1)',
                'glass-bg'      => 'rgba(0,0,0,0.25)',
                'glass-border'  => 'rgba(255,255,255,0.15)',
                'glass-blur'    => '12px',
                'card-radius'   => '0.5rem',
                'elev'          => '0 4px 8px rgba(0,0,0,0.5)',
            ),
            'high_contrast' => array(
                'color-bg'      => '#000000',
                'color-fg'      => '#ffffff',
                'link'          => '#00ffff',
                'focus'         => '0 0 0 3px #ffff00',
                'color-accent'  => '#00ffff',
                'color-surface' => '#000000',
                'color-text'    => '#ffffff',
                'color-border'  => '#ffffff',
                'glass-bg'      => '#000000',
                'glass-border'  => '#ffffff',
                'glass-blur'    => '0',
                'card-radius'   => '0.5rem',
                'elev'          => '0 0 0 0 transparent',
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
