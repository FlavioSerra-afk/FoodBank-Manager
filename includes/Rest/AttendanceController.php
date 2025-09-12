<?php
// phpcs:ignoreFile
/**
 * REST controller for attendance actions.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use FoodBankManager\Auth\Permissions;
use FoodBankManager\Security\Helpers;
use FoodBankManager\Attendance\TokenService;
use FoodBankManager\Attendance\AttendanceRepo;
use FoodBankManager\Attendance\Policy;
use FoodBankManager\Core\Options;
use FoodBankManager\Logging\Audit;
use FBM\Rest\ErrorHelper;
use wpdb;

/**
 * Attendance REST endpoints.
 */
class AttendanceController {

		/**
		 * Register attendance routes.
		 */
	public function register_routes(): void {
                register_rest_route(
                        'pcc-fb/v1',
                        '/attendance/checkin',
                        array(
                                'methods'             => 'POST',
                                'callback'            => array( $this, 'checkin' ),
                                'permission_callback' => array( $this, 'check_write_permissions' ),
                                'args'                => array(
                                        'application_id' => \FoodBankManager\Rest\ArgHelper::id( false ),
                                        'token'          => array(
                                                'type'              => 'string',
                                                'required'          => false,
                                                'sanitize_callback' => 'sanitize_text_field',
                                                'validate_callback' => static fn( $v ): bool => is_string( $v ) && $v !== '' && strlen( $v ) <= 512,
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
                                'permission_callback' => array( $this, 'check_write_permissions' ),
                                'args'                => array(
                                        'application_id' => \FoodBankManager\Rest\ArgHelper::id(),
                                ),
                        )
                );

                register_rest_route(
                        'pcc-fb/v1',
                        '/attendance/timeline',
                        array(
                                'methods'             => 'GET',
                                'callback'            => array( $this, 'timeline' ),
                                'permission_callback' => array( $this, 'check_view_permissions' ),
                                'args'                => array(
                                        'application_id' => \FoodBankManager\Rest\ArgHelper::id(),
                                        'from'           => array(
                                                'type'              => 'string',
                                                'required'          => false,
                                                'sanitize_callback' => 'sanitize_text_field',
                                                'validate_callback' => static fn( $v ): bool => is_string( $v ) && strtotime( $v ) !== false,
                                        ),
                                        'to'             => array(
                                                'type'              => 'string',
                                                'required'          => false,
                                                'sanitize_callback' => 'sanitize_text_field',
                                                'validate_callback' => static fn( $v ): bool => is_string( $v ) && strtotime( $v ) !== false,
                                        ),
                                ),
                        )
                );

                register_rest_route(
                        'pcc-fb/v1',
                        '/attendance/void',
                        array(
                                'methods'             => 'POST',
                                'callback'            => array( $this, 'void' ),
                                'permission_callback' => array( $this, 'check_admin_permissions' ),
                                'args'                => array(
                                        'attendance_id' => \FoodBankManager\Rest\ArgHelper::id(),
                                        'reason'        => array(
                                                'type'              => 'string',
                                                'required'          => false,
                                                'sanitize_callback' => array( Helpers::class, 'sanitize_text' ),
                                                'validate_callback' => static fn( $v ): bool => is_string( $v ) && strlen( $v ) <= 500,
                                        ),
                                ),
                        )
                );

                register_rest_route(
                        'pcc-fb/v1',
                        '/attendance/unvoid',
                        array(
                                'methods'             => 'POST',
                                'callback'            => array( $this, 'unvoid' ),
                                'permission_callback' => array( $this, 'check_admin_permissions' ),
                                'args'                => array(
                                        'attendance_id' => \FoodBankManager\Rest\ArgHelper::id(),
                                ),
                        )
                );

                register_rest_route(
                        'pcc-fb/v1',
                        '/attendance/note',
                        array(
                                'methods'             => 'POST',
                                'callback'            => array( $this, 'note' ),
                                'permission_callback' => array( $this, 'check_admin_permissions' ),
                                'args'                => array(
                                        'attendance_id' => \FoodBankManager\Rest\ArgHelper::id(),
                                        'note'          => array(
                                                'type'              => 'string',
                                                'required'          => true,
                                                'sanitize_callback' => array( Helpers::class, 'sanitize_text' ),
                                                'validate_callback' => static fn( $v ): bool => is_string( $v ) && $v !== '',
                                        ),
                                ),
                        )
                );
	}

		/**
		 * Check if user may check in attendees.
		 *
		 * @return bool
		 */
	public function check_write_permissions(): bool {
                    return Permissions::user_can( 'fb_manage_attendance' );
	}

		/**
		 * Check if user may view attendance records.
		 *
		 * @return bool
		 */
	public function check_view_permissions(): bool {
                    return Permissions::user_can( 'fb_manage_attendance' );
	}

		/**
		 * Check if user may administer attendance.
		 *
		 * @return bool
		 */
	public function check_admin_permissions(): bool {
                    return Permissions::user_can( 'fb_manage_attendance' );
	}

		/**
		 * Check in an attendee.
		 *
		 * @param WP_REST_Request $request Request.
		 *
		 * @return WP_REST_Response
		 */
	public function checkin( WP_REST_Request $request ): WP_REST_Response {
		$nonce = $request->get_header( 'x-wp-nonce' );
                if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
                        $err = ErrorHelper::from_wp_error(
                                new WP_Error( 'invalid_nonce', __( 'Invalid nonce', 'foodbank-manager' ), array( 'status' => 401 ) )
                        );
                        return new WP_REST_Response( $err['body'], $err['status'] );
                }
		$application_id = (int) $request->get_param( 'application_id' );
		$token          = (string) $request->get_param( 'token' );
		if ( $application_id === 0 && $token !== '' ) {
				$data           = TokenService::validate( $token );
				$application_id = (int) ( $data['a'] ?? 0 );
		}
                if ( $application_id === 0 ) {
                        $err = ErrorHelper::from_wp_error(
                                new WP_Error( 'invalid_param', __( 'Invalid application reference', 'foodbank-manager' ), array( 'status' => 422 ) )
                        );
                        return new WP_REST_Response( $err['body'], $err['status'] );
                }

		$policy_days = (int) Options::get( 'attendance.policy_days' );
                $last        = AttendanceRepo::last_present( $application_id );
		$now         = current_time( 'mysql', true );
		$override    = $request->get_param( 'override' );
		$override_ok = is_array( $override ) && ! empty( $override['allowed'] );
                if ( Policy::is_breach( $last, $policy_days, $now ) && ! $override_ok ) {
                        $err = ErrorHelper::from_wp_error(
                                new WP_Error(
                                        'conflict',
                                        __( 'Policy conflict', 'foodbank-manager' ),
                                        array(
                                                'status'  => 409,
                                                'details' => array(
                                                        'rule_days'        => $policy_days,
                                                        'last_attended_at' => $last,
                                                ),
                                        )
                                )
                        );
                        return new WP_REST_Response( $err['body'], $err['status'] );
                }

		$event_id = (int) $request->get_param( 'event_id' );
                $type     = Helpers::sanitize_text( (string) $request->get_param( 'type' ) );
                if ( $type === '' || ! in_array( $type, (array) Options::get( 'attendance.types' ), true ) ) {
				$type = 'in_person';
		}
		$method = Helpers::sanitize_text( (string) $request->get_param( 'method' ) );
		if ( $method === '' ) {
				$method = 'manual';
		}
		$note = '';
		if ( $override_ok ) {
				$note = Helpers::sanitize_text( (string) ( $override['note'] ?? '' ) );
		}

		global $wpdb;
		$ip     = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( (string) $_SERVER['REMOTE_ADDR'] ) ) : '';
		$ip_bin = $ip !== '' ? inet_pton( $ip ) : null;
		$wpdb->insert(
			$wpdb->prefix . 'fb_attendance',
			array(
				'form_id'             => 0,
				'application_id'      => $application_id,
				'event_id'            => $event_id ? $event_id : null,
				'attendance_at'       => $now,
				'status'              => 'present',
				'type'                => $type,
				'method'              => $method,
				'recorded_by_user_id' => get_current_user_id(),
				'notes'               => $note !== '' ? $note : null,
				'token_hash'          => $token !== '' ? hash( 'sha256', $token ) : null,
				'source_ip'           => $ip_bin,
				'created_at'          => $now,
				'updated_at'          => $now,
			),
			array( '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s' )
		);
		$attendance_id = (int) $wpdb->insert_id;

		return new WP_REST_Response(
			array(
				'attendance_id'     => $attendance_id,
				'application_id'    => $application_id,
				'status'            => 'present',
				'attendance_at'     => $now,
				'policy_overridden' => $override_ok,
			),
			200
		);
	}

