<?php
/**
 * Mail replay handler for diagnostics.
 *
 * @package FBM\Admin
 */

declare(strict_types=1);

namespace FBM\Admin;

use FBM\Mail\LogRepo;
use function apply_filters;
use WP_REST_Response;
use function absint;
use function check_ajax_referer;
use function current_user_can;
use function get_current_user_id;
use function sanitize_email;
use function sanitize_text_field;
use function wp_mail;
use function wp_send_json_error;
use function wp_send_json_success;
use function __;

/**
 * Resend failed mails via AJAX.
 */
final class MailReplay {
    /**
     * Handle AJAX resend request.
     */
    public static function handle(): WP_REST_Response {
        if ( ! check_ajax_referer( 'fbm_mail_replay', '_ajax_nonce', false ) ) {
            return wp_send_json_error( array( 'message' => __( 'Invalid nonce', 'foodbank-manager' ) ), 403 );
        }
        if ( ! current_user_can( 'fb_manage_diagnostics' ) ) {
            return wp_send_json_error( array( 'message' => __( 'Forbidden', 'foodbank-manager' ) ), 403 );
        }
        $id   = absint( $_POST['id'] ?? 0 );
        $repo = apply_filters( 'fbm_mail_replay_repo', LogRepo::class );
        $orig = $repo::get_by_id( $id );
        if ( ! $orig ) {
            return wp_send_json_error( 'not_found', 404 );
        }
        $to      = sanitize_email( (string) ( $orig['to_email'] ?? '' ) );
        $subject = sanitize_text_field( (string) ( $orig['subject'] ?? '' ) );
        $headers = sanitize_text_field( (string) ( $orig['headers'] ?? '' ) );
        if ( '' === $to || '' === $subject ) {
            return wp_send_json_error( 'invalid' );
        }
        $body = '';
        $ok   = wp_mail( $to, $subject, $body, $headers );
        $repo::audit_resend( $id, $ok ? 'sent' : 'error', get_current_user_id(), '' );
        return wp_send_json_success( array( 'result' => $ok ? 'sent' : 'error' ) );
    }
}
