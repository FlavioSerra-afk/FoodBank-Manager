<?php
/**
 * Diagnostics health status service.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Diagnostics;

use function __;
use function function_exists;
use function get_option;
use function in_array;
use function is_array;
use function is_scalar;
use function is_string;
use function strtolower;
use function stripos;
use function trim;
use function wp_salt;

/**
 * Aggregates health badges for the diagnostics dashboard.
 */
final class HealthStatus {
	public const STATUS_HEALTHY  = 'healthy';
	public const STATUS_DEGRADED = 'degraded';

	/**
	 * Collect status badges for display.
	 *
	 * @return array<int, array<string, string>>
	 */
	public function badges(): array {
		return array(
			$this->mail_transport_badge(),
			$this->token_salt_badge(),
		);
	}

	/**
	 * Summarise the configured mail transport credentials.
	 *
	 * @return array<string, string>
	 */
	private function mail_transport_badge(): array {
		$settings = get_option( 'fbm_settings', array() );
		$mail     = array();

		if ( is_array( $settings ) && isset( $settings['mail'] ) && is_array( $settings['mail'] ) ) {
			$mail = $settings['mail'];
		}

		$transport_raw = isset( $mail['transport'] ) && is_string( $mail['transport'] ) ? $mail['transport'] : 'wp_mail';
		$transport     = strtolower( trim( $transport_raw ) );

		$label   = __( 'Mail transport', 'foodbank-manager' );
		$status  = self::STATUS_HEALTHY;
		$message = __( 'Using the default WordPress mail transport.', 'foodbank-manager' );

		$needs_credentials   = ! in_array( $transport, array( '', 'mail', 'wp_mail', 'wordpress' ), true );
		$requires_user_field = 'smtp' === $transport;

		if ( $needs_credentials ) {
			$username = isset( $mail['username'] ) && is_string( $mail['username'] ) ? trim( $mail['username'] ) : '';
			$secret   = $this->extract_secret( $mail );

			if ( ( $requires_user_field && '' === $username ) || '' === $secret ) {
				$status  = self::STATUS_DEGRADED;
				$message = __( 'External mail transport is configured but credentials are incomplete.', 'foodbank-manager' );
			} else {
				$message = __( 'External mail credentials are configured.', 'foodbank-manager' );
			}
		}

		return array(
			'id'      => 'mail_transport',
			'label'   => $label,
			'status'  => $status,
			'message' => $message,
		);
	}

	/**
	 * Evaluate the configured token salts.
	 *
	 * @return array<string, string>
	 */
	private function token_salt_badge(): array {
		$label   = __( 'Token salts', 'foodbank-manager' );
		$status  = self::STATUS_HEALTHY;
		$message = __( 'Custom salts are configured for token signing.', 'foodbank-manager' );

		$salts = array();

		if ( function_exists( 'wp_salt' ) ) {
			$salts[] = (string) wp_salt( 'fbm-token-sign' );
			$salts[] = (string) wp_salt( 'fbm-token-store' );
		} else {
			$status  = self::STATUS_DEGRADED;
			$message = __( 'Token salts should be updated in wp-config.php.', 'foodbank-manager' );

			return array(
				'id'      => 'token_salts',
				'label'   => $label,
				'status'  => $status,
				'message' => $message,
			);
		}

		foreach ( $salts as $salt ) {
			$trimmed = trim( $salt );

			if ( '' === $trimmed || false !== stripos( $trimmed, 'put your unique phrase here' ) ) {
				$status  = self::STATUS_DEGRADED;
				$message = __( 'Token salts should be updated in wp-config.php.', 'foodbank-manager' );
				break;
			}
		}

		return array(
			'id'      => 'token_salts',
			'label'   => $label,
			'status'  => $status,
			'message' => $message,
		);
	}

	/**
	 * Extract a usable secret from the mail configuration payload.
	 *
	 * @param array<string, mixed> $mail Mail settings payload.
	 */
	private function extract_secret( array $mail ): string {
		$keys = array( 'password', 'api_key', 'token', 'secret', 'key' );

		foreach ( $keys as $key ) {
			if ( isset( $mail[ $key ] ) && is_scalar( $mail[ $key ] ) ) {
				$secret = trim( (string) $mail[ $key ] );

				if ( '' !== $secret ) {
					return $secret;
				}
			}
		}

		return '';
	}
}
