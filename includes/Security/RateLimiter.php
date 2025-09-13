<?php // phpcs:ignoreFile
/**
 * Simple rate limiter.
 *
 * @package FBM\Security
 */

declare(strict_types=1);

namespace FBM\Security;

use FBM\Security\ThrottleSettings;
use function apply_filters;
use function fbm_send_headers;
use function get_current_user_id;
use function get_option;
use function get_transient;
use function set_transient;
use function time;
use function sanitize_key;
use function wp_get_current_user;

/**
 * Transient-based rate limiter.
 */
final class RateLimiter {
    /**
     * Check whether a key is allowed and return metadata.
     *
     * @param string $key          Identifier.
     * @param int    $limit        Burst limit.
     * @param int    $ttl          Window TTL in seconds.
     * @param bool   $send_headers Whether to emit headers.
     * @return array{allowed:bool,limit:int,remaining:int,retry_after:int}
     */
    public static function check( string $key, int $limit = 5, int $ttl = 30, bool $send_headers = false ): array {
        $slug = 'fbm_rl_' . sanitize_key( $key );
        $hits = (int) get_transient( $slug );
        if ( $hits >= $limit ) {
            $expires = (int) get_option( '_transient_timeout_' . $slug );
            $retry = max( 0, $expires - time() );
            if ( $send_headers ) {
                $now   = (int) apply_filters( 'fbm_now', time() );
                $reset = $retry > 0 ? $now + $retry : $now;
                fbm_send_headers(
                    array(
                        'RateLimit-Limit: ' . $limit,
                        'RateLimit-Remaining: 0',
                        'RateLimit-Reset: ' . $reset,
                        'X-FBM-RateLimit-Limit: ' . $limit,
                        'X-FBM-RateLimit-Remaining: 0',
                        'Retry-After: ' . $retry,
                    )
                );
            }
            return array(
                'allowed'     => false,
                'limit'       => $limit,
                'remaining'   => 0,
                'retry_after' => $retry,
            );
        }
        set_transient( $slug, $hits + 1, $ttl );
        $expires = (int) get_option( '_transient_timeout_' . $slug );
        $remaining = max( 0, $limit - ( $hits + 1 ) );
        $retry     = max( 0, $expires - time() );
        if ( $send_headers ) {
            $now   = (int) apply_filters( 'fbm_now', time() );
            $reset = $retry > 0 ? $now + $retry : $now;
            fbm_send_headers(
                array(
                    'RateLimit-Limit: ' . $limit,
                    'RateLimit-Remaining: ' . $remaining,
                    'RateLimit-Reset: ' . $reset,
                    'X-FBM-RateLimit-Limit: ' . $limit,
                    'X-FBM-RateLimit-Remaining: ' . $remaining,
                )
            );
        }
        return array(
            'allowed'     => true,
            'limit'       => $limit,
            'remaining'   => $remaining,
            'retry_after' => $retry,
        );
    }

    /**
     * Backwards-compatibility wrapper returning allowed flag only.
     */
    public static function allow( string $key, int $limit = 5, int $ttl = 30 ): bool {
        $res = self::check( $key, $limit, $ttl );
        return $res['allowed'];
    }

    /**
     * Rate limit scan requests based on role settings.
     *
     * @return array{allowed:bool,limit:int,remaining:int,retry_after:int}
     */
    public static function scan(): array {
        $settings = ThrottleSettings::get();
        $uid      = get_current_user_id();
        $ip       = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_key( (string) $_SERVER['REMOTE_ADDR'] ) : '';
        $roles    = array();
        if ( function_exists( 'wp_get_current_user' ) ) {
            $user = wp_get_current_user();
            if ( property_exists( $user, 'roles' ) && is_array( $user->roles ) ) {
                $roles = $user->roles;
            }
        }
        if ( empty( $roles ) && isset( $GLOBALS['fbm_current_user_roles'] ) && is_array( $GLOBALS['fbm_current_user_roles'] ) ) {
            $roles = $GLOBALS['fbm_current_user_roles'];
        }
        $role = sanitize_key( $roles[0] ?? '' );
        $mult = (float) ( $settings['role_multipliers'][ $role ] ?? 1.0 );
        if ( 0.0 === $mult ) {
            $now = (int) apply_filters( 'fbm_now', time() );
            fbm_send_headers(
                array(
                    'RateLimit-Limit: 0',
                    'RateLimit-Remaining: 0',
                    'RateLimit-Reset: ' . $now,
                    'X-FBM-RateLimit-Limit: 0',
                    'X-FBM-RateLimit-Remaining: 0',
                )
            );
            return array(
                'allowed'     => true,
                'limit'       => 0,
                'remaining'   => 0,
                'retry_after' => 0,
            );
        }
        $limit = (int) ceil( $settings['base_limit'] * max( 0.1, $mult ) );
        $limit = max( 1, $limit );
        $ttl   = (int) $settings['window_seconds'];
        $id    = $uid > 0 ? 'u' . $uid : 'ip_' . $ip;
        $key   = 'scan_' . $role . '_' . $id;
        return self::check( $key, $limit, $ttl, true );
    }
}

