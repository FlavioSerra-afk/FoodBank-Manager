<?php
/**
 * Email Templates admin page.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use FoodBankManager\Mail\TemplateRepo;
use FoodBankManager\Mail\Renderer;
use function current_user_can;
use function wp_die;
use function esc_html__;
use function sanitize_key;
use function sanitize_text_field;
use function wp_unslash;
use function check_admin_referer;
use function wp_safe_redirect;
use function add_query_arg;
use function menu_page_url;
use function wp_send_json;
use function wp_get_current_user;
use function wp_nonce_field;

/**
 * Email Templates page handler.
 */
final class EmailTemplatesPage {
    private const CAP = 'fb_manage_emails';

    /**
     * Route requests.
     *
     * @return void
     */
    public static function route(): void {
        if ( ! current_user_can( self::CAP ) ) {
            wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ) );
        }
        TemplateRepo::register_setting();
        $method = strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ?? '' ) ) );
        $action = sanitize_key( wp_unslash( $_POST['fbm_action'] ?? '' ) );
        if ( 'POST' === $method ) {
            if ( 'save' === $action ) {
                check_admin_referer( 'fbm_email_templates_save', '_fbm_nonce' );
                self::handle_save();
            } elseif ( 'preview' === $action ) {
                check_admin_referer( 'fbm_email_templates_preview', '_fbm_nonce' );
                $preview = self::handle_preview();
                if ( isset( $_POST['fbm_ajax'] ) ) {
                    wp_send_json( $preview );
                }
            } elseif ( 'send_test' === $action ) {
                check_admin_referer( 'fbm_email_templates_send_test', '_fbm_nonce' );
                $sent = self::handle_send_test();
                if ( isset( $_POST['fbm_ajax'] ) ) {
                    wp_send_json( array( 'sent' => $sent ? 1 : 0 ) );
                }
            }
        }
        $templates = TemplateRepo::all();
        $current   = isset( $_GET['slug'] ) ? sanitize_key( (string) $_GET['slug'] ) : '';
        require FBM_PATH . 'templates/admin/email-templates-list.php';
    }

    /**
     * Handle save.
     *
     * @return void
     */
    private static function handle_save(): void {
        $slug = sanitize_key( wp_unslash( $_POST['slug'] ?? '' ) );
        $data = array(
            'subject' => sanitize_text_field( wp_unslash( (string) ( $_POST['subject'] ?? '' ) ) ),
            'body'    => wp_unslash( (string) ( $_POST['body'] ?? '' ) ),
            'to'      => $_POST['to'] ?? array(),
            'cc'      => $_POST['cc'] ?? array(),
            'bcc'     => $_POST['bcc'] ?? array(),
            'enabled' => ! empty( $_POST['enabled'] ),
        );
        TemplateRepo::save( $slug, $data );
        $url = add_query_arg( 'notice', 'saved', menu_page_url( 'fbm_emails', false ) );
        wp_safe_redirect( $url, 303 );
        exit;
    }

    /**
     * Handle preview.
     *
     * @return array{subject:string,body:string}
     */
    private static function handle_preview(): array {
        $slug = sanitize_key( wp_unslash( $_POST['slug'] ?? '' ) );
        $tpl  = TemplateRepo::get( $slug );
        $tpl['subject'] = isset( $_POST['subject'] ) ? (string) $_POST['subject'] : ( $tpl['subject'] ?? '' );
        $tpl['body']    = isset( $_POST['body'] ) ? (string) $_POST['body'] : ( $tpl['body'] ?? '' );
        return Renderer::render( $tpl, self::sample_data() );
    }

    /**
     * Handle send test.
     *
     * @return bool
     */
    private static function handle_send_test(): bool {
        $slug = sanitize_key( wp_unslash( $_POST['slug'] ?? '' ) );
        $tpl  = TemplateRepo::get( $slug );
        $tpl['subject'] = isset( $_POST['subject'] ) ? (string) $_POST['subject'] : ( $tpl['subject'] ?? '' );
        $tpl['body']    = isset( $_POST['body'] ) ? (string) $_POST['body'] : ( $tpl['body'] ?? '' );
        $user = wp_get_current_user();
        $email = (string) $user->user_email;
        return Renderer::send( $tpl, self::sample_data(), array( $email ) );
    }

    /**
     * Sample data for tokens.
     *
     * @return array<string,string>
     */
    private static function sample_data(): array {
        return array(
            'first_name' => 'Test',
            'event_date' => '2024-01-01',
        );
    }
}
