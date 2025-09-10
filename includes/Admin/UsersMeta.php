<?php // phpcs:ignoreFile
/**
 * User meta helpers.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Admin;

/**
 * Helpers for user meta storage.
 */
final class UsersMeta {
	private const KEY_DB_COLUMNS = 'fbm_db_columns';
	private const KEY_USER_CAPS  = 'fbm_user_caps';

		/**
		 * Allowed DB column IDs.
		 *
		 * @return array<int,string>
		 */
	public static function allowed_db_columns(): array {
        $cols = \FoodBankManager\Database\Columns::for_admin_list( false );
        return array_keys( $cols );
	}

		/**
		 * Labels for DB columns.
		 *
		 * @return array<string,string>
		 */
	public static function db_column_labels(): array {
        $defs = \FoodBankManager\Database\Columns::for_admin_list( false );
        $out  = array();
        foreach ( $defs as $key => $def ) {
                $out[ $key ] = (string) ( $def['label'] ?? $key );
        }
        return $out;
	}

		/**
		 * Get selected DB columns for a user.
		 *
		 * @param int $user_id User ID.
		 * @return array<int,string>
		 */
	public static function get_db_columns( int $user_id ): array {
			$raw = get_user_meta( $user_id, self::KEY_DB_COLUMNS, true );
		if ( ! is_array( $raw ) ) {
				return self::allowed_db_columns();
		}
			$allowed = self::allowed_db_columns();
			$out     = array();
		foreach ( $raw as $col ) {
				$col = sanitize_key( (string) $col );
			if ( in_array( $col, $allowed, true ) ) {
					$out[] = $col;
			}
		}
		if ( empty( $out ) ) {
				return self::allowed_db_columns();
		}
			return $out;
	}

		/**
		 * Persist selected DB columns for a user.
		 *
		 * @param int               $user_id User ID.
		 * @param array<int,string> $columns Column IDs.
		 * @return bool
		 */
	public static function set_db_columns( int $user_id, array $columns ): bool {
		$allowed = self::allowed_db_columns();
		$clean   = array();
		foreach ( $columns as $col ) {
				$col = sanitize_key( (string) $col );
			if ( in_array( $col, $allowed, true ) ) {
				$clean[] = $col;
			}
		}
		if ( empty( $clean ) ) {
				$clean = $allowed;
		}
			return update_user_meta( $user_id, self::KEY_DB_COLUMNS, $clean );
	}

		/**
		 * Get per-user capability overrides.
		 *
		 * @param int $user_id User ID.
		 * @return array<string,bool>
		 */
	public static function get_user_caps( int $user_id ): array {
			$raw = get_user_meta( $user_id, self::KEY_USER_CAPS, true );
			return is_array( $raw ) ? $raw : array();
	}

		/**
		 * Persist per-user capability overrides.
		 *
		 * @param int               $user_id User ID.
		 * @param array<int,string> $caps    Capabilities to grant.
		 * @return bool
		 */
	public static function set_user_caps( int $user_id, array $caps ): bool {
            $known = \FBM\Auth\Capabilities::all();
			$clean = array();
		foreach ( $caps as $cap ) {
				$cap = sanitize_key( (string) $cap );
			if ( in_array( $cap, $known, true ) ) {
				$clean[ $cap ] = true;
			}
		}
		if ( empty( $clean ) ) {
				return delete_user_meta( $user_id, self::KEY_USER_CAPS );
		}
			return update_user_meta( $user_id, self::KEY_USER_CAPS, $clean );
	}
}
