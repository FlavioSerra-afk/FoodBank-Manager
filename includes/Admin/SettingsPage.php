<?php
/**
 * Settings admin page.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FoodBankManager\Core\Options;
use FoodBankManager\Security\Helpers;

class SettingsPage {
    public static function route(): void {
        if (! current_user_can('fb_manage_settings') && ! current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'foodbank-manager'), '', ['response' => 403]);
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            self::handle_post();
        }
    }

    private static function handle_post(): void {
        if (! Helpers::verify_nonce('fbm_settings_save', 'fbm_settings_nonce')) {
            wp_die(esc_html__('Invalid nonce', 'foodbank-manager'), '', ['response' => 403]);
        }
        $data = isset($_POST['fbm_settings']) && is_array($_POST['fbm_settings'])
            ? wp_unslash($_POST['fbm_settings'])
            : [];
        Options::saveAll($data);
        add_settings_error('fbm-settings', 'fbm_saved', esc_html__('Settings saved.', 'foodbank-manager'), 'updated');
    }
}
