<?php
/**
 * Capture mail failures.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Mail;

use function add_action;
use function get_option;
use function update_option;
use function sanitize_text_field;
use function time;
use function wp_mail;
use function sanitize_email;
use function is_array;

/**
 * Record recent mail failures for diagnostics.
 */
class FailureLog {
	private const OPTION = 'fbm_mail_failures';
	private const MAX    = 5;

	/**
	 * Hook capture.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'wp_mail_failed', array( self::class, 'capture' ), 10, 2 );
	}

	/**
	 * Capture failure from wp_mail_failed.
	 *
	 * @param \WP_Error $error Error.
	 * @param array      $mail  Mail data.
	 * @return void
	 */
	public static function capture( \WP_Error $error, array $mail ): void {
		$entries = get_option( self::OPTION, array() );
		if ( ! is_array( $entries ) ) {
			$entries = array();
		}
		$entries[] = array(
			'time'    => time(),
			'error'   => sanitize_text_field( $error->get_error_message() ),
			'to'      => $mail['to'],
			'subject' => sanitize_text_field( (string) ( $mail['subject'] ?? '' ) ),
			'message' => (string) ( $mail['message'] ?? '' ),
			'headers' => $mail['headers'] ?? array(),
		);
		$entries   = array_slice( $entries, - self::MAX );
		update_option( self::OPTION, $entries, false ); // @phpstan-ignore-line
	}

	/**
	 * Get recent failures.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public static function recent(): array {
		$entries = get_option( self::OPTION, array() );
		return is_array( $entries ) ? $entries : array();
	}

	/**
	 * Retry send.
	 *
	 * @param int $index Index.
	 * @return bool
	 */
	public static function retry( int $index ): bool {
		$entries = self::recent();
		if ( ! isset( $entries[ $index ] ) ) {
			return false;
		}
		$entry   = $entries[ $index ];
		$headers = $entry['headers'];
		if ( ! is_array( $headers ) ) {
			$headers = array( (string) $headers );
		}
		$headers[] = 'Content-Type: text/html; charset=UTF-8';
		$to        = $entry['to'];
		$to        = is_array( $to ) ? $to : array( $to );
		$to        = array_filter( array_map( 'sanitize_email', $to ) );
		return (bool) wp_mail( $to, $entry['subject'], $entry['message'], $headers );
	}
}
