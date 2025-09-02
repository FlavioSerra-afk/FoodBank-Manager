<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Db;

class Migrations {

	private const OPTION_KEY = 'fbm_db_version';
	private const VERSION    = '2024090101';

	public function maybe_migrate(): void {
		$current = get_option( self::OPTION_KEY );
		if ( $current === self::VERSION ) {
			return;
		}
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = array();

		$sql[] = "CREATE TABLE {$wpdb->prefix}fb_applications (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            form_id BIGINT UNSIGNED NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'new',
            data_json LONGTEXT NOT NULL,
            pii_encrypted_blob LONGBLOB NULL,
            indexes_json JSON NULL,
            attachment_id BIGINT UNSIGNED NULL,
            consent_text_hash CHAR(64) NULL,
            consent_timestamp DATETIME NOT NULL,
            consent_ip VARBINARY(16) NULL,
            newsletter_opt_in TINYINT(1) NOT NULL DEFAULT 0,
            last_attended_at DATETIME NULL,
            total_attendances INT UNSIGNED NOT NULL DEFAULT 0,
            no_show_count INT UNSIGNED NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            KEY idx_form_created (form_id, created_at),
            KEY idx_status (status)
        ) $charset_collate;";

		$sql[] = "CREATE TABLE {$wpdb->prefix}fb_attendance (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            form_id BIGINT UNSIGNED NOT NULL,
            application_id BIGINT UNSIGNED NOT NULL,
            event_id BIGINT UNSIGNED NULL,
            attendance_at DATETIME NOT NULL,
            status VARCHAR(20) NOT NULL,
            type VARCHAR(20) NOT NULL DEFAULT 'food_parcel',
            method VARCHAR(20) NOT NULL,
            recorded_by_user_id BIGINT UNSIGNED NOT NULL,
            notes TEXT NULL,
            token_hash CHAR(64) NULL,
            source_ip VARBINARY(16) NULL,
            device VARCHAR(64) NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            UNIQUE KEY uq_token (token_hash),
            KEY idx_app_time (application_id, attendance_at),
            KEY idx_event_time (event_id, attendance_at),
            KEY idx_status (status)
        ) $charset_collate;";

		$sql[] = "CREATE TABLE {$wpdb->prefix}fb_events (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            form_id BIGINT UNSIGNED NOT NULL,
            title VARCHAR(190) NOT NULL,
            type VARCHAR(64) NULL,
            starts_at DATETIME NOT NULL,
            ends_at DATETIME NULL,
            location VARCHAR(190) NULL,
            capacity INT UNSIGNED NULL,
            recurrence TEXT NULL,
            notes TEXT NULL,
            created_by BIGINT UNSIGNED NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            KEY idx_form_start (form_id, starts_at)
        ) $charset_collate;";

		$sql[] = "CREATE TABLE {$wpdb->prefix}fb_mail_log (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            application_id BIGINT UNSIGNED NULL,
            to_email VARCHAR(254) NOT NULL,
            subject VARCHAR(255) NOT NULL,
            headers MEDIUMTEXT NULL,
            body_hash CHAR(64) NULL,
            status VARCHAR(20) NOT NULL,
            provider_msg TEXT NULL,
            timestamp DATETIME NOT NULL,
            KEY idx_status_time (status, timestamp),
            KEY idx_app (application_id)
        ) $charset_collate;";

		$sql[] = "CREATE TABLE {$wpdb->prefix}fb_files (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            application_id BIGINT UNSIGNED NOT NULL,
            stored_path VARCHAR(255) NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            mime VARCHAR(127) NOT NULL,
            size_bytes BIGINT UNSIGNED NOT NULL,
            sha256 CHAR(64) NOT NULL,
            created_at DATETIME NOT NULL,
            KEY idx_app (application_id)
        ) $charset_collate;";

		foreach ( $sql as $statement ) {
			dbDelta( $statement );
		}

		update_option( self::OPTION_KEY, self::VERSION );
	}
}
