<?php
/**
 * Email templates admin page.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FoodBankManager\Mail\Templates;
use FoodBankManager\Security\Helpers;
use WP_User;

class EmailsPage {
    public static function route(): void {
        if (! current_user_can('fb_manage_emails') && ! current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'foodbank-manager'), '', ['response' => 403]);
        }

        if (isset($_GET['preview'], $_GET['template'])) {
            self::handle_preview();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            self::handle_post();
        }
    }

    private static function handle_post(): void {
        if (! empty($_POST['fbm_email_action']) && $_POST['fbm_email_action'] === 'send_test') {
            self::send_test();
            return;
        }
        if (! Helpers::verify_nonce('fbm_emails_save', 'fbm_emails_nonce')) {
            wp_die(esc_html__('Invalid nonce', 'foodbank-manager'), '', ['response' => 403]);
        }
        $data = isset($_POST['templates']) && is_array($_POST['templates']) ? wp_unslash($_POST['templates']) : [];
        Templates::saveAll($data);
        add_settings_error('fbm-emails', 'fbm_saved', esc_html__('Templates saved.', 'foodbank-manager'), 'updated');
    }

    private static function send_test(): void {
        if (! Helpers::verify_nonce('fbm_emails_test', 'fbm_emails_test_nonce')) {
            wp_die('', '', ['response' => 403]);
        }
        $template = sanitize_key((string) ($_POST['test_template'] ?? 'applicant_confirmation'));
        $user     = wp_get_current_user();
        if (! $user instanceof WP_User) {
            return;
        }
        $to = sanitize_email((string) ($_POST['test_email'] ?? $user->user_email));
        $vars = self::sampleVars();
        $rendered = Templates::render($template, $vars);
        wp_mail($to, $rendered['subject'], $rendered['body_html'], ['Content-Type: text/html; charset=UTF-8']);
        add_settings_error('fbm-emails', 'fbm_test', esc_html__('Test email sent.', 'foodbank-manager'), 'updated');
    }

    private static function handle_preview(): void {
        $template = sanitize_key((string) ($_GET['template'] ?? ''));
        $vars = self::sampleVars();
        $rendered = Templates::render($template, $vars);
        header('Content-Type: text/html; charset=UTF-8');
        echo $rendered['body_html'];
        exit;
    }

    /**
     * Sample variables for preview/test.
     *
     * @return array<string,string>
     */
    private static function sampleVars(): array {
        $now = current_time('mysql');
        return [
            'application_id' => 123,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'created_at' => $now,
            'summary_table' => '<table><tr><th>Name</th><td>Jane Doe</td></tr></table>',
            'qr_code_url' => 'https://example.com/qr.png',
            'reference' => 'FBM-123',
            'application_link' => admin_url('admin.php?page=fbm_application&id=123'),
        ];
    }
}
