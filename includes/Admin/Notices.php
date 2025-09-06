<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Admin;
use FoodBankManager\Core\Screen;
use FoodBankManager\Core\Options;

final class Notices {
    private static bool $missingKek = false;
    private static bool $missingSodium = false;
    private static int $renderCount = 0;
    private static ?string $dupMessage = null;

    public static function boot(): void {}

    public static function render(): void {
        static $printed = false;
        if ($printed) {
            return;
        }

        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        $id     = $screen ? (string) $screen->id : '';
        $is_fbm = ($id === 'toplevel_page_fbm') || str_starts_with($id, 'foodbank_page_fbm_');

        if (!$is_fbm) {
            return;
        }

        self::$renderCount++;
        $printed = true;

        $dups = \FoodBankManager\Core\Install::duplicates();
        if ($dups) {
            $action = 'fbm_consolidate_plugins';
            echo '<div class="notice notice-warning"><p>' . esc_html__('Multiple FoodBank Manager copies detected.', 'foodbank-manager') . '</p>';
            echo '<form method="post" action=""><input type="hidden" name="fbm_action" value="' . esc_attr($action) . '" />';
            wp_nonce_field($action);
            echo '<p><button class="button">' . esc_html__('Consolidate', 'foodbank-manager') . '</button></p></form></div>';
        }

        if (self::$dupMessage) {
            echo '<div class="notice notice-success"><p>' . esc_html(self::$dupMessage) . '</p></div>';
            self::$dupMessage = null;
        }

        if (self::$missingSodium) {
            echo '<div class="notice notice-error"><p>' . esc_html__('Sodium extension not available; encryption disabled.', 'foodbank-manager') . '</p></div>';
        }

        if (self::$missingKek) {
            echo '<div class="notice notice-error"><p>' . esc_html__('FoodBank Manager encryption key is not configured.', 'foodbank-manager') . '</p></div>';
        }

        if (!defined('FBM_KEK_BASE64') || empty(constant('FBM_KEK_BASE64'))) {
            echo '<div class="notice notice-warning"><p>' . esc_html__('FoodBank Manager: Encryption key (FBM_KEK_BASE64) not set. Some features are degraded.', 'foodbank-manager') . '</p></div>';
        }

        $from = Options::get('emails.from_email');
        if (!is_email($from)) {
            echo '<div class="notice notice-error"><p>' . esc_html__('FoodBank Manager: From email is not configured.', 'foodbank-manager') . '</p></div>';
        }

        $provider = Options::get('forms.captcha_provider');
        if ($provider !== 'off') {
            $site   = Options::get('forms.captcha_site_key');
            $secret = Options::get('forms.captcha_secret');
            if ($site === '' || $secret === '') {
                echo '<div class="notice notice-warning"><p>' . esc_html__('FoodBank Manager: CAPTCHA keys are missing.', 'foodbank-manager') . '</p></div>';
            }
        }
    }

    public static function render_caps_fix_notice(): void {
        static $printed = false;
        if ($printed) {
            return;
        }
        if (!current_user_can('manage_options')) {
            return;
        }

        foreach (\FBM\Auth\Capabilities::all() as $cap) {
            if (current_user_can($cap)) {
                return;
            }
        }

        if (get_transient('fbm_caps_notice_dismissed')) {
            return;
        }

        $action      = 'fbm_caps_notice_dismiss';
        $dismiss_url = add_query_arg(
            [
                'fbm_action' => $action,
                '_wpnonce'   => wp_create_nonce($action),
            ],
            admin_url()
        );

        $repair_url = admin_url('admin.php?page=fbm_diagnostics');

        echo '<div class="notice notice-warning"><p>' .
            esc_html__('FoodBank Manager is installed, but custom capabilities are missing for Administrators.', 'foodbank-manager') . ' <a href="' . esc_url($repair_url) . '">' . esc_html__('Open Diagnostics → Repair caps', 'foodbank-manager') . '</a> · <a href="' . esc_url($dismiss_url) . '">' . esc_html__('Dismiss', 'foodbank-manager') . '</a>' .
            '</p></div>';

        $printed = true;
    }

    public static function maybe_handle_caps_notice_dismiss(): void {
        if (empty($_GET['fbm_action'])) {
            return;
        }
        $action = sanitize_key((string) $_GET['fbm_action']);
        if ($action !== 'fbm_caps_notice_dismiss') {
            return;
        }
        check_admin_referer($action);
        if (!current_user_can('manage_options')) {
            return;
        }
        set_transient('fbm_caps_notice_dismissed', 1, DAY_IN_SECONDS);
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
        \FBM\Auth\Capabilities::ensure_for_admin();
        wp_safe_redirect(remove_query_arg(['fbm_repair_caps', '_wpnonce']));
        exit;
    }

    /**
     * Mark that the KEK is missing.
     */
    public static function missing_kek(): void {
        if (current_user_can('manage_options')) {
            self::$missingKek = true;
        }
    }

    public static function maybe_handle_consolidate_plugins(): void {
        if (empty($_POST['fbm_action'])) {
            return;
        }
        $action = sanitize_key((string) $_POST['fbm_action']);
        if ($action !== 'fbm_consolidate_plugins') {
            return;
        }
        check_admin_referer($action);
        if (!current_user_can('manage_options')) {
            return;
        }
        $count = \FoodBankManager\Core\Install::consolidate();
        if ($count > 0) {
            self::$dupMessage = current_user_can('delete_plugins')
                ? __('Duplicate installs consolidated.', 'foodbank-manager')
                : __('Duplicates deactivated. Go to Plugins to delete.', 'foodbank-manager');
        } else {
            self::$dupMessage = __('No duplicate installs found.', 'foodbank-manager');
        }
    }

    /**
     * Mark that the sodium extension is missing.
     */
    public static function missing_sodium(): void {
        self::$missingSodium = true;
    }

    public static function getRenderCount(): int {
        return self::$renderCount;
    }
}
