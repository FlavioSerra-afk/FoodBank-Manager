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
use function sanitize_email;
use function wp_json_encode;
use function json_decode;
use function is_array;
use function time;

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
			$rows           = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT id,to_email,subject,headers,body_hash,status,provider_msg,timestamp FROM ' . $wpdb->prefix
							. 'fb_mail_log WHERE application_id = %d ORDER BY timestamp ASC',
					$application_id
				),
				'ARRAY_A'
			);
			$out            = array();
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
		 * List recent failed mails.
		 *
		 * @param int $limit Max rows.
		 * @return array<int,array{id:int,to:string,subject:string,provider_msg:string,timestamp:string}>
		 */
	public static function recent_failures( int $limit = 20 ): array {
			global $wpdb;
			$limit = absint( $limit );
			$sql   = 'SELECT id,to_email,subject,provider_msg,timestamp FROM ' . $wpdb->prefix . 'fb_mail_log WHERE status = %s ORDER BY id DESC LIMIT %d';
			$rows  = $wpdb->get_results(
				$wpdb->prepare( $sql, 'failed', $limit ),
				ARRAY_A
			);
			$out   = array();
		foreach ( $rows ? $rows : array() as $row ) {
				$out[] = array(
					'id'           => (int) ( $row['id'] ?? 0 ),
					'to'           => sanitize_text_field( (string) ( $row['to_email'] ?? '' ) ),
					'subject'      => sanitize_text_field( (string) ( $row['subject'] ?? '' ) ),
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

		/**
		 * Insert a log entry.
		 *
		 * @param int    $application_id Application ID.
		 * @param string $to_email       Recipient email.
		 * @param string $subject        Subject line.
		 * @param string $body_hash      Body hash.
		 * @param string $status         Status.
		 * @param string $provider_msg   Provider message.
		 * @return bool
		 */
	public static function insert(
		int $application_id,
		string $to_email,
		string $subject,
		string $body_hash,
		string $status,
		string $provider_msg = ''
	): bool {
		global $wpdb;
		$to_email     = sanitize_email( $to_email );
		$subject      = sanitize_text_field( $subject );
		$body_hash    = sanitize_text_field( $body_hash );
		$status       = sanitize_text_field( $status );
		$provider_msg = sanitize_text_field( $provider_msg );

		return (bool) $wpdb->insert(
			$wpdb->prefix . 'fb_mail_log',
			array(
				'application_id' => $application_id,
				'to_email'       => $to_email,
				'subject'        => $subject,
				'headers'        => '',
				'body_hash'      => $body_hash,
				'status'         => $status,
				'provider_msg'   => $provider_msg,
				'timestamp'      => gmdate( 'Y-m-d H:i:s' ),
			),
			array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);
	}

		/**
		 * Get a log entry by ID.
		 *
		 * @param int $id Log ID.
		 * @return array<string,mixed>|null
		 */
	public static function get_by_id( int $id ): ?array {
		global $wpdb;
		$id  = absint( $id );
		$row = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT id,to_email,subject,headers,provider_msg FROM ' . $wpdb->prefix . 'fb_mail_log WHERE id = %d',
				$id
			),
			ARRAY_A
		);
		if ( ! $row ) {
				return null;
		}
			$out  = array(
				'id'       => (int) ( $row['id'] ?? 0 ),
				'to_email' => sanitize_text_field( (string) ( $row['to_email'] ?? '' ) ),
				'subject'  => sanitize_text_field( (string) ( $row['subject'] ?? '' ) ),
				'headers'  => sanitize_text_field( (string) ( $row['headers'] ?? '' ) ),
			);
			$meta = json_decode( (string) ( $row['provider_msg'] ?? '' ), true );
			if ( is_array( $meta ) ) {
					$out['body_vars'] = $meta;
			}
			return $out;
	}

		/**
		 * Audit a resend attempt.
		 *
		 * @param int    $id     Original log ID.
		 * @param string $status Result status.
		 * @param int    $actor  User ID.
		 * @param string $msg    Provider message.
		 * @return bool
		 */
	public static function audit_resend( int $id, string $status, int $actor, string $msg ): bool {
			global $wpdb;
			$id       = absint( $id );
			$status   = sanitize_text_field( $status );
			$actor    = absint( $actor );
			$msg      = sanitize_text_field( $msg );
			$provider = wp_json_encode(
				array(
					'original_id' => $id,
					'by'          => $actor,
					'message'     => $msg,
				)
			);
			return (bool) $wpdb->insert(
				$wpdb->prefix . 'fb_mail_log',
				array(
					'to_email'     => '',
					'subject'      => '',
					'headers'      => '',
					'body_hash'    => '',
					'status'       => $status,
					'provider_msg' => $provider,
					'timestamp'    => gmdate( 'Y-m-d H:i:s' ),
				)
			);
	}

		/**
		 * Append an audit log entry.
		 *
		 * @param array<string,mixed> $row Row data.
		 * @return bool
		 */
	public static function append( array $row ): bool {
			global $wpdb;
			$type        = sanitize_text_field( (string) ( $row['type'] ?? '' ) );
			$original_id = absint( $row['original_id'] ?? 0 );
			$by          = absint( $row['by'] ?? 0 );
			$at          = (int) ( $row['at'] ?? time() );
			$result      = sanitize_text_field( (string) ( $row['result'] ?? '' ) );
			$provider    = wp_json_encode(
				array(
					'type'        => $type,
					'original_id' => $original_id,
					'by'          => $by,
					'result'      => $result,
				)
			);
			return (bool) $wpdb->insert(
				$wpdb->prefix . 'fb_mail_log',
				array(
					'to_email'     => '',
					'subject'      => '',
					'headers'      => '',
					'body_hash'    => '',
					'status'       => $type,
					'provider_msg' => $provider,
					'timestamp'    => gmdate( 'Y-m-d H:i:s', $at ),
				),
				array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
			);
	}
}
