<?php
declare(strict_types=1);

namespace FoodBankManager\Core;

if ( ! defined('ABSPATH')) {
    exit;
}

final class Plugin
{
    public const FBM_VER = '2.2.10';
    public const SLUG    = 'foodbank-manager';
    public const VERSION = self::FBM_VER;

    public static function version(): string
    {
        return self::FBM_VER;
    }

    public static function boot(): void
    {
        // Register settings early; Theme::sanitize is tolerant to null.
        add_action('admin_init', [self::class, 'register_settings']);
    }

    public static function register_settings(): void
    {
        // Settings API registration; keep it simple and safe.
        register_setting(
            'fbm',
            'fbm_theme',
            [
                'type'              => 'array',
                'sanitize_callback' => [\FoodBankManager\UI\Theme::class, 'sanitize'],
                // Default is always provided by defaults helper; do not force here.
            ]
        );
    }

    /**
     * Migration hook placeholder for legacy tests.
     */
    public static function maybe_upgrade(): void
    {
        // Intentionally empty.
    }
}

// Expose global define for consumers/tests without redeclaration warnings.
if ( ! defined('FBM_VER')) {
    \define('FBM_VER', \FoodBankManager\Core\Plugin::FBM_VER);
}
