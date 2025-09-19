<?php
/**
 * Encryption settings helper.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Crypto;

use function array_merge;
use function get_option;
use function is_array;
use function update_option;

/**
 * Provides typed accessors for the encryption settings option.
 */
final class EncryptionSettings {
	private const OPTION_KEY               = 'fbm_encryption_settings';
	private const FIELD_ENCRYPT_NEW_WRITES = 'encrypt_new_writes';

	/**
	 * Disallow instantiation.
	 */
	private function __construct() {}

	/**
	 * Determine whether new writes should be encrypted automatically.
	 *
	 * @return bool
	 */
	public static function encrypt_new_writes_enabled(): bool {
		$settings = self::get_settings();

		return ! empty( $settings[ self::FIELD_ENCRYPT_NEW_WRITES ] );
	}

	/**
	 * Persist the encrypt-new-writes preference.
	 *
	 * @param bool $enabled Whether to encrypt new writes.
	 */
	public static function update_encrypt_new_writes( bool $enabled ): void {
		$settings                                   = self::get_settings();
		$settings[ self::FIELD_ENCRYPT_NEW_WRITES ] = $enabled;

		update_option( self::OPTION_KEY, $settings, false );
	}

	/**
	 * Seed defaults during a fresh activation.
	 */
	public static function bootstrap_on_activation(): void {
		$existing = get_option( self::OPTION_KEY, null );

		if ( null === $existing ) {
			update_option(
				self::OPTION_KEY,
				array( self::FIELD_ENCRYPT_NEW_WRITES => true ),
				false
			);
		}
	}

	/**
	 * Retrieve the current settings payload merged with defaults.
	 *
	 * @return array<string,mixed>
	 */
	public static function get_settings(): array {
		$raw = get_option( self::OPTION_KEY, array() );

		if ( ! is_array( $raw ) ) {
			$raw = array();
		}

		return array_merge(
			array( self::FIELD_ENCRYPT_NEW_WRITES => false ),
			$raw
		);
	}
}
