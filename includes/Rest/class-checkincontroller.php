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
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use wpdb;
use function __;
use function current_user_can;
use function get_current_user_id;
use function register_rest_route;
use function rest_ensure_response;
use function sanitize_text_field;
use function sanitize_textarea_field;
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
					'reference' => array(
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'method'    => array(
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'note'      => array(
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

			$reference = $request->get_param( 'reference' );
		if ( ! is_string( $reference ) || '' === $reference ) {
				return new WP_Error( 'fbm_invalid_reference', __( 'A member reference is required.', 'foodbank-manager' ), array( 'status' => 400 ) );
		}

			$method = $request->get_param( 'method' );
			$note   = $request->get_param( 'note' );

			$reference = sanitize_text_field( $reference );
			$method    = is_string( $method ) ? sanitize_text_field( $method ) : 'qr';
			$note      = is_string( $note ) ? sanitize_textarea_field( $note ) : null;

			$repository = new AttendanceRepository( $wpdb );
			$service    = new CheckinService( $repository );

			$result = $service->record( $reference, $method, get_current_user_id(), $note );

			return rest_ensure_response( $result );
	}
}
