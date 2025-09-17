<?php
/**
 * REST controller for attendance check-ins.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Rest;

use FoodBankManager\Attendance\AttendanceRepository;
use FoodBankManager\Attendance\CheckinService;
use FoodBankManager\Registration\MembersRepository;
use FoodBankManager\Token\TokenRepository;
use FoodBankManager\Token\TokenService;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use wpdb;
use function __;
use function current_user_can;
use function get_current_user_id;
use function in_array;
use function register_rest_route;
use function rest_ensure_response;
use function sanitize_text_field;
use function sanitize_textarea_field;
use function strtolower;
use function trim;
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
				'args'                => array(
					'token'         => array(
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'manual_code'   => array(
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'method'        => array(
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'note'          => array(
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'sanitize_textarea_field',
					),
					'override'      => array(
						'type'     => 'boolean',
						'required' => false,
					),
					'override_note' => array(
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'sanitize_textarea_field',
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

		$token               = $request->get_param( 'token' );
		$manual_code         = $request->get_param( 'manual_code' );
		$method              = $request->get_param( 'method' );
		$note                = $request->get_param( 'note' );
		$override            = $request->get_param( 'override' );
		$override_note_param = $request->get_param( 'override_note' );

		$token         = is_string( $token ) ? sanitize_text_field( $token ) : '';
		$manual_code   = is_string( $manual_code ) ? sanitize_text_field( $manual_code ) : '';
		$method        = is_string( $method ) ? sanitize_text_field( $method ) : 'qr';
		$note          = is_string( $note ) ? sanitize_textarea_field( $note ) : null;
		$override      = self::is_truthy_flag( $override );
		$override_note = is_string( $override_note_param ) ? sanitize_textarea_field( $override_note_param ) : '';

		if ( $override ) {
			if ( ! current_user_can( 'fbm_manage' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability registered on activation.
				return new WP_Error( 'fbm_override_forbidden', __( 'You are not allowed to override collection warnings.', 'foodbank-manager' ), array( 'status' => 403 ) );
			}

			if ( '' === $override_note ) {
				return new WP_Error( 'fbm_override_note_required', __( 'An override note is required.', 'foodbank-manager' ), array( 'status' => 400 ) );
			}
		}

		if ( '' === $token && '' === $manual_code ) {
			return new WP_Error( 'fbm_invalid_reference', __( 'A token or manual code is required.', 'foodbank-manager' ), array( 'status' => 400 ) );
		}

		$attendance_repository = new AttendanceRepository( $wpdb );
		$members_repository    = new MembersRepository( $wpdb );
		$service               = new CheckinService( $attendance_repository );

		$member_reference = null;

		if ( '' !== $token ) {
			$token_repository = new TokenRepository( $wpdb );
			$token_service    = new TokenService( $token_repository );
			$member_id        = $token_service->verify( $token );

			if ( null === $member_id ) {
				return new WP_Error( 'fbm_invalid_token', __( 'The provided token is not recognized or has been revoked.', 'foodbank-manager' ), array( 'status' => 400 ) );
			}

			$member = $members_repository->find( $member_id );

			if ( null === $member || '' === $member['member_reference'] ) {
				return new WP_Error( 'fbm_invalid_token', __( 'The provided token is not recognized or has been revoked.', 'foodbank-manager' ), array( 'status' => 400 ) );
			}

			if ( 'active' !== $member['status'] ) {
				return new WP_Error( 'fbm_inactive_member', __( 'This member is not currently active.', 'foodbank-manager' ), array( 'status' => 400 ) );
			}

			$member_reference = $member['member_reference'];
		} else {
			$record = $members_repository->find_by_reference( $manual_code );

			if ( null === $record ) {
				return new WP_Error( 'fbm_unknown_reference', __( 'The provided manual code is not recognized.', 'foodbank-manager' ), array( 'status' => 400 ) );
			}

			if ( 'active' !== $record['status'] ) {
				return new WP_Error( 'fbm_inactive_member', __( 'This member is not currently active.', 'foodbank-manager' ), array( 'status' => 400 ) );
			}

			$member_reference = $record['member_reference'];
		}

		$override_note = '' !== $override_note ? $override_note : null;

                $result = $service->record( $member_reference, $method, get_current_user_id(), $note, $override, $override_note );

                $status = (string) $result['status'];

                switch ( $status ) {
                        case CheckinService::STATUS_OUT_OF_WINDOW:
                                if ( ! isset( $result['window'] ) ) {
                                        $result['window'] = array(
                                                'day'      => 'thursday',
                                                'start'    => '11:00',
                                                'end'      => '14:30',
                                                'timezone' => 'Europe/London',
                                        );
                                }
                                break;
                        case CheckinService::STATUS_DUPLICATE_DAY:
                                if ( ! isset( $result['duplicate'] ) ) {
                                        $result['duplicate'] = true;
                                }
                                break;
                        case CheckinService::STATUS_RECENT_WARNING:
                                if ( ! isset( $result['requires_override'] ) ) {
                                        $result['requires_override'] = true;
                                }
                                break;
                }

                return rest_ensure_response( $result );
        }

	/**
	 * Normalize boolean-like values from REST parameters.
	 *
	 * @param mixed $value Raw request value.
	 */
	private static function is_truthy_flag( $value ): bool {
		if ( is_bool( $value ) ) {
			return $value;
		}

		if ( is_numeric( $value ) ) {
			return (bool) (int) $value;
		}

		if ( is_string( $value ) ) {
			$value = strtolower( trim( $value ) );

			return in_array( $value, array( '1', 'true', 'yes', 'on' ), true );
		}

		return false;
	}
}
