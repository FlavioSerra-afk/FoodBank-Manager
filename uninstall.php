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

    $opts = array(
        'fbm_options',
        'fbm_version',
        'fbm_permissions_defaults',
        'fbm_permissions_audit',
        'fbm_email_templates',
        'fbm_mail_failures',
        'fbm_throttle',
    );

    $cleanup = static function () use ( $opts, $wpdb ): void {
        foreach ( $opts as $opt ) {
            delete_option( $opt );
        }
        $blog = $GLOBALS['fbm_current_blog'] ?? 1;
        foreach ( array_keys( $GLOBALS['fbm_options'][$blog] ?? array() ) as $name ) {
            if ( strpos( $name, 'fbm_caps_migrated_' ) === 0 || strpos( $name, 'fbm_telemetry_' ) === 0 || strpos( $name, '_transient_fbm_' ) === 0 ) {
                delete_option( $name );
            }
        }
        foreach ( array_keys( $GLOBALS['fbm_transients'][$blog] ?? array() ) as $t ) {
            if ( strpos( $t, 'fbm_' ) === 0 ) {
                delete_transient( $t );
            }
        }
        if ( function_exists( 'wp_clear_scheduled_hook' ) ) {
            wp_clear_scheduled_hook( 'fbm_retention_hourly' );
            wp_clear_scheduled_hook( 'fbm_retention_tick' );
            wp_clear_scheduled_hook( 'fbm_jobs_tick' );
        }
    };

    if ( function_exists( 'is_multisite' ) && is_multisite() && function_exists( 'get_sites' ) && function_exists( 'switch_to_blog' ) && function_exists( 'restore_current_blog' ) && ( ! function_exists( 'is_plugin_active_for_network' ) || is_plugin_active_for_network( 'foodbank-manager/foodbank-manager.php' ) ) ) {
        foreach ( get_sites( array( 'number' => 0 ) ) as $site ) {
            switch_to_blog( (int) $site->blog_id );
            $cleanup();
        }
        restore_current_blog();
    } else {
        $cleanup();
    }

    foreach ( $opts as $opt ) {
        delete_site_option( $opt );
    }

    foreach ( array_keys( $GLOBALS['fbm_site_options'] ?? array() ) as $name ) {
        if ( strpos( $name, 'fbm_caps_migrated_' ) === 0 || strpos( $name, 'fbm_telemetry_' ) === 0 || strpos( $name, '_site_transient_fbm_' ) === 0 ) {
            delete_site_option( $name );
        }
    }
} catch ( \Throwable $e ) {
    // Silent by design.
}
return;
