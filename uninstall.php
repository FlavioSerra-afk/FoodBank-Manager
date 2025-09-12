<?php
/**
 * FoodBank Manager – Uninstall cleanup.
 *
 * Deletes plugin options, transients, and migration/telemetry flags.
 * Content such as custom tables or posts is left untouched.
 *
 * @package FoodBankManager
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) || ! defined( 'ABSPATH' ) ) {
    return;
}

try {
    global $wpdb;

    // Delete known options (site + network).
    $opts = array(
        'fbm_options',
        'fbm_version',
        'fbm_permissions_defaults',
        'fbm_permissions_audit',
        'fbm_email_templates',
        'fbm_mail_failures',
        'fbm_throttle',
    );
    foreach ( $opts as $opt ) {
        delete_option( $opt );
        delete_site_option( $opt );
    }

    // Remove migration/telemetry flags.
    $like_flag = $wpdb->esc_like( 'fbm_caps_migrated_' ) . '%';
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- targeted uninstall cleanup.
    $wpdb->query( "DELETE FROM `{$wpdb->options}` WHERE option_name LIKE '{$like_flag}'" );
    if ( function_exists( 'is_multisite' ) && is_multisite() && isset( $wpdb->sitemeta ) ) {
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- targeted uninstall cleanup.
        $wpdb->query( "DELETE FROM `{$wpdb->sitemeta}` WHERE meta_key LIKE '{$like_flag}'" );
    }
    $like_tel = $wpdb->esc_like( 'fbm_telemetry_' ) . '%';
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- targeted uninstall cleanup.
    $wpdb->query( "DELETE FROM `{$wpdb->options}` WHERE option_name LIKE '{$like_tel}'" );
    if ( function_exists( 'is_multisite' ) && is_multisite() && isset( $wpdb->sitemeta ) ) {
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- targeted uninstall cleanup.
        $wpdb->query( "DELETE FROM `{$wpdb->sitemeta}` WHERE meta_key LIKE '{$like_tel}'" );
    }

    // Clear transients (site + network).
    $like = $wpdb->esc_like( '_transient_fbm_' ) . '%';
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- targeted uninstall cleanup.
    $wpdb->query( "DELETE FROM `{$wpdb->options}` WHERE option_name LIKE '{$like}'" );
    if ( function_exists( 'is_multisite' ) && is_multisite() && isset( $wpdb->sitemeta ) ) {
        $like_site = $wpdb->esc_like( '_site_transient_fbm_' ) . '%';
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- targeted uninstall cleanup.
        $wpdb->query( "DELETE FROM `{$wpdb->sitemeta}` WHERE meta_key LIKE '{$like_site}'" );
    }

    // Unschedule cron hooks we own.
    if ( function_exists( 'wp_clear_scheduled_hook' ) ) {
        wp_clear_scheduled_hook( 'fbm_retention_hourly' );
        wp_clear_scheduled_hook( 'fbm_retention_tick' );
        wp_clear_scheduled_hook( 'fbm_jobs_tick' );
        wp_clear_scheduled_hook( 'fbm_cron_cleanup' );
        wp_clear_scheduled_hook( 'fbm_cron_email_retry' );
    }
} catch ( \Throwable $e ) {
    // Silent by design.
}
return;
