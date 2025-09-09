<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Db;

class Migrations {

        private const OPTION_KEY = 'fbm_db_version';
        private const VERSION    = '2024091504';

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
            is_void TINYINT(1) NOT NULL DEFAULT 0,
            void_reason VARCHAR(255) NULL,
            void_by_user_id BIGINT UNSIGNED NULL,
            void_at DATETIME NULL,
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

                $sql[] = "CREATE TABLE {$wpdb->prefix}fb_attendance_notes (
            id BIGINT(20) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
            attendance_id BIGINT(20) UNSIGNED NOT NULL,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            note_text TEXT NOT NULL,
            created_at DATETIME NOT NULL,
            KEY idx_attendance (attendance_id),
            KEY idx_created (created_at)
        ) $charset_collate;";

                $sql[] = "CREATE TABLE {$wpdb->prefix}fb_audit_log (
            id BIGINT(20) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
            actor_user_id BIGINT(20) UNSIGNED NOT NULL,
            action VARCHAR(64) NOT NULL,
            target_type VARCHAR(32) NOT NULL,
            target_id BIGINT(20) UNSIGNED NOT NULL,
            details_json LONGTEXT NULL,
            created_at DATETIME NOT NULL,
            KEY idx_target (target_type, target_id),
            KEY idx_created (created_at)
        ) $charset_collate;";
        $sql[] = "CREATE TABLE {$wpdb->prefix}fb_events (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            starts_at DATETIME NOT NULL,
            ends_at DATETIME NOT NULL,
            location VARCHAR(255) NULL,
            capacity INT UNSIGNED NULL,
            notes TEXT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            KEY idx_status (status),
            KEY idx_start (starts_at)
        ) $charset_collate;";
        $sql[] = "CREATE TABLE {$wpdb->prefix}fb_checkins (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            event_id BIGINT UNSIGNED NOT NULL,
            recipient VARCHAR(255) NULL,
            token_hash VARBINARY(32) NULL,
            method VARCHAR(16) NOT NULL,
            note TEXT NULL,
            `by` BIGINT UNSIGNED NULL,
            verified_at DATETIME NOT NULL,
            created_at DATETIME NOT NULL,
            UNIQUE KEY uq_event_token (event_id, token_hash),
            KEY idx_event (event_id),
            KEY idx_verified (verified_at)
        ) $charset_collate;";


                $sql[] = "CREATE TABLE {$wpdb->prefix}fb_tickets (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            event_id BIGINT UNSIGNED NOT NULL,
            recipient VARCHAR(255) NOT NULL,
            token_hash VARBINARY(32) NOT NULL,
            exp DATETIME NOT NULL,
            nonce VARBINARY(16) NOT NULL,
            status VARCHAR(16) NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            KEY idx_event_status (event_id,status)
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
