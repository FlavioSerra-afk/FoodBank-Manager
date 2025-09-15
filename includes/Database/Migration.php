<?php // phpcs:ignoreFile
/**
 * Database migration for core tables.
 *
 * @package FBM\Database
 */

declare(strict_types=1);

namespace FBM\Database;

use wpdb;
use function dbDelta;
use function update_option;

/**
 * Ensure required tables exist.
 */
final class Migration {
	/**
	 * Run the migration.
	 */
	public static function run(): void {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$charset    = $wpdb->get_charset_collate();
		$members    = $wpdb->prefix . 'fbm_members';
		$tokens     = $wpdb->prefix . 'fbm_tokens';
		$attendance = $wpdb->prefix . 'fbm_attendance';

		$sql = "CREATE TABLE $members (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            KEY status (status)
        ) $charset;

        CREATE TABLE $tokens (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            member_id BIGINT UNSIGNED NOT NULL,
            token_hash VARCHAR(64) NOT NULL,
            issued_at DATETIME NOT NULL,
            revoked_at DATETIME NULL,
            version SMALLINT UNSIGNED NOT NULL DEFAULT 1,
            PRIMARY KEY  (id),
            UNIQUE KEY token_hash (token_hash),
            KEY member_id (member_id)
        ) $charset;

        CREATE TABLE $attendance (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            member_id BIGINT UNSIGNED NOT NULL,
            collected_at DATETIME NOT NULL,
            collected_date DATE NOT NULL,
            method VARCHAR(10) NOT NULL,
            recorded_by BIGINT UNSIGNED NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY member_day (member_id, collected_date),
            KEY member_collected (member_id, collected_at)
        ) $charset;";

		dbDelta( $sql );
		update_option( 'fbm_db_version', '1', false ); // simple version flag
	}
}
