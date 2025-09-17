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

/*
 * Remove configuration options regardless of the uninstall mode so the next
 * installation starts from a clean slate. Scheduling, theming, and migration
 * markers are considered non-destructive because they do not contain member
 * data.
 */
$option_names = array(
        'fbm_db_version',
        'fbm_theme',
        'fbm_settings',
        'fbm_db_migration_summary',
        'fbm_schedule_window',
        'fbm_schedule_window_overrides',
);

if ( function_exists( 'delete_option' ) ) {
        foreach ( $option_names as $option_name ) {
                delete_option( $option_name );
        }
}

/*
 * Database tables are preserved by default so administrators can reinstall
 * without data loss. Define the FBM_ALLOW_DESTRUCTIVE_UNINSTALL constant or
 * return true from the fbm_allow_destructive_uninstall filter to drop tables
 * and clear stored secrets during uninstall.
 */
$allow_destructive = false;

if ( defined( 'FBM_ALLOW_DESTRUCTIVE_UNINSTALL' ) ) {
        $allow_destructive = (bool) FBM_ALLOW_DESTRUCTIVE_UNINSTALL;
}

if ( function_exists( 'apply_filters' ) ) {
        $allow_destructive = (bool) apply_filters( 'fbm_allow_destructive_uninstall', $allow_destructive );
}

if ( ! $allow_destructive ) {
        return;
}

global $wpdb;

if ( ! isset( $wpdb ) || ! $wpdb instanceof \wpdb ) {
        return;
}

$destructive_options = array(
        'fbm_token_signing_key',
        'fbm_token_storage_key',
);

if ( function_exists( 'delete_option' ) ) {
        foreach ( $destructive_options as $option_name ) {
                delete_option( $option_name );
        }
}

$tables = array(
        $wpdb->prefix . 'fbm_attendance_overrides',
        $wpdb->prefix . 'fbm_attendance',
        $wpdb->prefix . 'fbm_tokens',
        $wpdb->prefix . 'fbm_members',
);

$valid_tables = array();

foreach ( $tables as $table ) {
        if ( preg_match( '/^[A-Za-z0-9_]+$/', $table ) === 1 ) {
                $valid_tables[] = $table;
        }
}

if ( empty( $valid_tables ) ) {
        return;
}

if ( defined( 'ABSPATH' ) ) {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
}

if ( function_exists( 'maybe_drop_table' ) ) {
        foreach ( $valid_tables as $table ) {
                maybe_drop_table( $table );
        }
}
