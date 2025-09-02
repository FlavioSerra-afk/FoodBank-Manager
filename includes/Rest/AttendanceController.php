<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Rest;

use WP_REST_Request;
use WP_REST_Response;
use FoodBankManager\Auth\Permissions;
use FoodBankManager\Security\Helpers;
use FoodBankManager\Attendance\TokenService;

class AttendanceController {

	public function register_routes(): void {
		register_rest_route(
			'pcc-fb/v1',
			'/attendance/checkin',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'checkin' ),
				'permission_callback' => array( $this, 'check_permissions' ),
				'args'                => array(
					'application_id' => array(
						'type'     => 'integer',
						'required' => false,
					),
					'token'          => array(
						'type'     => 'string',
						'required' => false,
					),
				),
			)
		);

		register_rest_route(
			'pcc-fb/v1',
			'/attendance/noshow',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'noshow' ),
				'permission_callback' => array( $this, 'check_permissions' ),
				'args'                => array(
					'application_id' => array(
						'type'     => 'integer',
						'required' => true,
					),
				),
			)
		);
	}

	public function check_permissions(): bool {
		return Permissions::user_can( 'attendance_checkin' );
	}

	public function checkin( WP_REST_Request $request ): WP_REST_Response {
		if ( ! Helpers::verify_nonce( 'wp_rest', '_wpnonce' ) ) {
			return new WP_REST_Response(
				array(
					'error' => array(
						'code'    => 'fbm_invalid_nonce',
						'message' => __( 'Invalid nonce', 'foodbank-manager' ),
					),
				),
				403
			);
		}
		$application_id = (int) $request->get_param( 'application_id' );
		$token          = (string) $request->get_param( 'token' );
		if ( $application_id === 0 && $token !== '' ) {
			$data           = TokenService::validate( $token );
			$application_id = (int) ( $data['a'] ?? 0 );
		}
		if ( $application_id === 0 ) {
			return new WP_REST_Response(
				array(
					'error' => array(
						'code'    => 'fbm_invalid_application',
						'message' => __( 'Invalid application reference', 'foodbank-manager' ),
					),
				),
				400
			);
		}
		// TODO(PRD §5.5): insert attendance record and apply policy rules.
		return new WP_REST_Response(
			array(
				'status'         => 'checked_in',
				'application_id' => $application_id,
			),
			200
		);
	}

	public function noshow( WP_REST_Request $request ): WP_REST_Response {
		if ( ! Helpers::verify_nonce( 'wp_rest', '_wpnonce' ) ) {
			return new WP_REST_Response(
				array(
					'error' => array(
						'code'    => 'fbm_invalid_nonce',
						'message' => __( 'Invalid nonce', 'foodbank-manager' ),
					),
				),
				403
			);
		}
		$application_id = (int) $request->get_param( 'application_id' );
		if ( $application_id === 0 ) {
			return new WP_REST_Response(
				array(
					'error' => array(
						'code'    => 'fbm_invalid_application',
						'message' => __( 'Invalid application reference', 'foodbank-manager' ),
					),
				),
				400
			);
		}
		// TODO(PRD §5.5): record no-show with audit log.
		return new WP_REST_Response(
			array(
				'status'         => 'no_show_recorded',
				'application_id' => $application_id,
			),
			200
		);
	}
}