		/**
		 * Mark a no-show.
		 *
		 * @param WP_REST_Request $request Request.
		 *
		 * @return WP_REST_Response
		 */
	public function noshow( WP_REST_Request $request ): WP_REST_Response {
		$nonce = $request->get_header( 'x-wp-nonce' );
                if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
                        $err = ErrorHelper::from_wp_error(
                                new WP_Error( 'invalid_nonce', __( 'Invalid nonce', 'foodbank-manager' ), array( 'status' => 401 ) )
                        );
                        return new WP_REST_Response( $err['body'], $err['status'] );
                }
		$application_id = (int) $request->get_param( 'application_id' );
                if ( $application_id === 0 ) {
                        $err = ErrorHelper::from_wp_error(
                                new WP_Error( 'invalid_param', __( 'Invalid application reference', 'foodbank-manager' ), array( 'status' => 422 ) )
                        );
                        return new WP_REST_Response( $err['body'], $err['status'] );
                }
		$event_id = (int) $request->get_param( 'event_id' );
                $type     = Helpers::sanitize_text( (string) $request->get_param( 'type' ) );
                if ( $type === '' || ! in_array( $type, (array) Options::get( 'attendance_types' ), true ) ) {
				$type = 'in_person';
		}
		$reason = Helpers::sanitize_text( (string) $request->get_param( 'reason' ) );

