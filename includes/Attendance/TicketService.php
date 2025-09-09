<?php
/**
 * Ticket token service.
 *
 * @package FBM\Attendance
 */

declare(strict_types=1);

namespace FBM\Attendance;

use DomainException;
use function add_query_arg;
use function base64_decode;
use function base64_encode;
use function hash;
use function hash_equals;
use function hash_hmac;
use function random_bytes;
use function site_url;
use function strtr;
use function trim;
use function strtolower;

/**
 * Create and verify ticket tokens.
 */
final class TicketService {
    /**
     * Create a new ticket token.
     *
     * @param int    $event_id  Event ID.
     * @param string $recipient Recipient email.
     * @param int    $exp       Expiry timestamp.
     * @return array{token:string,token_hash:string,payload:array{event_id:int,recipient:string,exp:int,nonce:string}}
     */
    public static function createToken(int $event_id, string $recipient, int $exp): array {
        $nonce   = self::b64url_encode(random_bytes(16));
        $payload = self::payload($event_id, $recipient, $exp, $nonce);
        $token   = self::buildToken($payload);
        return array(
            'token'      => $token['token'],
            'token_hash' => $token['token_hash'],
            'payload'    => array(
                'event_id'  => $event_id,
                'recipient' => strtolower(trim($recipient)),
                'exp'       => $exp,
                'nonce'     => $nonce,
            ),
        );
    }

    /**
     * Build token from payload fields.
     *
     * @param int    $event_id  Event ID.
     * @param string $recipient Recipient.
     * @param int    $exp       Expiry.
     * @param string $nonce     Nonce (base64url).
     * @return array{token:string,token_hash:string}
     */
    public static function fromPayload(int $event_id, string $recipient, int $exp, string $nonce): array {
        $payload = self::payload($event_id, $recipient, $exp, $nonce);
        return self::buildToken($payload);
    }

    /**
     * Verify token against stored hash.
     */
    public static function verifyAgainstHash(string $token, string $hash): bool {
        return hash_equals($hash, hash('sha256', $token, true));
    }

    /**
     * Build ticket URL for embedding.
     */
    public static function ticketUrl(string $token): string {
        return add_query_arg(
            array('fbm' => 1, 'action' => 'fbm_scan', 'token' => $token),
            site_url('/')
        );
    }

    /**
     * Base64url encode.
     */
    public static function b64url_encode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64url decode.
     */
    public static function b64url_decode(string $data): string {
        $data = strtr($data, '-_', '+/');
        return (string) base64_decode($data, true);
    }

    /**
     * Build payload string.
     */
    private static function payload(int $event_id, string $recipient, int $exp, string $nonce): string {
        $recipient = strtolower(trim($recipient));
        return $event_id . '|' . $recipient . '|' . $exp . '|' . $nonce;
    }

    /**
     * Build token from payload string.
     *
     * @param string $payload Payload string.
     * @return array{token:string,token_hash:string}
     */
    private static function buildToken(string $payload): array {
        $kek_b64 = defined('FBM_KEK_BASE64') ? constant('FBM_KEK_BASE64') : '';
        $kek     = base64_decode((string) $kek_b64, true);
        if (false === $kek || 32 !== strlen($kek)) {
            throw new DomainException('Encryption key not set');
        }
        $sig   = hash_hmac('sha256', $payload, $kek, true);
        $token = self::b64url_encode($sig . '.' . $payload);
        $hash  = hash('sha256', $token, true);
        return array(
            'token'      => $token,
            'token_hash' => $hash,
        );
    }
}
