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

        private const DB_VERSION            = '2024093002';
        private const INITIAL_TOKEN_VERSION = 'v1';
        private const LEGACY_TABLE_SUFFIXES = array(
                'fb_events',
                'fb_tickets',
                'fb_checkins',
        );

	/**
	 * Create or update required tables.
	 */
	public static function ensure_tables(): void {
		global $wpdb;

		if ( ! $wpdb instanceof wpdb ) {
			return;
		}

                if ( ! function_exists( 'dbDelta' ) ) {
                        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
                }

                self::cleanup_legacy_tables( $wpdb );

				$charset                = $wpdb->get_charset_collate();
				$members_table          = self::members_table_name( $wpdb );
				$attendance_table       = self::attendance_table_name( $wpdb );
				$attendance_audit_table = self::attendance_overrides_table_name( $wpdb );
				$tokens_table           = self::tokens_table_name( $wpdb );

				$initial_token_version = self::INITIAL_TOKEN_VERSION;
		$sql_members                   = 'CREATE TABLE `' . $members_table . '` (
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

				$sql_attendance_overrides = 'CREATE TABLE `' . $attendance_audit_table . '` (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                attendance_id BIGINT UNSIGNED NOT NULL,
                member_reference VARCHAR(191) NOT NULL,
                override_by BIGINT UNSIGNED NOT NULL,
                override_note TEXT NOT NULL,
                override_at DATETIME NOT NULL,
                created_at DATETIME NOT NULL,
                PRIMARY KEY  (id),
                KEY idx_attendance_id (attendance_id),
                KEY idx_member_reference (member_reference)
        ) ' . $charset . ';';

		$sql_tokens = 'CREATE TABLE `' . $tokens_table . '` (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		member_id BIGINT UNSIGNED NOT NULL,
		token_hash CHAR(64) NOT NULL,
		version VARCHAR(10) NOT NULL DEFAULT \' . $initial_token_version . \',
		issued_at DATETIME NOT NULL,
		revoked_at DATETIME NULL,
		meta LONGTEXT NULL,
		PRIMARY KEY  (id),
                UNIQUE KEY uq_member (member_id),
                UNIQUE KEY uq_token_hash (token_hash)
        ) ' . $charset . ';';

				dbDelta( $sql_members );
				dbDelta( $sql_attendance );
				dbDelta( $sql_attendance_overrides );
                dbDelta( $sql_tokens );

                update_option( 'fbm_db_version', self::DB_VERSION, false );
        }

        /**
         * Remove deprecated legacy tables left from the events era schema.
         *
         * @param wpdb $wpdb WordPress database abstraction.
         */
        private static function cleanup_legacy_tables( wpdb $wpdb ): void {
                foreach ( self::LEGACY_TABLE_SUFFIXES as $suffix ) {
                        $table = $wpdb->prefix . $suffix;

                        self::drop_table_if_exists( $wpdb, $table );
                }
        }

        /**
         * Drop a table if it still exists in the database.
         *
         * @param wpdb  $wpdb  WordPress database abstraction.
         * @param string $table Fully qualified table name.
         */
        private static function drop_table_if_exists( wpdb $wpdb, string $table ): void {
                if ( function_exists( 'maybe_drop_table' ) ) {
                        // maybe_drop_table() already guards the DROP statement for us.
                        maybe_drop_table( $table );

                        return;
                }

                if ( method_exists( $wpdb, 'query' ) ) {
                        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name is trusted internal metadata.
                        $wpdb->query( 'DROP TABLE IF EXISTS `' . $table . '`' );
                }
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
		 * Resolve the fully qualified attendance override audit table name.
		 *
		 * @param wpdb $wpdb WordPress database abstraction.
		 */
	public static function attendance_overrides_table_name( wpdb $wpdb ): string {
			return $wpdb->prefix . 'fbm_attendance_overrides';
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
