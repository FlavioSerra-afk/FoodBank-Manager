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
use FBM\Security\RateLimiter;
use WP_REST_Request;
use WP_REST_Response;
use DomainException;
use function apply_filters;
use function base64_decode;
use function gmdate;
use function hash;
use function hash_equals;
use function __;
use function sanitize_text_field;
use function sanitize_key;
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
                    return current_user_can('fb_manage_attendance');
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
        if (!$nonce || !wp_verify_nonce($nonce, 'wp_rest') || !current_user_can('fb_manage_attendance')) {
            return new WP_REST_Response(
                array(
                    'checked_in' => false,
                    'status'     => 'denied',
                    'message'    => __('Access denied', 'foodbank-manager'),
                ),
                200
            );
        }
        if (!current_user_can('manage_options')) {
            $ip  = isset($_SERVER['REMOTE_ADDR']) ? sanitize_key((string) $_SERVER['REMOTE_ADDR']) : '';
            $uid = get_current_user_id();
            if (!RateLimiter::allow('scan_ip_' . $ip) || ($uid > 0 && !RateLimiter::allow('scan_user_' . (string) $uid))) {
                return new WP_REST_Response(
                    array(
                        'checked_in' => false,
                        'status'     => 'rate-limited',
                        'message'    => __('Too many requests', 'foodbank-manager'),
                    ),
                    429
                );
            }
        }
        $token = sanitize_text_field((string) $request->get_param('token'));
        if ($token === '' || strlen($token) > 512) {
            return new WP_REST_Response(
                array(
                    'checked_in' => false,
                    'status'     => 'invalid',
                    'message'    => __('Invalid token', 'foodbank-manager'),
                ),
                200
            );
        }
        $decoded = TicketService::b64url_decode($token);
        if (false === strpos($decoded, '.')) {
            return new WP_REST_Response(
                array(
                    'checked_in' => false,
                    'status'     => 'invalid',
                    'message'    => __('Invalid token', 'foodbank-manager'),
                ),
                200
            );
        }
        list($sig_raw, $payload) = explode('.', $decoded, 2);
        $kek_b64 = defined('FBM_KEK_BASE64') ? constant('FBM_KEK_BASE64') : '';
        $kek = base64_decode((string) $kek_b64, true);
        if (false === $kek || 32 !== strlen($kek)) {
            return new WP_REST_Response(
                array(
                    'checked_in' => false,
                    'status'     => 'invalid',
                    'message'    => __('Invalid token', 'foodbank-manager'),
                ),
                200
            );
        }
        $calc = hash_hmac('sha256', $payload, $kek, true);
        if (!hash_equals($calc, $sig_raw)) {
            return new WP_REST_Response(
                array(
                    'checked_in' => false,
                    'status'     => 'invalid',
                    'message'    => __('Invalid token', 'foodbank-manager'),
                ),
                200
            );
        }
        $parts = explode('|', $payload);
        if (4 !== count($parts)) {
            return new WP_REST_Response(
                array(
                    'checked_in' => false,
                    'status'     => 'invalid',
                    'message'    => __('Invalid token', 'foodbank-manager'),
                ),
                200
            );
        }
        $event_id = (int) $parts[0];
        $recipient = trim((string) $parts[1]);
        $exp = (int) $parts[2];
        $now_ts = (int) apply_filters('fbm_now', time());
        if ($now_ts > $exp) {
            return new WP_REST_Response(
                array(
                    'checked_in' => false,
                    'status'     => 'expired',
                    'message'    => __('Expired token', 'foodbank-manager'),
                ),
                200
            );
        }
        $event = EventsRepo::get($event_id);
        if (!$event || ($event['status'] ?? '') !== 'active') {
            return new WP_REST_Response(
                array(
                    'checked_in' => false,
                    'status'     => 'invalid',
                    'message'    => __('Invalid event', 'foodbank-manager'),
                ),
                200
            );
        }
        $token_hash = hash('sha256', $token, true);
        if (CheckinsRepo::exists_by_token($event_id, $token_hash)) {
            return new WP_REST_Response(
                array(
                    'checked_in' => false,
                    'status'     => 'replay',
                    'message'    => __('Already checked in', 'foodbank-manager'),
                ),
                200
            );
        }
        $checked    = false;
        $now        = gmdate('Y-m-d H:i:s', $now_ts);
        $checkin_id = CheckinsRepo::record(array(
            'event_id'    => $event_id,
            'recipient'   => $recipient,
            'token_hash'  => $token_hash,
            'method'      => 'qr',
            'note'        => null,
            'by'          => get_current_user_id(),
            'verified_at' => $now,
            'created_at'  => $now,
        ));
        if ($checkin_id > 0) {
            $checked = true;
        }
        return new WP_REST_Response(array(
            'ticket_id'      => (int) $checkin_id,
            'checked_in'     => $checked,
            'status'         => $checked ? 'checked-in' : 'not-checked',
            'message'        => $checked ? __('Checked in', 'foodbank-manager') : __('Not checked in', 'foodbank-manager'),
            'recipient_masked'=> self::mask($recipient),
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
