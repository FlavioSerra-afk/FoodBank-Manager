<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Admin;

final class Notices {
    public static function boot(): void {
        add_action('admin_notices', [self::class, 'maybeShowCapsRepair']);
        add_action('admin_init', [self::class, 'handleCapsRepair']);
    }

    public static function maybeShowCapsRepair(): void {
        $s = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$s || strpos($s->id, 'foodbank_page_') !== 0 && $s->id !== 'toplevel_page_fbm-dashboard') {
            return;
        }
        if (! current_user_can('administrator')) {
            return;
        }
        if (! current_user_can('fb_manage_dashboard')) {
            $url = wp_nonce_url(
                add_query_arg('fbm_repair_caps', '1', admin_url('admin.php?page=fbm-diagnostics')),
                'fbm_repair_caps'
            );
            echo '<div class="notice notice-warning"><p>' .
                esc_html__('FoodBank Manager detected missing capabilities on your Administrator role.', 'foodbank-manager') . ' ' .
                '<a class="button button-primary" href="' . esc_url($url) . '">' .
                esc_html__('Repair capabilities', 'foodbank-manager') .
                '</a></p></div>';
        }
    }

    public static function handleCapsRepair(): void {
        if (!isset($_GET['fbm_repair_caps'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- verified below
            return;
        }
        if (! current_user_can('administrator')) {
            return;
        }
        if (! wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'] ?? '')), 'fbm_repair_caps')) {
            return;
        }
        \FoodBankManager\Auth\Roles::ensure_admin_caps();
        wp_safe_redirect(remove_query_arg(['fbm_repair_caps', '_wpnonce']));
        exit;
    }

    /**
     * Show missing KEK notice.
     */
    public static function missing_kek(): void {
        if (! current_user_can('manage_options')) {
            return;
        }
        add_action(
            'admin_notices',
            function (): void {
                $s = function_exists('get_current_screen') ? get_current_screen() : null;
                if (!$s || (strpos($s->id, 'foodbank_page_') !== 0 && $s->id !== 'toplevel_page_fbm-dashboard')) {
                    return;
                }
                echo '<div class="notice notice-error"><p>' . \esc_html__('FoodBank Manager encryption key is not configured.', 'foodbank-manager') . '</p></div>';
            }
        );
    }
}
