<?php
/**
 * Shortcode registry bootstrap.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Shortcodes;

use function add_action;
use function add_shortcode;
use function function_exists;

/**
 * Registers supported shortcodes.
 */
final class Shortcodes
{
    /** @var bool Whether hooks have been attached. */
    private static bool $booted = false;

    /** @var bool Whether shortcodes have been registered. */
    private static bool $registered = false;

    /**
     * Hook shortcode registration into WordPress.
     */
    public static function register(): void
    {
        if (self::$booted) {
            return;
        }

        self::$booted = true;

        if (function_exists('add_action')) {
            add_action('init', array(self::class, 'add_shortcodes'));
        } else {
            self::add_shortcodes();
        }
    }

    /**
     * Register shortcode callbacks.
     */
    public static function add_shortcodes(): void
    {
        if (self::$registered || !function_exists('add_shortcode')) {
            return;
        }

        self::$registered = true;

        add_shortcode('fbm_registration_form', array(FormShortcode::class, 'render'));
        add_shortcode('fbm_staff_dashboard', array(\FoodBankManager\Shortcodes\StaffDashboard::class, 'render'));
    }
}

