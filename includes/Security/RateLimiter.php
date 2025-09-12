<?php // phpcs:ignoreFile
/**
 * Simple rate limiter.
 *
 * @package FBM\Security
 */

declare(strict_types=1);

namespace FBM\Security;

use function get_option;
use function get_transient;
use function set_transient;
use function time;
use function sanitize_key;

/**
 * Transient-based rate limiter.
 */
final class RateLimiter {
    /**
     * Check whether a key is allowed and return metadata.
     *
     * @param string $key   Identifier.
     * @param int    $limit Burst limit.
     * @param int    $ttl   Window TTL in seconds.
     * @return array{allowed:bool,limit:int,remaining:int,retry_after:int}
     */
    public static function check( string $key, int $limit = 5, int $ttl = 30 ): array {
        $slug = 'fbm_rl_' . sanitize_key( $key );
        $hits = (int) get_transient( $slug );
        if ( $hits >= $limit ) {
            $expires = (int) get_option( '_transient_timeout_' . $slug );
            return array(
                'allowed'      => false,
                'limit'        => $limit,
                'remaining'    => 0,
                'retry_after'  => max( 0, $expires - time() ),
            );
        }
        set_transient( $slug, $hits + 1, $ttl );
        $expires = (int) get_option( '_transient_timeout_' . $slug );
        return array(
            'allowed'      => true,
            'limit'        => $limit,
            'remaining'    => max( 0, $limit - ( $hits + 1 ) ),
            'retry_after'  => max( 0, $expires - time() ),
        );
    }

    /**
     * Backwards-compatibility wrapper returning allowed flag only.
     */
    public static function allow( string $key, int $limit = 5, int $ttl = 30 ): bool {
        $res = self::check( $key, $limit, $ttl );
        return $res['allowed'];
    }
}

