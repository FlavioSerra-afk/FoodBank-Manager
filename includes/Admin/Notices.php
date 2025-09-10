<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Admin;
use FoodBankManager\Core\Screen;
use FoodBankManager\Core\Options;
use FoodBankManager\Core\Install;

final class Notices {
    private static bool $missingKek = false;
    private static bool $missingSodium = false;
    private static int $renderCount = 0;
    private static bool $printed = false;

    /**
     * Register notice-related hooks.
     *
     * @return void
     */
    public static function boot(): void {
        add_action('admin_init', static function (): void {
            Install::getCachedScan();
        });
        add_action('admin_post_fbm_consolidate_plugins', [__CLASS__, 'handleConsolidatePlugins']);
        add_action('admin_post_fbm_deactivate_duplicates', [__CLASS__, 'handleDeactivateDuplicates']);
    }

    /**
     * Output admin notices for FoodBank Manager.
     *
     * @return void
     */
    public static function render(): void {
        if (self::$printed) {
            return;
        }

        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        $id     = $screen ? (string) $screen->id : '';
        $is_fbm = ($id === 'toplevel_page_fbm') || str_starts_with($id, 'foodbank_page_fbm_');
        $show   = $is_fbm || isset($_GET['fbm_consolidated']);
        if (!$show) {
            return;
        }

        self::$renderCount++;
        self::$printed = true;

        $scan = Install::getCachedScan();
        $dups = $scan['duplicates'];
        if ($dups && $is_fbm && current_user_can('manage_options')) {
            echo '<div class="notice notice-warning"><p>' . esc_html__('Multiple FoodBank Manager copies detected', 'foodbank-manager') . '</p>';
            echo '<ul>';
            foreach ($dups as $d) {
                echo '<li>' . esc_html($d['dir'] . ' (' . $d['version'] . ')') . '</li>';
            }
            echo '</ul>';
            $url = admin_url('admin-post.php');
            echo '<form method="post" action="' . esc_url($url) . '"><input type="hidden" name="action" value="fbm_consolidate_plugins" />';
            wp_nonce_field('fbm_consolidate_plugins');
            echo '<p><button class="button button-primary">' . esc_html__('Consolidate (deactivate & delete)', 'foodbank-manager') . '</button></p></form>';
            echo '<form method="post" action="' . esc_url($url) . '"><input type="hidden" name="action" value="fbm_deactivate_duplicates" />';
            wp_nonce_field('fbm_deactivate_duplicates');
            echo '<p><button class="button">' . esc_html__('Deactivate only', 'foodbank-manager') . '</button></p></form></div>';
        }

        if (isset($_GET['fbm_consolidated'])) {
            $deleted = (int)($_GET['deleted'] ?? 0);
            $msg     = $deleted > 0
                ? (current_user_can('delete_plugins')
                    ? __('Duplicate installs consolidated.', 'foodbank-manager')
                    : __('Duplicates deactivated. Go to Plugins to delete.', 'foodbank-manager'))
                : __('No duplicate installs found.', 'foodbank-manager');
            $class = $deleted > 0 ? 'notice-success' : 'notice-info';
            echo '<div class="notice ' . esc_attr($class) . '"><p>' . esc_html($msg) . '</p></div>';
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

    /**
     * Show a notice if core capabilities are missing.
     *
     * @return void
     */
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
        if (! current_user_can('manage_options')) {
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

    public static function handleConsolidatePlugins(): void {
        check_admin_referer('fbm_consolidate_plugins');
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Insufficient permissions', 'foodbank-manager'));
        }
        Install::consolidate(true);
        $url = admin_url('admin.php?page=fbm_diagnostics&fbm_consolidated=1');
        wp_safe_redirect($url);
        exit;
    }

    public static function handleDeactivateDuplicates(): void {
        check_admin_referer('fbm_deactivate_duplicates');
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Insufficient permissions', 'foodbank-manager'));
        }
        Install::consolidate(false);
        $url = admin_url('admin.php?page=fbm_diagnostics&fbm_consolidated=1');
        wp_safe_redirect($url);
        exit;
    }

    public static function handle_consolidate_plugins(): void {
        check_admin_referer('fbm_consolidate');
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Insufficient permissions', 'foodbank-manager'));
        }
        $res = Install::consolidate(true);
        $url = add_query_arg(
            [
                'fbm_consolidated' => '1',
                'deleted' => (string) $res['deleted'],
            ],
            admin_url('plugins.php')
        );
        wp_redirect($url);
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

    /**
     * Reset counters for tests.
     */
    public static function reset_for_tests(): void {
        self::$missingKek = false;
        self::$missingSodium = false;
        self::$renderCount = 0;
        self::$printed = false;
    }
}

\class_alias( __NAMESPACE__ . '\\Notices', 'FBM\\Admin\\Notices' );

if (defined('FBM_TESTS')) {
    add_action('fbm_test_reset_notices', static function (): void {
        /** @phpstan-ignore-next-line */
        \FBM\Admin\Notices::reset_for_tests();
    });
    add_action('fbm_test_set_missing_kek', static function (): void {
        /** @phpstan-ignore-next-line */
        \FBM\Admin\Notices::missing_kek();
    });
}
