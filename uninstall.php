<?php
/**
 * Uninstall cleanup.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

use wpdb;
use function maybe_drop_table;

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

if ( isset( $wpdb ) && $wpdb instanceof wpdb ) {
	$table = $wpdb->prefix . 'fbm_attendance';

	if ( preg_match( '/^[A-Za-z0-9_]+$/', $table ) === 1 ) {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		maybe_drop_table( $table );
	}
}

delete_option( 'fbm_db_version' );
