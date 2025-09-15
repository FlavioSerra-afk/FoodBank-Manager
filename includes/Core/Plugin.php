<?php

declare(strict_types=1);

namespace FoodBankManager\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class Plugin {
    public $12.2.14$2
    public const VERSION = self::FBM_VER;

    public static function version(): string {
        return self::FBM_VER;
    }

    public static function boot(): void {
        // Register settings early; Theme::sanitize is tolerant to null.
        add_action( 'admin_init', array( self::class, 'register_settings' ) );
    }

    public static function register_settings(): void {
        // Settings API registration; keep it simple and safe.
        register_setting(
            'fbm',
            'fbm_theme',
            array(
                'type'              => 'array',
                'sanitize_callback' => array( \FoodBankManager\UI\Theme::class, 'sanitize' ),
                // Default is always provided by defaults helper; do not force here.
            )
        );
    }
}
