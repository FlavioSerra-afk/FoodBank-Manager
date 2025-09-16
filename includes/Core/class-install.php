<?php
/**
 * Installation helpers.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Core;

use wpdb;
use function dbDelta;
use function update_option;

/**
 * Handles database setup.
 */
final class Install {

	private const DB_VERSION            = '2024093001';
	private const INITIAL_TOKEN_VERSION = 'v1';

	/**
	 * Create or update required tables.
	 */
	public static function ensure_tables(): void {
		global $wpdb;

		if ( ! $wpdb instanceof wpdb ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

				$charset          = $wpdb->get_charset_collate();
				$members_table    = self::members_table_name( $wpdb );
				$attendance_table = self::attendance_table_name( $wpdb );
				$tokens_table     = self::tokens_table_name( $wpdb );

		$sql_members = 'CREATE TABLE `' . $members_table . '` (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		member_reference VARCHAR(64) NOT NULL,
		first_name VARCHAR(191) NOT NULL,
		last_initial CHAR(1) NOT NULL,
		email VARCHAR(191) NOT NULL,
		household_size SMALLINT UNSIGNED NOT NULL DEFAULT 1,
		status VARCHAR(20) NOT NULL,
		created_at DATETIME NOT NULL,
		updated_at DATETIME NOT NULL,
		activated_at DATETIME NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY uq_member_reference (member_reference),
		KEY idx_status (status),
		KEY idx_email (email)
	) ' . $charset . ';';

				$sql_attendance = 'CREATE TABLE `' . $attendance_table . '` (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                member_reference VARCHAR(191) NOT NULL,
                collected_at DATETIME NOT NULL,
                collected_date DATE NOT NULL,
                method VARCHAR(20) NOT NULL,
                note TEXT NULL,
                recorded_by BIGINT UNSIGNED NULL,
                created_at DATETIME NOT NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY uq_member_day (member_reference, collected_date),
                KEY idx_collected_at (collected_at)
        ) ' . $charset . ';';

		$sql_tokens = 'CREATE TABLE `' . $tokens_table . '` (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		member_id BIGINT UNSIGNED NOT NULL,
		token_hash CHAR(64) NOT NULL,
		version VARCHAR(10) NOT NULL DEFAULT \' . self::INITIAL_TOKEN_VERSION . \',
		issued_at DATETIME NOT NULL,
		revoked_at DATETIME NULL,
		PRIMARY KEY  (id),
                UNIQUE KEY uq_member (member_id),
                UNIQUE KEY uq_token_hash (token_hash)
        ) ' . $charset . ';';

				dbDelta( $sql_members );
				dbDelta( $sql_attendance );
				dbDelta( $sql_tokens );

				update_option( 'fbm_db_version', self::DB_VERSION, false );
	}

	/**
	 * Resolve the fully qualified attendance table name.
	 *
	 * @param wpdb $wpdb WordPress database abstraction.
	 */
	public static function attendance_table_name( wpdb $wpdb ): string {
		return $wpdb->prefix . 'fbm_attendance';
	}

	/**
	 * Resolve the fully qualified members table name.
	 *
	 * @param wpdb $wpdb WordPress database abstraction.
	 */
	public static function members_table_name( wpdb $wpdb ): string {
			return $wpdb->prefix . 'fbm_members';
	}

		/**
		 * Resolve the fully qualified tokens table name.
		 *
		 * @param wpdb $wpdb WordPress database abstraction.
		 */
	public static function tokens_table_name( wpdb $wpdb ): string {
			return $wpdb->prefix . 'fbm_tokens';
	}
}
