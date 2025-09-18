<?php
/**
 * REST controller for attendance check-ins.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Rest;

use DateTimeImmutable;
use FoodBankManager\Attendance\AttendanceRepository;
use FoodBankManager\Attendance\CheckinService;
use FoodBankManager\Core\Schedule;
use FoodBankManager\Registration\MembersRepository;
use FoodBankManager\Token\Token;
use FoodBankManager\Token\TokenRepository;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use wpdb;
use function __;
use function current_user_can;
use function esc_html__;
use function in_array;
use function get_current_user_id;
use function is_bool;
use function is_string;
use function preg_replace;
use function register_rest_route;
use function rest_ensure_response;
use function sanitize_text_field;
use function sanitize_textarea_field;
use function strtolower;
use function trim;
use function strtoupper;
use function wp_unslash;
use function wp_verify_nonce;

/**
 * REST routes for the staff dashboard.
 */
final class CheckinController {
	private const ROUTE_NAMESPACE = 'fbm/v1';
	private const ROUTE_PATH      = '/checkin';

	/**
	 * Register REST API routes.
	 */
	public static function register_routes(): void {
				register_rest_route(
					self::ROUTE_NAMESPACE,
					self::ROUTE_PATH,
					array(
						'methods'             => WP_REST_Server::CREATABLE,
						'callback'            => array( self::class, 'handle_checkin' ),
						'permission_callback' => array( self::class, 'verify_permissions' ),
					)
				);
	}

