<?php // phpcs:ignoreFile
/**
 * Diagnostics HTTP controller.
 *
 * @package FoodBankManager\Http
 */

declare(strict_types=1);

namespace FoodBankManager\Http;

use FoodBankManager\Core\Options;
use FoodBankManager\Mail\FailureLog;
use FoodBankManager\Mail\Renderer;
use FBM\Mail\LogRepo;
use FBM\Security\RateLimiter;
use WP_REST_Response;
use function current_user_can;
use function check_admin_referer;
use function check_ajax_referer;
use function wp_mail;
use function wp_send_json_error;
use function wp_send_json_success;
use function add_filter;
use function remove_filter;
use function add_query_arg;
use function menu_page_url;
use function wp_safe_redirect;
use function esc_url_raw;
use function sanitize_email;
use function sanitize_text_field;
use function sanitize_key;
use function absint;
use function is_email;
use function get_option;
use function get_current_user_id;
use function esc_html__;
use function __;
use function wp_die;

final class DiagnosticsController {
    /**
     * Handle Diagnostics HTTP requests.
     *
     * @return void
     */
    public function handle(): void {
        $action = sanitize_key( (string) ( $_POST['fbm_action'] ?? '' ) );
        if ( $action === 'mail_test' ) {
            self::mail_test();
        }
    }

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
        if ( ! current_user_can( 'manage_options' ) ) {
            $ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_key( (string) $_SERVER['REMOTE_ADDR'] ) : '';
            $uid = get_current_user_id();
            if ( ! RateLimiter::allow( 'mailtest_ip_' . $ip ) || ( $uid > 0 && ! RateLimiter::allow( 'mailtest_user_' . (string) $uid ) ) ) {
                wp_die( esc_html__( 'Too many requests', 'foodbank-manager' ), '', array( 'response' => 429 ) );
            }
        }
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
            $ct_filter = static fn(): string => 'text/html; charset=UTF-8';
            add_filter( 'wp_mail_content_type', $ct_filter );
            $sent = wp_mail(
                $to,
                __( 'FoodBank Manager test email', 'foodbank-manager' ),
                '<p>' . __( 'This is a test email from FoodBank Manager.', 'foodbank-manager' ) . '</p>'
            );
            remove_filter( 'wp_mail_content_type', $ct_filter );
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
     * Handle AJAX mail test.
     */
    public static function ajax_mail_test(): WP_REST_Response {
        if ( ! check_ajax_referer( 'fbm_mail_test', '_ajax_nonce', false ) ) {
            return wp_send_json_error(
                array( 'error' => array( 'code' => 'invalid_nonce', 'message' => __( 'Invalid nonce', 'foodbank-manager' ) ) ),
                401
            );
        }
        if ( ! current_user_can( 'fb_manage_diagnostics' ) ) {
            return wp_send_json_error(
                array( 'error' => array( 'code' => 'forbidden', 'message' => __( 'Forbidden', 'foodbank-manager' ) ) ),
                403
            );
        }
        $to = sanitize_email( (string) ( $_POST['to'] ?? '' ) );
        if ( ! is_email( $to ) ) {
            return wp_send_json_error(
                array( 'error' => array( 'code' => 'invalid_param', 'message' => __( 'Invalid email', 'foodbank-manager' ) ) ),
                422
            );
        }
        $tpl = array(
            'subject' => __( 'FoodBank Manager test email', 'foodbank-manager' ),
            'body'    => '<p>' . __( 'This is a test email from FoodBank Manager.', 'foodbank-manager' ) . '</p>',
        );
        $sent = Renderer::send( $tpl, array(), array( $to ) );
        return wp_send_json_success( array( 'sent' => (bool) $sent ) );
    }

    /**
     * Retry a failed email send.
     *
     * @return void
     */
    public static function mail_retry(): void {
        if ( ! current_user_can( 'fb_manage_diagnostics' ) ) {
            wp_die( esc_html__( 'Forbidden', 'foodbank-manager' ) );
        }
        check_admin_referer( 'fbm_diag_mail_retry', '_fbm_nonce' );
        $index = absint( $_POST['index'] ?? -1 );
        $sent  = FailureLog::retry( $index );
        $notice = $sent ? 'retried' : 'error';
        $url    = add_query_arg( array( 'notice' => $notice ), menu_page_url( 'fbm_diagnostics', false ) );
        wp_safe_redirect( esc_url_raw( $url ), 303 );
        exit;
    }

    /**
     * Resend a failed email by log ID.
     *
     * @return void
     */
    public static function mail_resend(): void {
        $id = absint( $_GET['id'] ?? 0 );
        if ( ! current_user_can( 'fb_manage_emails' ) && ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Forbidden', 'foodbank-manager' ) );
        }
        check_admin_referer( 'fbm_mail_resend_' . $id, '_fbm_nonce' );
        $log = LogRepo::get_by_id( $id );
        $sent = false;
        if ( $log ) {
            $to      = array_filter( array_map( 'sanitize_email', explode( ',', (string) ( $log['to_email'] ?? '' ) ) ) );
            $headers = array_filter( array_map( 'sanitize_text_field', explode( "\n", (string) ( $log['headers'] ?? '' ) ) ) );
            $sent    = wp_mail( $to, (string) $log['subject'], '', $headers );
            LogRepo::audit_resend( $id, $sent ? 'sent' : 'error', get_current_user_id(), '' );
        }
        $notice = $sent ? 'resent' : 'error';
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

\class_alias( __NAMESPACE__ . '\\DiagnosticsController', 'FBM\\Http\\DiagnosticsController' );
