<?php
/**
 * Capability registration.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Auth;

use WP_Role;
use function get_role;

/**
 * Manages FoodBank Manager capabilities.
 */
final class Capabilities {

	/**
	 * Canonical capability list.
	 *
	 * @var string[]
	 */
	private const CAPABILITIES = array(
		'fbm_manage',
		'fbm_edit',
		'fbm_view',
		'fbm_export',
		'fbm_diagnostics',
		'fbm_checkin',
	);

	/**
	 * Mapping of roles to capabilities granted on activation.
	 *
	 * @var array<string, string[]>
	 */
	private const ROLE_MAP = array(
		'administrator' => self::CAPABILITIES,
	);

		/**
		 * Tracks whether ensure() has been executed.
		 *
		 * @var bool
		 */
	private static bool $ensured = false;

	/**
	 * Ensure mapped roles receive capabilities.
	 */
	public static function ensure(): void {
		if ( self::$ensured ) {
			return;
		}

		foreach ( self::ROLE_MAP as $role_name => $capabilities ) {
			$role = get_role( $role_name );
			if ( ! $role instanceof WP_Role ) {
				continue;
			}

			foreach ( $capabilities as $capability ) {
				$role->add_cap( $capability );
			}
		}

		self::$ensured = true;
	}

	/**
	 * Return the canonical capability list.
	 *
	 * @return string[]
	 */
	public static function all(): array {
		return self::CAPABILITIES;
	}
}
