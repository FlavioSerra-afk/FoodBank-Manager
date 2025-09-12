<?php // phpcs:ignoreFile
/**
 * Simple rate limiter.
 *
 * @package FBM\Security
 */

declare(strict_types=1);

namespace FBM\Security;

use function get_transient;
use function set_transient;
use function sanitize_key;

/**
 * Transient-based rate limiter.
 */
final class RateLimiter {
/**
 * Check whether a key is allowed.
 *
 * @param string $key   Identifier.
 * @param int    $limit Burst limit.
 * @param int    $ttl   Window TTL in seconds.
 * @return bool True if allowed, false if throttled.
 */
public static function allow( string $key, int $limit = 5, int $ttl = 30 ): bool {
$slug = 'fbm_rl_' . sanitize_key( $key );
$hits = (int) get_transient( $slug );
if ( $hits >= $limit ) {
return false;
}
set_transient( $slug, $hits + 1, $ttl );
return true;
}
}