	/**
	 * Enforce capability and nonce checks.
	 *
	 * @param WP_REST_Request $request Incoming REST request.
	 */
	public static function verify_permissions( WP_REST_Request $request ): bool|WP_Error {
		if ( ! current_user_can( 'fbm_checkin' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability registered on activation.
			return new WP_Error( 'fbm_forbidden', __( 'You are not allowed to record collections.', 'foodbank-manager' ), array( 'status' => 403 ) );
		}

		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( ! is_string( $nonce ) || '' === $nonce ) {
			$nonce = (string) $request->get_param( '_wpnonce' );
		}

		if ( ! is_string( $nonce ) || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return new WP_Error( 'fbm_invalid_nonce', __( 'Security check failed.', 'foodbank-manager' ), array( 'status' => 403 ) );
		}

		return true;
	}

	/**
	 * Handle the check-in request.
	 *
	 * @param WP_REST_Request $request Incoming REST request.
	 */
	public static function handle_checkin( WP_REST_Request $request ): WP_REST_Response|WP_Error {
			global $wpdb;

		if ( ! $wpdb instanceof wpdb ) {
				return new WP_Error( 'fbm_db_unavailable', __( 'Database connection unavailable.', 'foodbank-manager' ), array( 'status' => 500 ) );
		}

			$payload = array(
				'code'          => $request->get_param( 'code' ),
				'manual_code'   => $request->get_param( 'manual_code' ),
				'override'      => $request->get_param( 'override' ),
				'override_note' => $request->get_param( 'override_note' ),
				'method'        => $request->get_param( 'method' ),
			);

			$result = self::process_checkin_payload(
				$payload,
				$wpdb,
				self::extract_fingerprint( $request ),
				get_current_user_id()
			);

			$response = rest_ensure_response( $result );

		if ( 'throttled' === $result['status'] ) {
				$response->set_status( 429 );
		}

			return $response;
	}

		/**
		 * Process a check-in payload for REST or manual fallbacks.
		 *
		 * @param array<string,mixed> $payload     Raw payload data.
		 * @param wpdb                $wpdb        WordPress database instance.
		 * @param string|null         $fingerprint Optional request fingerprint for throttling.
		 * @param int|null            $user_id     Acting user identifier.
		 *
		 * @return array{
		 *     status:string,
		 *     message:string,
		 *     member_ref:?string,
		 *     time:?string,
		 *     window:array{day:string,start:string,end:string,timezone:string},
		 *     window_labels:array{day:string,range:string,sentence:string,timezone:string,notice:string},
		 *     window_notice:string,
		 *     requires_override:bool
		 * }
		 */
	public static function process_checkin_payload( array $payload, wpdb $wpdb, ?string $fingerprint = null, ?int $user_id = null ): array {
			$schedule              = new Schedule();
			$attendance_repository = new AttendanceRepository( $wpdb );
			$token_repository      = new TokenRepository( $wpdb );
			$token_service         = new Token( $token_repository );
			$members_repository    = new MembersRepository( $wpdb );

			$window        = $schedule->current_window();
			$window_labels = Schedule::window_labels( $window );
			$window_notice = Schedule::window_notice( $window );

			$method_input   = $payload['method'] ?? null;
			$code_input     = $payload['code'] ?? null;
			$manual_input   = $payload['manual_code'] ?? null;
			$override_input = $payload['override'] ?? null;
			$note_input     = $payload['override_note'] ?? null;

			$method        = self::sanitize_method_value( $method_input );
			$code          = self::sanitize_code_value( $code_input );
			$manual_code   = self::sanitize_code_value( $manual_input );
			$override      = self::sanitize_bool_value( $override_input );
			$override_note = self::sanitize_override_note( $note_input );

			$invalid_message = esc_html__( 'Enter a valid collection code.', 'foodbank-manager' );

		if ( $override && '' === $override_note ) {
				return self::compose_payload( 'invalid', esc_html__( 'An override note is required.', 'foodbank-manager' ), null, null, $window, $window_labels, $window_notice, true );
		}

			$resolution = null;

		if ( '' !== $code ) {
				$resolution = self::resolve_token_reference( $code, $token_service, $members_repository, $invalid_message );
		} elseif ( '' !== $manual_code ) {
				$resolution = self::resolve_manual_reference( $manual_code, $token_service, $members_repository, $invalid_message );
		} else {
				return self::compose_payload( 'invalid', $invalid_message, null, null, $window, $window_labels, $window_notice, false );
		}

		if ( ! $resolution['ok'] ) {
			$status  = $resolution['status'];
			$message = $resolution['message'];
			$member  = $resolution['member_ref'] ?? null;
			$display = $resolution['display_ref'] ?? $member;

			return self::compose_payload( $status, $message, $display, null, $window, $window_labels, $window_notice, ( 'recent_warning' === $status ) );
		}

		$member_reference = $resolution['member_ref'];
		$member_display   = is_string( $resolution['display_ref'] ?? null ) ? $resolution['display_ref'] : '';

		if ( null === $member_reference ) {
				return self::compose_payload( 'invalid', $invalid_message, null, null, $window, $window_labels, $window_notice, false );
		}

		if ( '' === $method ) {
				$method = '' !== $code ? 'qr' : 'manual';
		}

			$acting_user = null;

		if ( is_int( $user_id ) ) {
			$acting_user = $user_id;
		}

		if ( null !== $acting_user && 0 >= $acting_user ) {
				$acting_user = null;
		}

		$service = new CheckinService( $attendance_repository, $schedule );

		/**
		 * Normalized check-in response data.
		 *
		 * @var array<string,mixed> $result
		 */
		$result = $service->record(
			$member_reference,
			$method,
			$acting_user,
			null,
			$override,
			$override ? $override_note : null,
			$fingerprint
		);

		$status  = self::normalize_status( (string) ( $result['status'] ?? '' ) );
		$message = (string) ( $result['message'] ?? '' );
		$time    = ( isset( $result['time'] ) && is_string( $result['time'] ) && '' !== $result['time'] ) ? $result['time'] : null;
		$member  = ( isset( $result['member_ref'] ) && is_string( $result['member_ref'] ) && '' !== $result['member_ref'] ) ? $result['member_ref'] : $member_reference;

		if ( 'already' === $status ) {
				$latest = $attendance_repository->latest_for_member( $member_reference );

			if ( $latest instanceof DateTimeImmutable ) {
				$time = $latest->format( DATE_ATOM );
			}
		}

		if ( ! is_string( $time ) ) {
			$time = null;
		}

		if ( '' === $message ) {
			if ( 'invalid' === $status ) {
					$message = $invalid_message;
			} elseif ( 'revoked' === $status ) {
					$message = esc_html__( 'This code has been revoked.', 'foodbank-manager' );
			}
		}

		$display_reference = $member_display;
		if ( '' === $display_reference && is_string( $member ) && '' !== $member ) {
			$display_reference = $member;
		}

		if ( '' === $display_reference ) {
			$display_reference = $member_reference;
		}

		return self::compose_payload(
			$status,
			$message,
			$display_reference,
			$time,
			$window,
			$window_labels,
			$window_notice,
			( 'recent_warning' === $status )
		);
	}

		/**
		 * Sanitize general code inputs from REST or manual payloads.
		 *
		 * @param mixed $value Raw input value.
		 */
	private static function sanitize_code_value( $value ): string {
		if ( ! is_string( $value ) ) {
				return '';
		}

			$value = sanitize_text_field( $value );

			return '' !== $value ? $value : '';
	}

		/**
		 * Sanitize override note inputs.
		 *
		 * @param mixed $value Raw input value.
		 */
	private static function sanitize_override_note( $value ): string {
		if ( ! is_string( $value ) ) {
				return '';
		}

			$value = sanitize_textarea_field( $value );

			return '' !== $value ? $value : '';
	}

		/**
		 * Normalize boolean-like inputs.
		 *
		 * @param mixed $value Raw input value.
		 */
	private static function sanitize_bool_value( $value ): bool {
		if ( is_bool( $value ) ) {
				return $value;
		}

		if ( is_numeric( $value ) ) {
			return 1 === (int) $value;
		}

		if ( is_string( $value ) ) {
				$value = strtolower( trim( $value ) );

				return in_array( $value, array( '1', 'true', 'yes', 'on' ), true );
		}

			return false;
	}

		/**
		 * Normalize the collection method string.
		 *
		 * @param mixed $value Raw input value.
		 */
	private static function sanitize_method_value( $value ): string {
		if ( ! is_string( $value ) ) {
				return '';
		}

			$value = strtolower( sanitize_text_field( $value ) );

			return in_array( $value, array( 'qr', 'manual' ), true ) ? $value : '';
	}

	/**
	 * Resolve a member reference from a token payload.
	 *
	 * @param string            $code               Raw token code.
	 * @param Token             $token_service      Token service helper.
	 * @param MembersRepository $members_repository Members data repository.
	 * @param string            $invalid_message    Localized invalid message.
	 *
	 * @return array{ok:bool,status:string,message:string,member_ref:?string,display_ref:?string}
	 */
	private static function resolve_token_reference( string $code, Token $token_service, MembersRepository $members_repository, string $invalid_message ): array {
			$canonical = Token::canonicalize( $code );

		if ( null === $canonical ) {
			return array(
				'ok'          => false,
				'status'      => 'invalid',
				'message'     => $invalid_message,
				'member_ref'  => null,
				'display_ref' => null,
			);
		}

			$verification = $token_service->verify( $canonical );

		if ( ! $verification['ok'] || null === $verification['member_id'] ) {
			if ( 'revoked' === $verification['reason'] ) {
				return array(
					'ok'          => false,
					'status'      => 'revoked',
					'message'     => esc_html__( 'This code has been revoked.', 'foodbank-manager' ),
					'member_ref'  => null,
					'display_ref' => null,
				);
			}

			return array(
				'ok'          => false,
				'status'      => 'invalid',
				'message'     => $invalid_message,
				'member_ref'  => null,
				'display_ref' => null,
			);
		}

			$member = $members_repository->find( (int) $verification['member_id'] );

			return self::validate_member_record( $member, $invalid_message );
	}

	/**
	 * Resolve a member reference from manual input.
	 *
	 * @param string            $code               Manual input value.
	 * @param Token             $token_service      Token service helper.
	 * @param MembersRepository $members_repository Members data repository.
	 * @param string            $invalid_message    Localized invalid message.
	 *
	 * @return array{ok:bool,status:string,message:string,member_ref:?string,display_ref:?string}
	 */
	private static function resolve_manual_reference( string $code, Token $token_service, MembersRepository $members_repository, string $invalid_message ): array {
			$maybe_token = Token::canonicalize( $code );

		if ( null !== $maybe_token ) {
				return self::resolve_token_reference( $maybe_token, $token_service, $members_repository, $invalid_message );
		}

			$reference = self::canonicalize_reference( $code );

		if ( null === $reference ) {
			return array(
				'ok'          => false,
				'status'      => 'invalid',
				'message'     => $invalid_message,
				'member_ref'  => null,
				'display_ref' => null,
			);
		}

			$member = $members_repository->find_by_reference( $reference );

			return self::validate_member_record( $member, $invalid_message );
	}

	/**
	 * Ensure a member record is active and usable.
	 *
	 * @param array{id:int,status:string,member_reference:string}|null $member           Candidate member record.
	 * @param string                                                   $invalid_message Localized invalid message.
	 *
	 * @return array{ok:bool,status:string,message:string,member_ref:?string,display_ref:?string}
	 */
	private static function validate_member_record( ?array $member, string $invalid_message ): array {
		if ( null === $member ) {
			return array(
				'ok'          => false,
				'status'      => 'invalid',
				'message'     => $invalid_message,
				'member_ref'  => null,
				'display_ref' => null,
			);
		}

			$reference  = (string) $member['member_reference'];
			$normalized = self::canonicalize_reference( $reference );

		if ( null === $normalized ) {
			return array(
				'ok'          => false,
				'status'      => 'invalid',
				'message'     => $invalid_message,
				'member_ref'  => null,
				'display_ref' => null,
			);
		}

		if ( MembersRepository::STATUS_ACTIVE !== $member['status'] ) {
			return array(
				'ok'          => false,
				'status'      => 'revoked',
				'message'     => esc_html__( 'This member is not currently active.', 'foodbank-manager' ),
				'member_ref'  => $normalized,
				'display_ref' => $reference,
			);
		}

		return array(
			'ok'          => true,
			'status'      => 'ok',
			'message'     => '',
			'member_ref'  => $normalized,
			'display_ref' => $reference,
		);
	}

	/**
	 * Canonicalize member references for consistent comparisons.
	 *
	 * @param string|null $raw Raw member reference.
	 */
	private static function canonicalize_reference( ?string $raw ): ?string {
		if ( ! is_string( $raw ) ) {
				return null;
		}

			$trimmed = trim( $raw );

		if ( '' === $trimmed ) {
				return null;
		}

			$normalized = strtoupper( $trimmed );
			$normalized = preg_replace( '/[^A-Z0-9-]/', '', $normalized );

		if ( ! is_string( $normalized ) || '' === $normalized ) {
				return null;
		}

			return $normalized;
	}

	/**
	 * Compose the common response payload structure.
	 *
	 * @param string                                                                       $status            Normalized status identifier.
	 * @param string                                                                       $message           Localized response message.
	 * @param string|null                                                                  $member_ref        Canonical member reference.
	 * @param string|null                                                                  $time              ISO8601 timestamp if available.
	 * @param array{day:string,start:string,end:string,timezone:string}                    $window            Active window definition.
	 * @param array{day:string,range:string,sentence:string,timezone:string,notice:string} $window_labels Window labels.
	 * @param string                                                                       $window_notice     Window notice text.
	 * @param bool                                                                         $requires_override Whether an override is required.
	 *
	 * @return array{
	 *     status:string,
	 *     message:string,
	 *     member_ref:?string,
	 *     time:?string,
	 *     window:array{day:string,start:string,end:string,timezone:string},
	 *     window_labels:array{day:string,range:string,sentence:string,timezone:string,notice:string},
	 *     window_notice:string,
	 *     requires_override:bool
	 * }
	 */
	private static function compose_payload( string $status, string $message, ?string $member_ref, ?string $time, array $window, array $window_labels, string $window_notice, bool $requires_override ): array {
			return array(
				'status'            => $status,
				'message'           => $message,
				'member_ref'        => ( null !== $member_ref && '' !== $member_ref ) ? $member_ref : null,
				'time'              => ( null !== $time && '' !== $time ) ? $time : null,
				'window'            => $window,
				'window_labels'     => $window_labels,
				'window_notice'     => $window_notice,
				'requires_override' => $requires_override,
			);
	}

		/**
		 * Determine the best available request fingerprint for throttling.
		 *
		 * @param WP_REST_Request $request Incoming request.
		 */
	private static function extract_fingerprint( WP_REST_Request $request ): ?string {
		$headers = array(
			$request->get_header( 'X-Forwarded-For' ),
			$request->get_header( 'CF-Connecting-IP' ),
			$request->get_header( 'X-Real-IP' ),
			$request->get_header( 'REMOTE_ADDR' ),
		);

		foreach ( $headers as $raw_header ) {
			if ( ! is_string( $raw_header ) ) {
				continue;
			}

			$raw_header = trim( $raw_header );

			if ( '' === $raw_header ) {
				continue;
			}

			$parts = explode( ',', $raw_header );
			$value = trim( $parts[0] );

			if ( '' !== $value ) {
				return sanitize_text_field( $value );
			}
		}

		if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$server_address = sanitize_text_field( wp_unslash( (string) $_SERVER['REMOTE_ADDR'] ) );

			if ( '' !== $server_address ) {
				return $server_address;
			}
		}

		return null;
	}

		/**
		 * Normalize service status values for the REST payload.
		 *
		 * @param string $status Raw status from the check-in service.
		 */
	private static function normalize_status( string $status ): string {
		if ( CheckinService::STATUS_DUPLICATE_DAY === $status ) {
				return 'already';
		}

		if ( CheckinService::STATUS_RECENT_WARNING === $status ) {
				return 'recent_warning';
		}

		if ( CheckinService::STATUS_THROTTLED === $status ) {
				return 'throttled';
		}

		if ( CheckinService::STATUS_OUT_OF_WINDOW === $status || CheckinService::STATUS_ERROR === $status ) {
				return 'invalid';
		}

		if ( '' === $status ) {
				return 'invalid';
		}

		if ( CheckinService::STATUS_SUCCESS === $status ) {
				return 'success';
		}

			return $status;
	}
}
