<?php

declare(strict_types=1);

namespace FoodBankManager\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use function add_action;
use function register_setting;

final class Plugin
{
    public const FBM_VER = '2.2.14';
    public const VERSION = self::FBM_VER;

    public static function version(): string
    {
        return self::FBM_VER;
    }

    public static function boot(): void
    {
        add_action('admin_init', array(self::class, 'register_settings'));
    }

    public static function register_settings(): void
    {
        register_setting(
            'fbm',
            'fbm_theme',
            array(
                'type'              => 'array',
                'sanitize_callback' => array(\FoodBankManager\UI\Theme::class, 'sanitize'),
            )
        );
    }
}
