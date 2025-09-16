<?php
/**
 * Uninstall cleanup.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);


if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

/*
 * Database tables are preserved by default so administrators can reinstall
 * without data loss. Define the FBM_ALLOW_DESTRUCTIVE_UNINSTALL constant or
 * return true from the fbm_allow_destructive_uninstall filter to drop tables
 * during uninstall.
 */
$allow_destructive = false;

if ( defined( 'FBM_ALLOW_DESTRUCTIVE_UNINSTALL' ) ) {
	$allow_destructive = (bool) FBM_ALLOW_DESTRUCTIVE_UNINSTALL;
}

if ( function_exists( 'apply_filters' ) ) {
	$allow_destructive = (bool) apply_filters( 'fbm_allow_destructive_uninstall', $allow_destructive );
}

if ( $allow_destructive && isset( $wpdb ) && $wpdb instanceof \wpdb ) {
	$table = $wpdb->prefix . 'fbm_attendance';

	if ( preg_match( '/^[A-Za-z0-9_]+$/', $table ) === 1 ) {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		maybe_drop_table( $table );
	}
}

delete_option( 'fbm_db_version' );
