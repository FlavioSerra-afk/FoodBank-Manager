<?php
/**
 * GDPR Personal Data Eraser.
 *
 * @package FoodBankManager\Privacy
 */

declare(strict_types=1);

namespace FBM\Privacy;

use wpdb;
use function sanitize_email;
use function wp_list_pluck;

/**
 * Erase FBM data for WordPress privacy eraser.
 */
final class Eraser {
	private const PER_PAGE = 50;

	/**
	 * Callback for WP eraser.
	 *
	 * @param string $email Email address.
	 * @param int    $page  Page number.
	 * @return array{items_removed:bool,items_retained:bool,messages:list<string>,done:bool}
	 */
	public static function erase( string $email, int $page ): array {
		return self::run( $email, $page, false );
	}

	/**
	 * Execute erasure.
	 *
	 * @param string $email Email.
	 * @param int    $page  Page number.
	 * @param bool   $dry   Whether to simulate.
	 * @return array{items_removed:bool,items_retained:bool,messages:list<string>,done:bool}
	 */
	public static function run( string $email, int $page, bool $dry = false ): array {
		global $wpdb;
		$email  = sanitize_email( $email );
		$page   = max( 1, (int) $page );
		$limit  = self::PER_PAGE;
		$offset = ( $page - 1 ) * $limit;

		$tables = array(
			$wpdb->prefix . 'fb_submissions',
			$wpdb->prefix . 'fb_attendance',
			$wpdb->prefix . 'fb_tickets',
			$wpdb->prefix . 'fb_emails',
		);

		$removed  = false;
		$retained = false;
		$done     = true;

		foreach ( $tables as $table ) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name is constant.
			$sql = "SELECT id FROM {$table} WHERE email = %s LIMIT %d OFFSET %d";
			$ids = (array) $wpdb->get_col( $wpdb->prepare( $sql, $email, $limit, $offset ) );
			if ( $ids ) {
				if ( $dry ) {
					$retained = true;
				} else {
					$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name is constant.
					$del_sql = "DELETE FROM {$table} WHERE id IN ($placeholders)";
					$wpdb->query( $wpdb->prepare( $del_sql, $ids ) );
					$removed = true;
				}
				if ( count( $ids ) === $limit ) {
					$done = false;
				}
			}
		}

		return array(
			'items_removed'  => $removed,
			'items_retained' => $retained,
			'messages'       => array(),
			'done'           => $done,
		);
	}
}
