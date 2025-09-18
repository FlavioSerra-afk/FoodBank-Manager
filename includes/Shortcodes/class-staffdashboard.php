<?php
/**
 * Staff dashboard shortcode handler.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Shortcodes;

use FoodBankManager\Core\Assets;
use FoodBankManager\Core\Schedule;
use FoodBankManager\Rest\CheckinController;
use wpdb;
use function add_shortcode;
use function current_user_can;
use function esc_html__;
use function in_array;
use function function_exists;
use function is_bool;
use function is_numeric;
use function is_readable;
use function is_user_logged_in;
use function is_string;
use function ob_get_clean;
use function ob_start;
use function get_current_user_id;
use function status_header;
use function strtolower;
use function trim;
use function sanitize_text_field;
use function sanitize_textarea_field;
use function wp_unslash;
use function wp_verify_nonce;

/**
 * Renders the staff dashboard shortcode.
 */
final class StaffDashboard {

	private const SHORTCODE           = 'fbm_staff_dashboard';
	private const MANUAL_NONCE_ACTION = 'fbm_staff_manual_entry';
	private const MANUAL_NONCE_FIELD  = 'fbm_staff_manual_nonce';

		/**
		 * Register the shortcode with WordPress.
		 */
	public static function register(): void {
		add_shortcode( self::SHORTCODE, array( self::class, 'render' ) );
	}

	/**
	 * Render the staff dashboard view.
	 *
	 * @param array<string, mixed> $atts Shortcode attributes.
	 */
	public static function render( array $atts = array() ): string {
			unset( $atts );

		if ( ! is_user_logged_in() || ! current_user_can( 'fbm_view' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability registered on activation.
			if ( function_exists( 'status_header' ) ) {
						status_header( 403 );
			}

				return '<div class="fbm-staff-dashboard fbm-staff-dashboard--denied">'
					. esc_html__( 'Staff dashboard is available to authorised team members only.', 'foodbank-manager' )
					. '</div>';
		}

				Assets::mark_staff_dashboard();

				$manual_entry = self::maybe_handle_manual_entry();

				ob_start();
				$template = FBM_PATH . 'templates/public/staff-dashboard.php';
		if ( is_readable( $template ) ) {
				include $template;
		}

			$output = ob_get_clean();

				return is_string( $output ) ? $output : '';
	}

		/**
		 * Handle manual code submissions when JavaScript is unavailable.
		 *
		 * @return array{status:string,message:string,code?:string,requires_override?:bool,override_note?:string}|null
		 */
	private static function maybe_handle_manual_entry(): ?array {
		if ( ! current_user_can( 'fbm_checkin' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability registered on activation.
				return null;
		}

		$request_method = isset( $_SERVER['REQUEST_METHOD'] ) ? (string) wp_unslash( $_SERVER['REQUEST_METHOD'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Request method sanitized by strict comparison below.

		if ( 'POST' !== strtoupper( $request_method ) ) {
			return null;
		}

		$nonce_input = isset( $_POST[ self::MANUAL_NONCE_FIELD ] ) ? wp_unslash( $_POST[ self::MANUAL_NONCE_FIELD ] ) : null; // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Checked below and validated prior to use.
		if ( ! is_string( $nonce_input ) ) {
			return self::manual_response( 'invalid', esc_html__( 'Security check failed. Please try again.', 'foodbank-manager' ) );
		}

		$nonce = $nonce_input;

		if ( ! wp_verify_nonce( $nonce, self::MANUAL_NONCE_ACTION ) ) {
			return self::manual_response( 'invalid', esc_html__( 'Security check failed. Please try again.', 'foodbank-manager' ) );
		}

		$code_input = isset( $_POST['code'] ) ? wp_unslash( $_POST['code'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Intentional manual submission handler and sanitized below.
		if ( ! is_string( $code_input ) ) {
			return self::manual_response( 'invalid', esc_html__( 'Enter a valid collection code.', 'foodbank-manager' ) );
		}

		$raw_code = sanitize_text_field( $code_input );
		$raw_code = trim( $raw_code );

		if ( '' === $raw_code ) {
				return self::manual_response( 'invalid', esc_html__( 'Enter a valid collection code.', 'foodbank-manager' ) );
		}

		$override_flag = isset( $_POST['override'] ) ? wp_unslash( $_POST['override'] ) : null; // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Checked above and normalized below.
			$override  = false;

		if ( is_bool( $override_flag ) ) {
				$override = $override_flag;
		} elseif ( is_numeric( $override_flag ) ) {
				$override = ( (int) $override_flag ) === 1;
		} elseif ( is_string( $override_flag ) ) {
				$override = in_array( strtolower( trim( $override_flag ) ), array( '1', 'true', 'yes', 'on' ), true );
		}

			$override_note = '';
		if ( $override ) {
			$note_input = isset( $_POST['override_note'] ) ? wp_unslash( $_POST['override_note'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Intentional manual submission handler sanitized immediately below.
			if ( is_string( $note_input ) ) {
				$override_note = sanitize_textarea_field( $note_input );
			}
		}

			global $wpdb;

			$invalid = esc_html__( 'Enter a valid collection code.', 'foodbank-manager' );

		if ( ! $wpdb instanceof wpdb ) {
				return self::manual_response( 'invalid', $invalid, array( 'code' => $raw_code ) );
		}

			$payload = array(
				'manual_code'   => $raw_code,
				'method'        => 'manual',
				'override'      => $override,
				'override_note' => $override ? $override_note : '',
			);

			/**
			 * Manual entry result payload.
			 *
			 * @var array<string,mixed> $result
			 */
			$result = CheckinController::process_checkin_payload( $payload, $wpdb, null, get_current_user_id() );

			$status = (string) ( $result['status'] ?? '' );
			if ( '' === $status ) {
				$status = 'invalid';
			}

			$message = (string) ( $result['message'] ?? '' );
			if ( '' === $message ) {
				$message = $invalid;
			}
			$requires_override = ! empty( $result['requires_override'] );
			if ( ! $requires_override && 'already' === $status && isset( $result['time'] ) && is_string( $result['time'] ) && '' !== $result['time'] ) {
				$requires_override = true;
			}

			$code_for_form = 'success' === $status ? '' : $raw_code;
			if ( $requires_override ) {
					$code_for_form = $raw_code;
			}

			$override_note_for_form = ( $override && 'success' !== $status ) ? $override_note : '';

			return self::manual_response(
				$status,
				$message,
				array(
					'code'              => $code_for_form,
					'requires_override' => $requires_override,
					'override_note'     => $override_note_for_form,
				)
			);
	}

		/**
		 * Compose a manual entry response payload.
		 *
		 * @param string              $status  Response status identifier.
		 * @param string              $message Localized response message.
		 * @param array<string,mixed> $extra   Additional response context.
		 */
	private static function manual_response( string $status, string $message, array $extra = array() ): array {
			$payload = array(
				'status'  => $status,
				'message' => $message,
			);

			foreach ( $extra as $key => $value ) {
					$payload[ $key ] = $value;
			}

			return $payload;
	}
}
