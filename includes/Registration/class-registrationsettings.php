<?php
/**
 * Registration settings helper.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Registration;

use function get_option;

/**
 * Provides access to persisted registration configuration.
 */
final class RegistrationSettings {
	private const OPTION_NAME = 'fbm_settings';

	/**
	 * Default configuration values.
	 *
	 * @return array{registration:array{auto_approve:bool}}
	 */
	public static function defaults(): array {
		return array(
			'registration' => array(
				'auto_approve' => true,
			),
		);
	}

	/**
	 * Resolve whether registrations should be auto-approved.
	 */
	public function auto_approve(): bool {
		$settings = get_option( self::OPTION_NAME, array() );

		if ( ! is_array( $settings ) ) {
			$settings = array();
		}

		$registration = array();

		if ( isset( $settings['registration'] ) && is_array( $settings['registration'] ) ) {
			$registration = $settings['registration'];
		}

		$normalized = self::normalize_registration_settings( $registration );

		return (bool) $normalized['auto_approve'];
	}

	/**
	 * Normalize persisted registration settings with defaults.
	 *
	 * @param array<string,mixed> $registration Raw stored settings.
	 *
	 * @return array{auto_approve:bool}
	 */
	public static function normalize_registration_settings( array $registration ): array {
		$defaults = self::defaults()['registration'];
		$auto     = $defaults['auto_approve'];

		if ( array_key_exists( 'auto_approve', $registration ) ) {
			$auto = self::to_bool( $registration['auto_approve'] );
		}

		return array(
			'auto_approve' => $auto,
		);
	}

	/**
	 * Sanitize registration payload received from a form submission.
	 *
	 * @param mixed $registration Raw registration payload.
	 *
	 * @return array{auto_approve:bool}
	 */
	public static function sanitize_registration_payload( $registration ): array {
		if ( ! is_array( $registration ) ) {
			$registration = array();
		}

		$auto = false;

		if ( isset( $registration['auto_approve'] ) ) {
			$auto = self::to_bool( $registration['auto_approve'] );
		}

		return array(
			'auto_approve' => $auto,
		);
	}

	/**
	 * Merge sanitized registration settings into an existing configuration array.
	 *
	 * @param mixed                    $existing     Current configuration payload.
	 * @param array{auto_approve:bool} $registration Sanitized registration settings.
	 *
	 * @return array<string,mixed>
	 */
	public static function merge_registration_settings( $existing, array $registration ): array {
		if ( ! is_array( $existing ) ) {
			$existing = array();
		}

		$existing['registration'] = self::normalize_registration_settings( $registration );

		return $existing;
	}

	/**
	 * Normalize common boolean-like values.
	 *
	 * @param mixed $value Raw value to normalize.
	 */
	private static function to_bool( $value ): bool {
		if ( is_bool( $value ) ) {
			return $value;
		}

		if ( is_numeric( $value ) ) {
			return (bool) (int) $value;
		}

		if ( is_string( $value ) ) {
			$value = strtolower( trim( $value ) );

			return in_array( $value, array( '1', 'true', 'yes', 'on' ), true );
		}

		return false;
	}
}
