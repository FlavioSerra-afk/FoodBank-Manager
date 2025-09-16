<?php
/**
 * Check-in endpoint tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Rest;

use DateTimeImmutable;
use DateTimeZone;
use FoodBankManager\Attendance\AttendanceRepository;
use FoodBankManager\Attendance\CheckinService;
use FoodBankManager\Registration\MembersRepository;
use FoodBankManager\Rest\CheckinController;
use PHPUnit\Framework\TestCase;

/**
 * @covers \FoodBankManager\Rest\CheckinController
 */
final class CheckinEndpointTest extends TestCase {
        private \wpdb $wpdb;

        protected function setUp(): void {
                parent::setUp();

                $this->wpdb        = new \wpdb();
                $GLOBALS['wpdb']   = $this->wpdb;
                $GLOBALS['fbm_current_caps'] = array(
                        'fbm_checkin' => true,
                        'fbm_manage'  => false,
                );
                $GLOBALS['fbm_test_nonces'] = array();

                CheckinService::set_current_time_override(
                        new DateTimeImmutable( '2023-08-17 12:00:00', new DateTimeZone( 'Europe/London' ) )
                );
        }

        protected function tearDown(): void {
                CheckinService::set_current_time_override( null );

                unset( $GLOBALS['fbm_current_caps'], $GLOBALS['fbm_test_nonces'], $GLOBALS['wpdb'] );

                parent::tearDown();
        }

        public function test_verify_permissions_requires_capability_and_nonce(): void {
                $GLOBALS['fbm_current_caps']['fbm_checkin'] = false;

                $request = new \WP_REST_Request();
                $result  = CheckinController::verify_permissions( $request );

                $this->assertInstanceOf( \WP_Error::class, $result );
                $this->assertSame( 'fbm_forbidden', $result->get_error_code() );

                $GLOBALS['fbm_current_caps']['fbm_checkin'] = true;

                $result = CheckinController::verify_permissions( new \WP_REST_Request() );
                $this->assertInstanceOf( \WP_Error::class, $result );
                $this->assertSame( 'fbm_invalid_nonce', $result->get_error_code() );

                $request = new \WP_REST_Request();
                $request->set_header( 'X-WP-Nonce', 'rest-nonce' );

                $GLOBALS['fbm_test_nonces']['wp_rest'] = 'rest-nonce';

                $this->assertTrue( CheckinController::verify_permissions( $request ) );
        }

        public function test_handle_checkin_enforces_unique_daily_collection(): void {
                $members = new MembersRepository( $this->wpdb );
                $member_id = $members->insert_active_member( 'FBM600', 'Avery', 'B', 'avery@example.com', 2 );

                $this->assertIsInt( $member_id );

                $request  = new \WP_REST_Request(
                        array(
                                'manual_code' => 'FBM600',
                                'method'      => 'manual',
                        )
                );
                $response = CheckinController::handle_checkin( $request );

                $this->assertInstanceOf( \WP_REST_Response::class, $response );
                $first = $response->get_data();

                $this->assertSame( CheckinService::STATUS_SUCCESS, $first['status'] );

                $duplicate_request  = new \WP_REST_Request(
                        array(
                                'manual_code' => 'FBM600',
                                'method'      => 'manual',
                        )
                );
                $duplicate_response = CheckinController::handle_checkin( $duplicate_request );

                $this->assertInstanceOf( \WP_REST_Response::class, $duplicate_response );
                $duplicate = $duplicate_response->get_data();

                $this->assertSame( CheckinService::STATUS_DUPLICATE_DAY, $duplicate['status'] );
                $this->assertSame( 'Member already collected today.', $duplicate['message'] );
        }

        public function test_handle_checkin_accepts_override_with_note_to_bypass_weekly_limit(): void {
                $members = new MembersRepository( $this->wpdb );
                $member_id = $members->insert_active_member( 'FBM777', 'Robin', 'S', 'robin@example.com', 3 );

                $this->assertIsInt( $member_id );

                $attendance = new AttendanceRepository( $this->wpdb );
                $attendance->record(
                        'FBM777',
                        'manual',
                        1,
                        new DateTimeImmutable( '2023-08-10 12:00:00', new DateTimeZone( 'UTC' ) ),
                        'Initial visit'
                );

                $GLOBALS['fbm_current_caps']['fbm_manage'] = true;

                CheckinService::set_current_time_override(
                        new DateTimeImmutable( '2023-08-17 12:30:00', new DateTimeZone( 'Europe/London' ) )
                );

                $request  = new \WP_REST_Request(
                        array(
                                'manual_code'   => 'FBM777',
                                'method'        => 'manual',
                                'override'      => true,
                                'override_note' => 'Emergency pickup',
                        )
                );
                $response = CheckinController::handle_checkin( $request );

                $this->assertInstanceOf( \WP_REST_Response::class, $response );

                $data = $response->get_data();

                $this->assertSame( CheckinService::STATUS_SUCCESS, $data['status'] );
                $this->assertSame( 'FBM777', $data['member_ref'] );
                $this->assertSame( 'Collection recorded.', $data['message'] );
                $this->assertNotEmpty( $data['time'] );

                $this->assertNotEmpty( $this->wpdb->attendance );
                $record = end( $this->wpdb->attendance );
                $this->assertSame( 'Emergency pickup', $record['note'] );

                $this->assertNotEmpty( $this->wpdb->attendance_overrides );
                $override = end( $this->wpdb->attendance_overrides );
                $this->assertSame( 'Emergency pickup', $override['override_note'] );
        }

        public function test_handle_checkin_rejects_requests_outside_collection_window(): void {
                CheckinService::set_current_time_override(
                        new DateTimeImmutable( '2023-08-16 10:00:00', new DateTimeZone( 'Europe/London' ) )
                );

                $members = new MembersRepository( $this->wpdb );
                $member_id = $members->insert_active_member( 'FBM500', 'Quinn', 'L', 'quinn@example.com', 1 );

                $this->assertIsInt( $member_id );

                $request  = new \WP_REST_Request(
                        array(
                                'manual_code' => 'FBM500',
                                'method'      => 'manual',
                        )
                );
                $response = CheckinController::handle_checkin( $request );

                $this->assertInstanceOf( \WP_REST_Response::class, $response );

                $data = $response->get_data();

                $this->assertSame( CheckinService::STATUS_OUT_OF_WINDOW, $data['status'] );
                $this->assertSame( 'FBM500', $data['member_ref'] );
        }
}
