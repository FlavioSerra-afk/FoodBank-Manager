<?php // phpcs:ignoreFile
/**
 * Diagnostics HTTP controller.
 *
 * @package FoodBankManager\Http
 */

declare(strict_types=1);

namespace FoodBankManager\Http;

use FoodBankManager\Core\Options;
use function current_user_can;
use function check_admin_referer;
use function wp_mail;
use function add_filter;
use function remove_filter;
use function add_query_arg;
use function menu_page_url;
use function wp_safe_redirect;
use function esc_url_raw;
use function sanitize_email;
use function sanitize_text_field;
use function is_email;
use function get_option;
use function esc_html__;
use function __;
use function wp_die;

final class DiagnosticsController {
    /**
     * Handle test email POST action.
     *
     * @return void
     */
    public static function mail_test(): void {
        if ( ! current_user_can( 'fb_manage_diagnostics' ) ) {
            wp_die( esc_html__( 'Forbidden', 'foodbank-manager' ) );
        }
        check_admin_referer( 'fbm_diag_mail_test', '_fbm_nonce' );
        $to         = sanitize_email( (string) get_option( 'admin_email' ) );
        $from_name  = sanitize_text_field( (string) Options::get( 'emails.from_name' ) );
        $from_email = sanitize_email( (string) Options::get( 'emails.from_email' ) );
        $from_filter = static function () use ( $from_email ): string {
            return $from_email;
        };
        $name_filter = static function () use ( $from_name ): string {
            return $from_name;
        };
        if ( is_email( $from_email ) ) {
            add_filter( 'wp_mail_from', $from_filter );
        }
        if ( '' !== $from_name ) {
            add_filter( 'wp_mail_from_name', $name_filter );
        }
        $sent = false;
        if ( is_email( $to ) ) {
            $sent = wp_mail(
                $to,
                __( 'FoodBank Manager test email', 'foodbank-manager' ),
                __( 'This is a test email from FoodBank Manager.', 'foodbank-manager' )
            );
        }
        if ( is_email( $from_email ) ) {
            remove_filter( 'wp_mail_from', $from_filter );
        }
        if ( '' !== $from_name ) {
            remove_filter( 'wp_mail_from_name', $name_filter );
        }
        $notice = $sent ? 'sent' : 'error';
        $url    = add_query_arg( array( 'notice' => $notice ), menu_page_url( 'fbm_diagnostics', false ) );
        wp_safe_redirect( esc_url_raw( $url ), 303 );
        exit;
    }

    /**
     * Get SMTP transport details.
     *
     * @return array<string,string>
     */
    public static function transport_info(): array {
        $info = array(
            'mailer'     => '',
            'host'       => '',
            'port'       => '',
            'encryption' => '',
            'auth'       => '',
        );
        if ( function_exists( 'wp_get_phpmailer' ) ) {
            $phpmailer = wp_get_phpmailer();
            if ( $phpmailer ) {
                $info['mailer']     = sanitize_text_field( (string) $phpmailer->Mailer );
                $info['host']       = sanitize_text_field( (string) $phpmailer->Host );
                $info['port']       = sanitize_text_field( (string) $phpmailer->Port );
                $info['encryption'] = sanitize_text_field( (string) $phpmailer->SMTPSecure );
                $info['auth']       = ! empty( $phpmailer->SMTPAuth ) ? 'yes' : 'no';
            }
        }
        return $info;
    }
}
