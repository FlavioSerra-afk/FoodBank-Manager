<?php
/**
 * Permissions audit helper.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

use function get_current_user_id;
use function get_option;
use function sanitize_text_field;
use function update_option;

final class PermissionsAudit {
	private const OPTION = 'fbm_permissions_audit';

	/**
	 * Record an audit entry.
	 *
	 * @param string               $message Human readable message.
	 * @param array<string,mixed>  $context Extra context to store.
	 * @return void
	 */
	public static function add( string $message, array $context = array() ): void {
		$entry = array(
			'time'    => time(),
			'user_id' => get_current_user_id(),
			'message' => sanitize_text_field( $message ),
			'ctx'     => $context,
		);
		$log   = get_option( self::OPTION, array() );
		if ( ! is_array( $log ) ) {
			$log = array();
		}
		$log[] = $entry;
		if ( count( $log ) > 100 ) {
			$log = array_slice( $log, -100 );
		}
		update_option( self::OPTION, $log, false ); // @phpstan-ignore-line
	}

	/**
	 * Retrieve audit log.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public static function all(): array {
		$log = get_option( self::OPTION, array() );
		return is_array( $log ) ? $log : array();
	}
}
