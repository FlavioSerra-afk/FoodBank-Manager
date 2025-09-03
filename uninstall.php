<?php
/**
 * FoodBank Manager – Uninstall.
 * Runs when plugin is deleted from WP Admin.
 * Must be standalone (no autoloader/classes), silent, and defensive.
 *
 * @package FoodBankManager
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) || ! defined( 'ABSPATH' ) ) {
	return;
}

try {
	global $wpdb;

	// 1) Drop custom tables (IF EXISTS) – keep this list in sync with Docs/DB_SCHEMA.md.
	$tables = array(
		$wpdb->prefix . 'fb_applications',
		$wpdb->prefix . 'fb_attendance',
		$wpdb->prefix . 'fb_attendance_notes',
		$wpdb->prefix . 'fb_audit_log',
		$wpdb->prefix . 'fb_events',
		$wpdb->prefix . 'fb_files',
		$wpdb->prefix . 'fb_mail_log',
	);

	foreach ( $tables as $t ) {
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- uninstall-time schema cleanup.
		$wpdb->query( "DROP TABLE IF EXISTS `{$t}`" ); // No prepare: identifiers only, controlled by us.
	}

	// 2) Delete options (namespaced).
	$opts = array(
		'fbm_options',
		'fbm_version',
	);
	foreach ( $opts as $opt ) {
		delete_option( $opt );
		delete_site_option( $opt ); // In case of multisite.
	}

	// 3) Clear transients.
	$like = $wpdb->esc_like( '_transient_fbm_' ) . '%';
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$wpdb->query( "DELETE FROM `{$wpdb->options}` WHERE option_name LIKE '{$like}'" );

	// 4) Unschedule cron hooks if any (keep names in sync with code).
	if ( function_exists( 'wp_clear_scheduled_hook' ) ) {
		wp_clear_scheduled_hook( 'fbm_cron_cleanup' );
		wp_clear_scheduled_hook( 'fbm_cron_email_retry' );
	}
} catch ( Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement -- Silence on uninstall.
}

// 5) DO NOT: echo, redirect, load plugin classes, or modify roles/caps (WP core handles role removal by file deletion).
// Just end silently.
return;
