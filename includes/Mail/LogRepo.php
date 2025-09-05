<?php
/**
 * Mail log repository.
 *
 * @package FBM
 */

declare(strict_types=1);

namespace FBM\Mail;

use wpdb;
use function absint;
use function sanitize_text_field;

/**
 * Access mail log entries.
 */
class LogRepo {
	/**
	 * Find log entries by application ID.
	 *
	 * @param int $application_id Application ID.
	 * @return array<int,array>
	 */
	public static function find_by_application_id( int $application_id ): array {
		global $wpdb;
		$application_id = absint( $application_id );
			$sql        = 'SELECT id,to_email,subject,headers,body_hash,status,provider_msg,timestamp'
			. " FROM {$wpdb->prefix}fb_mail_log WHERE application_id = %d ORDER BY timestamp ASC";
			$query      = call_user_func_array( array( $wpdb, 'prepare' ), array( $sql, $application_id ) );
			$rows       = call_user_func( array( $wpdb, 'get_results' ), $query, 'ARRAY_A' );
			$out        = array();
		foreach ( $rows ? $rows : array() as $row ) {
			$out[] = array(
				'id'           => (int) ( $row['id'] ?? 0 ),
				'to_email'     => sanitize_text_field( (string) ( $row['to_email'] ?? '' ) ),
				'subject'      => sanitize_text_field( (string) ( $row['subject'] ?? '' ) ),
				'headers'      => sanitize_text_field( (string) ( $row['headers'] ?? '' ) ),
				'body_hash'    => sanitize_text_field( (string) ( $row['body_hash'] ?? '' ) ),
				'status'       => sanitize_text_field( (string) ( $row['status'] ?? '' ) ),
				'provider_msg' => sanitize_text_field( (string) ( $row['provider_msg'] ?? '' ) ),
				'timestamp'    => sanitize_text_field( (string) ( $row['timestamp'] ?? '' ) ),
			);
		}
		return $out;
	}

		/**
		 * Anonymise mail log entries.
		 *
		 * @param array<int> $ids IDs to anonymise.
		 * @return int Rows affected.
		 */
	public static function anonymise_batch( array $ids ): int {
			global $wpdb;
			$ids = array_values( array_filter( array_map( 'absint', $ids ) ) );
		if ( empty( $ids ) ) {
				return 0;
		}
				return (int) $wpdb->query(
					$wpdb->prepare(
						'UPDATE ' . $wpdb->prefix . "fb_mail_log SET to_email='',subject='',headers='',provider_msg='' WHERE id IN ("
								. implode( ',', array_fill( 0, count( $ids ), '%d' ) ) . ')',
						$ids
					)
				);
	}
}
