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
use function get_current_user_id;
use function is_string;
use function register_rest_route;
use function rest_ensure_response;
use function sanitize_text_field;
use function wp_verify_nonce;
use function wp_unslash;

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
                                'args'                => array(
                                        'token' => array(
                                                'type'              => 'string',
                                                'required'          => true,
                                                'sanitize_callback' => 'sanitize_text_field',
                                        ),
                                ),
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

		$raw_token = $request->get_param( 'token' );

		if ( ! is_string( $raw_token ) ) {
			return new WP_Error( 'fbm_missing_token', __( 'A token is required.', 'foodbank-manager' ), array( 'status' => 400 ) );
		}

		$raw_token = sanitize_text_field( $raw_token );

		if ( '' === $raw_token ) {
			return new WP_Error( 'fbm_missing_token', __( 'A token is required.', 'foodbank-manager' ), array( 'status' => 400 ) );
		}

		$canonical_token = Token::canonicalize( $raw_token );

		if ( null === $canonical_token ) {
			return new WP_Error( 'fbm_invalid_token', __( 'Invalid or expired token.', 'foodbank-manager' ), array( 'status' => 400 ) );
		}

		$token_repository = new TokenRepository( $wpdb );
		$token_service    = new Token( $token_repository );
		$verification     = $token_service->verify( $canonical_token );

		if ( ! $verification['ok'] || null === $verification['member_id'] ) {
			if ( 'revoked' === $verification['reason'] ) {
				return new WP_Error( 'fbm_revoked_token', __( 'This token has been revoked.', 'foodbank-manager' ), array( 'status' => 403 ) );
			}

			return new WP_Error( 'fbm_invalid_token', __( 'Invalid or expired token.', 'foodbank-manager' ), array( 'status' => 400 ) );
		}

		$member_id          = (int) $verification['member_id'];
		$members_repository = new MembersRepository( $wpdb );
		$member             = $members_repository->find( $member_id );

		if ( null === $member || '' === $member['member_reference'] ) {
			return new WP_Error( 'fbm_invalid_token', __( 'Invalid or expired token.', 'foodbank-manager' ), array( 'status' => 400 ) );
		}

		if ( MembersRepository::STATUS_ACTIVE !== $member['status'] ) {
			return new WP_Error( 'fbm_inactive_member', __( 'Member account is not active.', 'foodbank-manager' ), array( 'status' => 403 ) );
		}

		$attendance_repository = new AttendanceRepository( $wpdb );
		$schedule              = new Schedule();
		$service               = new CheckinService( $attendance_repository, $schedule );

		$fingerprint = self::extract_fingerprint( $request );

		$result = $service->record( $member['member_reference'], 'qr', get_current_user_id(), null, false, null, $fingerprint );

		$status  = self::normalize_status( (string) $result['status'] );
		$message = (string) $result['message'];
		$time    = $result['time'] ?? null;

		if ( 'already' === $status ) {
			$latest = $attendance_repository->latest_for_member( $member['member_reference'] );

			if ( $latest instanceof DateTimeImmutable ) {
				$time = $latest->format( DATE_ATOM );
			}
		}

		$payload = array(
			'status'     => $status,
			'message'    => $message,
			'member_ref' => (string) $member['member_reference'],
			'time'       => is_string( $time ) ? $time : null,
		);

		$response = rest_ensure_response( $payload );

		if ( 'throttled' === $status ) {
			$response->set_status( 429 );
		}

		return $response;
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

                if ( '' === $status ) {
                        return 'error';
                }

                return $status;
        }
}
