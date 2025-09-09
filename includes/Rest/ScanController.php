<?php
/**
 * QR Scan controller.
 *
 * @package FBM\Rest
 */

declare(strict_types=1);

namespace FBM\Rest;

use FBM\Attendance\CheckinsRepo;
use FBM\Attendance\EventsRepo;
use FBM\Attendance\TicketService;
use WP_REST_Request;
use WP_REST_Response;
use DomainException;
use function apply_filters;
use function base64_decode;
use function gmdate;
use function hash;
use function hash_equals;
use function sanitize_text_field;
use function current_user_can;
use function get_current_user_id;
use function wp_verify_nonce;

/**
 * REST endpoint to verify QR tokens and record attendance.
 */
final class ScanController {
    /**
     * Register REST routes.
     */
    public function register(): void {
        register_rest_route(
            'fbm/v1',
            '/scan',
            array(
                'methods'             => 'POST',
                'callback'            => array($this, 'verify'),
                'permission_callback' => function (): bool {
                    return current_user_can('fbm_manage_events');
                },
                'args'                => array(
                    'token' => array(
                        'type'     => 'string',
                        'required' => true,
                    ),
                ),
            )
        );
    }

    /**
     * Verify a token.
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response
     */
    public function verify(WP_REST_Request $request): WP_REST_Response {
        $nonce = $request->get_header('x-wp-nonce');
        if (!$nonce || !wp_verify_nonce($nonce, 'wp_rest') || !current_user_can('fbm_manage_events')) {
            return new WP_REST_Response(array('ok' => false, 'status' => 'denied'), 200);
        }
        $token = sanitize_text_field((string) $request->get_param('token'));
        if ($token === '' || strlen($token) > 512) {
            return new WP_REST_Response(array('ok' => false, 'status' => 'invalid'), 200);
        }
        $decoded = TicketService::b64url_decode($token);
        if (false === strpos($decoded, '.')) {
            return new WP_REST_Response(array('ok' => false, 'status' => 'invalid'), 200);
        }
        list($sig_raw, $payload) = explode('.', $decoded, 2);
        $kek_b64 = defined('FBM_KEK_BASE64') ? constant('FBM_KEK_BASE64') : '';
        $kek = base64_decode((string) $kek_b64, true);
        if (false === $kek || 32 !== strlen($kek)) {
            return new WP_REST_Response(array('ok' => false, 'status' => 'invalid'), 200);
        }
        $calc = hash_hmac('sha256', $payload, $kek, true);
        if (!hash_equals($calc, $sig_raw)) {
            return new WP_REST_Response(array('ok' => false, 'status' => 'invalid'), 200);
        }
        $parts = explode('|', $payload);
        if (4 !== count($parts)) {
            return new WP_REST_Response(array('ok' => false, 'status' => 'invalid'), 200);
        }
        $event_id = (int) $parts[0];
        $recipient = trim((string) $parts[1]);
        $exp = (int) $parts[2];
        $now_ts = (int) apply_filters('fbm_now', time());
        if ($now_ts > $exp) {
            return new WP_REST_Response(array('ok' => false, 'status' => 'expired'), 200);
        }
        $event = EventsRepo::get($event_id);
        if (!$event || ($event['status'] ?? '') !== 'active') {
            return new WP_REST_Response(array('ok' => false, 'status' => 'invalid'), 200);
        }
        $token_hash = hash('sha256', $token, true);
        if (CheckinsRepo::exists_by_token($event_id, $token_hash)) {
            return new WP_REST_Response(array('ok' => false, 'status' => 'replay'), 200);
        }
        $now = gmdate('Y-m-d H:i:s', $now_ts);
        CheckinsRepo::record(array(
            'event_id'    => $event_id,
            'recipient'   => $recipient,
            'token_hash'  => $token_hash,
            'method'      => 'qr',
            'note'        => null,
            'by'          => get_current_user_id(),
            'verified_at' => $now,
            'created_at'  => $now,
        ));
        return new WP_REST_Response(array(
            'ok'              => true,
            'status'          => 'checked_in',
            'event_id'        => $event_id,
            'recipient_masked'=> self::mask($recipient),
            'at'              => $now_ts,
        ), 200);
    }

    /**
     * Mask an email address or identifier.
     */
    private static function mask(string $recipient): string {
        $at = strpos($recipient, '@');
        if (false === $at) {
            return substr($recipient, 0, 1) . '***';
        }
        $name = substr($recipient, 0, $at);
        $domain = substr($recipient, $at);
        return substr($name, 0, 1) . '***' . $domain;
    }
}