		global $wpdb;
		$now = current_time( 'mysql', true );
		$wpdb->insert(
			$wpdb->prefix . 'fb_attendance',
			array(
				'form_id'             => 0,
				'application_id'      => $application_id,
				'event_id'            => $event_id ? $event_id : null,
				'attendance_at'       => $now,
				'status'              => 'no_show',
				'type'                => $type,
				'method'              => 'manual',
				'recorded_by_user_id' => get_current_user_id(),
				'notes'               => $reason !== '' ? $reason : null,
				'created_at'          => $now,
				'updated_at'          => $now,
			),
			array( '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s' )
		);
		$attendance_id = (int) $wpdb->insert_id;
		return new WP_REST_Response(
			array(
				'attendance_id' => $attendance_id,
				'status'        => 'no_show',
			),
			200
		);
	}

		/**
		 * Get attendance timeline.
		 *
		 * @param WP_REST_Request $request Request.
		 *
		 * @return WP_REST_Response
		 */
	public function timeline( WP_REST_Request $request ): WP_REST_Response {
		$application_id = (int) $request->get_param( 'application_id' );
		if ( $application_id === 0 ) {
				return new WP_REST_Response( array(), 200 );
		}
		$from           = (string) $request->get_param( 'from' );
		$to             = (string) $request->get_param( 'to' );
		$include_voided = (bool) $request->get_param( 'include_voided' );
		$rows           = AttendanceRepo::timeline( $application_id, $from, $to, $include_voided );
		return new WP_REST_Response( array( 'records' => $rows ), 200 );
	}

		/**
		 * Void an attendance record.
		 *
		 * @param WP_REST_Request $request Request.
		 *
		 * @return WP_REST_Response
		 */
	public function void( WP_REST_Request $request ): WP_REST_Response {
		$nonce = $request->get_header( 'x-wp-nonce' );
                if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
                        $err = ErrorHelper::from_wp_error(
                                new WP_Error( 'invalid_nonce', __( 'Invalid nonce', 'foodbank-manager' ), array( 'status' => 401 ) )
                        );
                        return new WP_REST_Response( $err['body'], $err['status'] );
                }
		$attendance_id = (int) $request->get_param( 'attendance_id' );
		$reason        = Helpers::sanitize_text( (string) $request->get_param( 'reason' ) );
		$now           = current_time( 'mysql', true );
            $ok            = AttendanceRepo::set_void( $attendance_id, true, $reason !== '' ? $reason : null, get_current_user_id(), $now );
		if ( $ok ) {
				Audit::log( 'attendance_void', 'attendance', $attendance_id, get_current_user_id(), array( 'reason' => $reason ) );
		}
		return new WP_REST_Response(
			array(
				'ok'            => $ok,
				'attendance_id' => $attendance_id,
				'is_void'       => 1,
			),
			200
		);
	}

		/**
		 * Unvoid an attendance record.
		 *
		 * @param WP_REST_Request $request Request.
		 *
		 * @return WP_REST_Response
		 */
	public function unvoid( WP_REST_Request $request ): WP_REST_Response {
		$nonce = $request->get_header( 'x-wp-nonce' );
                if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
                        $err = ErrorHelper::from_wp_error(
                                new WP_Error( 'invalid_nonce', __( 'Invalid nonce', 'foodbank-manager' ), array( 'status' => 401 ) )
                        );
                        return new WP_REST_Response( $err['body'], $err['status'] );
                }
		$attendance_id = (int) $request->get_param( 'attendance_id' );
		$now           = current_time( 'mysql', true );
            $ok            = AttendanceRepo::set_void( $attendance_id, false, null, get_current_user_id(), $now );
		if ( $ok ) {
				Audit::log( 'attendance_unvoid', 'attendance', $attendance_id, get_current_user_id(), array() );
		}
		return new WP_REST_Response(
			array(
				'ok'            => $ok,
				'attendance_id' => $attendance_id,
				'is_void'       => 0,
			),
			200
		);
	}

		/**
		 * Add a note to an attendance record.
		 *
		 * @param WP_REST_Request $request Request.
		 *
		 * @return WP_REST_Response
		 */
	public function note( WP_REST_Request $request ): WP_REST_Response {
		$nonce = $request->get_header( 'x-wp-nonce' );
                if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
                        $err = ErrorHelper::from_wp_error(
                                new WP_Error( 'invalid_nonce', __( 'Invalid nonce', 'foodbank-manager' ), array( 'status' => 401 ) )
                        );
                        return new WP_REST_Response( $err['body'], $err['status'] );
                }
		$attendance_id = (int) $request->get_param( 'attendance_id' );
		$note          = Helpers::sanitize_text( (string) $request->get_param( 'note' ) );
		$now           = current_time( 'mysql', true );
            $ok            = AttendanceRepo::add_note( $attendance_id, get_current_user_id(), $note, $now );
		if ( $ok ) {
			Audit::log(
				'attendance_note',
				'attendance',
				$attendance_id,
				get_current_user_id(),
				array( 'preview' => mb_substr( $note, 0, 100 ) )
			);
				$id = (int) $GLOBALS['wpdb']->insert_id;
				return new WP_REST_Response(
					array(
						'ok'      => true,
						'note_id' => $id,
					),
					200
				);
		}
                $err = ErrorHelper::from_wp_error(
                        new WP_Error( 'internal', __( 'Internal error', 'foodbank-manager' ), array( 'status' => 500 ) )
                );
                return new WP_REST_Response( $err['body'], $err['status'] );
        }
}
