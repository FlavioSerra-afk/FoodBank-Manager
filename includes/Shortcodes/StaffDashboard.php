<?php
/**
 * Staff dashboard shortcode handler.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Shortcodes;

use FoodBankManager\UI\Theme;
use function apply_filters;
use function current_user_can;
use function dirname;
use function esc_html__;
use function esc_url_raw;
use function function_exists;
use function is_readable;
use function is_user_logged_in;
use function ob_get_clean;
use function ob_start;
use function plugins_url;
use function rest_url;
use function wp_create_nonce;
use function wp_enqueue_script;
use function wp_enqueue_style;
use function wp_localize_script;

/**
 * Renders the front-end staff dashboard for QR/manual check-ins.
 */
final class StaffDashboard
{
    /**
     * Render the shortcode markup.
     *
     * @param array<string, string> $atts Shortcode attributes (unused).
     *
     * @return string
     */
    public static function render(array $atts = []): string
    {
        if (!self::is_authorized()) {
            return self::render_denied();
        }

        self::enqueue_assets();

        ob_start();
        $template = dirname(__DIR__, 2) . '/templates/public/staff-dashboard.php';
        if (is_readable($template)) {
            include $template;
        }

        return (string) ob_get_clean();
    }

    /**
     * Determine if the current visitor may use the staff dashboard.
     */
    private static function is_authorized(): bool
    {
        if (!function_exists('is_user_logged_in') || !is_user_logged_in()) {
            return false;
        }

        if (!function_exists('current_user_can')) {
            return false;
        }

        $caps = apply_filters(
            'fbm_staff_dashboard_capabilities',
            [
                'fbm_view',
                'fbm_edit',
                'fbm_manage',
                'fb_manage_attendance',
            ]
        );

        foreach ((array) $caps as $capability) {
            if (is_string($capability) && '' !== $capability && current_user_can($capability)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Enqueue front-end styles and scripts used by the dashboard.
     */
    private static function enqueue_assets(): void
    {
        Theme::enqueue_front();

        $version = defined('FBM_VER') ? FBM_VER : '1.0.0';

        if (function_exists('wp_enqueue_style')) {
            wp_enqueue_style(
                'fbm-public',
                plugins_url('assets/css/public.css', FBM_FILE),
                [],
                $version
            );

            wp_enqueue_style(
                'fbm-staff-dashboard',
                plugins_url('assets/css/staff-dashboard.css', FBM_FILE),
                ['fbm-public'],
                $version
            );
        }

        if (function_exists('wp_enqueue_script')) {
            wp_enqueue_script(
                'fbm-qrcode',
                plugins_url('assets/js/qrcode.min.js', FBM_FILE),
                [],
                $version,
                true
            );

            wp_enqueue_script(
                'fbm-staff-dashboard',
                plugins_url('assets/js/staff-dashboard.js', FBM_FILE),
                ['fbm-qrcode'],
                $version,
                true
            );
        }

        if (function_exists('wp_localize_script')) {
            wp_localize_script(
                'fbm-staff-dashboard',
                'fbmStaffDashboard',
                [
                    'checkinUrl' => esc_url_raw(rest_url('fbm/v1/checkin')),
                    'nonce'      => wp_create_nonce('wp_rest'),
                    'messages'   => [
                        'ready'    => esc_html__('Ready for the next collection.', 'foodbank-manager'),
                        'success'  => esc_html__('Check-in recorded.', 'foodbank-manager'),
                        'duplicate'=> esc_html__('Member already collected today.', 'foodbank-manager'),
                        'error'    => esc_html__('Unable to record attendance. Please try again.', 'foodbank-manager'),
                    ],
                ]
            );
        }
    }

    /**
     * Render the unauthorized notice.
     */
    private static function render_denied(): string
    {
        return '<div class="fbm-staff-dashboard fbm-staff-dashboard--denied">'
            . esc_html__( 'Staff dashboard is available to logged-in team members.', 'foodbank-manager' )
            . '</div>';
    }
}

