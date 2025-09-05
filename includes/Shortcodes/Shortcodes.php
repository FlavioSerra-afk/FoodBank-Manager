<?php declare(strict_types=1);

namespace FBM\Shortcodes;

use function add_shortcode;

final class Shortcodes {
    public static function register(): void {
        static $done = false;
        if ($done) return;
        $done = true;

        add_shortcode('fbm_form', [FormShortcode::class, 'render']);
        // (If we have other shortcodes, register here too)
    }
}

