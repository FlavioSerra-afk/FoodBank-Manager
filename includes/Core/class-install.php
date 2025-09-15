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

	private const DB_VERSION = '2024091501';

	/**
	 * Create or update required tables.
	 */
	public static function ensure_tables(): void {
		global $wpdb;

		if ( ! $wpdb instanceof wpdb ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset = $wpdb->get_charset_collate();
		$table   = self::attendance_table_name( $wpdb );

		$sql = 'CREATE TABLE `' . $table . '` (
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

		dbDelta( $sql );

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
}
