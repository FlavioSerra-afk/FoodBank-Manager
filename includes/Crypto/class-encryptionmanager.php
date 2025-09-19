<?php
/**
 * Encryption adapter registry.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Crypto;

use FoodBankManager\Crypto\Adapters\MailFailLogAdapter;
use FoodBankManager\Crypto\Adapters\MembersPiiAdapter;
use wpdb;

/**
 * Provides discovery helpers for available encryption adapters.
 */
final class EncryptionManager {
		/**
		 * Retrieve all registered adapters keyed by identifier.
		 *
		 * @return array<string,EncryptionAdapter>
		 */
	public static function adapters(): array {
			global $wpdb;

			$adapters = array();

		if ( isset( $wpdb ) && $wpdb instanceof wpdb ) {
				$adapters['members_pii'] = new MembersPiiAdapter( $wpdb );
		}

			$adapters['mail_fail_log'] = new MailFailLogAdapter();

			return $adapters;
	}

	/**
		Locate an adapter by identifier.

		@param string $identifier Adapter identifier.

		@return EncryptionAdapter|null
	 */
	public static function get( string $identifier ): ?EncryptionAdapter {
			$adapters = self::adapters();

		if ( isset( $adapters[ $identifier ] ) && $adapters[ $identifier ] instanceof EncryptionAdapter ) {
				return $adapters[ $identifier ];
		}

			return null;
	}
}
