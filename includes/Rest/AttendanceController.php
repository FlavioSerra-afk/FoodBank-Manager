<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Rest;

use WP_REST_Request;
use WP_REST_Response;
use FoodBankManager\Auth\Permissions;
use FoodBankManager\Security\Helpers;
use FoodBankManager\Attendance\TokenService;
use FoodBankManager\Attendance\AttendanceRepo;
use FoodBankManager\Attendance\Policy;
use FoodBankManager\Core\Options;
use wpdb;

class AttendanceController {

        public function register_routes(): void {
                register_rest_route(
                        'pcc-fb/v1',
                        '/attendance/checkin',
                        array(
                                'methods'             => 'POST',
                                'callback'            => array( $this, 'checkin' ),
                                'permission_callback' => array( $this, 'check_write_permissions' ),
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
                                'permission_callback' => array( $this, 'check_write_permissions' ),
                                'args'                => array(
                                        'application_id' => array(
                                                'type'     => 'integer',
                                                'required' => true,
                                        ),
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
                                        'application_id' => array(
                                                'type'     => 'integer',
                                                'required' => true,
                                        ),
                                        'from' => array(
                                                'type'     => 'string',
                                                'required' => false,
                                        ),
                                        'to' => array(
                                                'type'     => 'string',
                                                'required' => false,
                                        ),
                                ),
                        )
                );
        }

        public function check_write_permissions(): bool {
                return Permissions::user_can( 'attendance_checkin' );
        }

        public function check_view_permissions(): bool {
                return Permissions::user_can( 'attendance_view' );
        }

        public function checkin( WP_REST_Request $request ): WP_REST_Response {
                $nonce = $request->get_header( 'x-wp-nonce' );
                if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
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

                $policy_days = (int) Options::get( 'policy_days' );
                $last        = AttendanceRepo::lastPresent( $application_id );
                $now         = current_time( 'mysql', true );
                $override    = $request->get_param( 'override' );
                $override_ok = is_array( $override ) && ! empty( $override['allowed'] );
                if ( Policy::is_breach( $last, $policy_days, $now ) && ! $override_ok ) {
                        return new WP_REST_Response(
                                array(
                                        'policy_warning' => array(
                                                'rule_days'       => $policy_days,
                                                'last_attended_at'=> $last,
                                        ),
                                ),
                                409
                        );
                }

                $event_id = (int) $request->get_param( 'event_id' );
                $type     = Helpers::sanitize_text( (string) $request->get_param( 'type' ) );
                if ( $type === '' || ! in_array( $type, Options::get( 'attendance_types' ), true ) ) {
                        $type = 'in_person';
                }
                $method  = Helpers::sanitize_text( (string) $request->get_param( 'method' ) );
                if ( $method === '' ) {
                        $method = 'manual';
                }
                $note = '';
                if ( $override_ok ) {
                        $note = Helpers::sanitize_text( (string) ( $override['note'] ?? '' ) );
                }

                global $wpdb;
                $ip     = $_SERVER['REMOTE_ADDR'] ?? '';
                $ip_bin = $ip !== '' ? @inet_pton( $ip ) : null; // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
                $wpdb->insert(
                        $wpdb->prefix . 'fb_attendance',
                        array(
                                'form_id'            => 0,
                                'application_id'     => $application_id,
                                'event_id'           => $event_id ?: null,
                                'attendance_at'      => $now,
                                'status'             => 'present',
                                'type'               => $type,
                                'method'             => $method,
                                'recorded_by_user_id'=> get_current_user_id(),
                                'notes'              => $note !== '' ? $note : null,
                                'token_hash'         => $token !== '' ? hash( 'sha256', $token ) : null,
                                'source_ip'          => $ip_bin,
                                'created_at'         => $now,
                                'updated_at'         => $now,
                        ),
                        array( '%d','%d','%d','%s','%s','%s','%d','%s','%s','%s','%s','%s' )
                );
                $attendance_id = (int) $wpdb->insert_id;

                return new WP_REST_Response(
                        array(
                                'attendance_id'    => $attendance_id,
                                'application_id'   => $application_id,
                                'status'           => 'present',
                                'attendance_at'    => $now,
                                'policy_overridden'=> $override_ok,
                        ),
                        200
                );
        }

        public function noshow( WP_REST_Request $request ): WP_REST_Response {
                $nonce = $request->get_header( 'x-wp-nonce' );
                if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
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
                $event_id = (int) $request->get_param( 'event_id' );
                $type     = Helpers::sanitize_text( (string) $request->get_param( 'type' ) );
                if ( $type === '' || ! in_array( $type, Options::get( 'attendance_types' ), true ) ) {
                        $type = 'in_person';
                }
                $reason = Helpers::sanitize_text( (string) $request->get_param( 'reason' ) );

                global $wpdb;
                $now = current_time( 'mysql', true );
                $wpdb->insert(
                        $wpdb->prefix . 'fb_attendance',
                        array(
                                'form_id'            => 0,
                                'application_id'     => $application_id,
                                'event_id'           => $event_id ?: null,
                                'attendance_at'      => $now,
                                'status'             => 'no_show',
                                'type'               => $type,
                                'method'             => 'manual',
                                'recorded_by_user_id'=> get_current_user_id(),
                                'notes'              => $reason !== '' ? $reason : null,
                                'created_at'         => $now,
                                'updated_at'         => $now,
                        ),
                        array( '%d','%d','%d','%s','%s','%s','%d','%s','%s','%s' )
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

        public function timeline( WP_REST_Request $request ): WP_REST_Response {
                $application_id = (int) $request->get_param( 'application_id' );
                if ( $application_id === 0 ) {
                        return new WP_REST_Response( array(), 200 );
                }
                $from = (string) $request->get_param( 'from' );
                $to   = (string) $request->get_param( 'to' );
                global $wpdb;
                $att    = $wpdb->prefix . 'fb_attendance';
                $events = $wpdb->prefix . 'fb_events';
                $users  = $wpdb->users;
                $where  = array( 't.application_id = %d' );
                $params = array( $application_id );
                if ( $from !== '' ) {
                        $where[] = 't.attendance_at >= %s';
                        $params[] = $from;
                }
                if ( $to !== '' ) {
                        $where[] = 't.attendance_at <= %s';
                        $params[] = $to;
                }
                $where_sql = implode( ' AND ', $where );
                $sql       = "SELECT t.attendance_at,t.status,t.type,t.method,t.notes,u.display_name,e.title AS event_title FROM {$att} t LEFT JOIN {$users} u ON u.ID=t.recorded_by_user_id LEFT JOIN {$events} e ON e.id=t.event_id WHERE {$where_sql} ORDER BY t.attendance_at DESC LIMIT 50";
                $rows      = $wpdb->get_results( $wpdb->prepare( $sql, $params ), 'ARRAY_A' ) ?: array();
                $records   = array();
                foreach ( $rows as $row ) {
                        $records[] = array(
                                'attendance_at' => $row['attendance_at'],
                                'event'         => $row['event_title'] ?? '',
                                'type'          => $row['type'],
                                'status'        => $row['status'],
                                'method'        => $row['method'],
                                'manager'       => $row['display_name'] ?? '',
                                'override'      => $row['notes'] !== null && $row['notes'] !== '',
                                'note'          => $row['notes'],
                        );
                }
                return new WP_REST_Response( array( 'records' => $records ), 200 );
        }
}
