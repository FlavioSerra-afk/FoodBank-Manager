<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Attendance;

class TokenService {

	/**
	 * Generate an opaque token for an application ID.
	 *
	 * @param int $application_id Application ID.
	 */
	public static function generate( int $application_id ): string {
		$payload = wp_json_encode(
			array(
				'a' => $application_id,
				't' => time(),
			)
		);
		$sig     = hash_hmac( 'sha256', (string) $payload, wp_salt( 'auth' ) );
		return base64_encode( (string) $payload ) . '.' . $sig;
	}

	/**
	 * Validate a token and return payload data.
	 *
	 * @param string $token Token string.
	 * @return array|null Payload array or null on failure.
	 */
	public static function validate( string $token ): ?array {
		$parts = explode( '.', $token );
		if ( 2 !== count( $parts ) ) {
			return null;
		}
		$payload = base64_decode( $parts[0], true );
		if ( false === $payload ) {
			return null;
		}
		$calc = hash_hmac( 'sha256', $payload, wp_salt( 'auth' ) );
		if ( ! hash_equals( $calc, (string) $parts[1] ) ) {
			return null;
		}
		$data = json_decode( $payload, true );
		return is_array( $data ) ? $data : null;
	}
}
