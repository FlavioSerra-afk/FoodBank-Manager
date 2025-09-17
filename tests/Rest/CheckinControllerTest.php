<?php
/**
 * Check-in endpoint behavioural tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Rest;

use DateTimeImmutable;
use DateTimeZone;
use FoodBankManager\Attendance\AttendanceRepository;
use FoodBankManager\Attendance\CheckinService;
use FoodBankManager\Core\Schedule;
use FoodBankManager\Registration\MembersRepository;
use FoodBankManager\Rest\CheckinController;
use PHPUnit\Framework\TestCase;
use function update_option;

/**
 * @covers \FoodBankManager\Rest\CheckinController
 * @covers \FoodBankManager\Attendance\CheckinService
 */
final class CheckinControllerTest extends TestCase {
        private \wpdb $wpdb;

        protected function setUp(): void {
                parent::setUp();

                unset( $GLOBALS['fbm_transients'] );

                $GLOBALS['fbm_options'] = array();
                update_option( 'fbm_schedule_window', $this->default_window() );

                $this->wpdb      = new \wpdb();
                $GLOBALS['wpdb'] = $this->wpdb;
                $GLOBALS['fbm_current_caps'] = array(
                        'fbm_checkin' => true,
                        'fbm_manage'  => false,
                );
                $GLOBALS['fbm_test_nonces'] = array();

                $_SERVER['REMOTE_ADDR'] = '198.51.100.1';

                Schedule::set_current_window_override( null );

                CheckinService::set_current_time_override(
                        new DateTimeImmutable( '2023-08-17 12:00:00', new DateTimeZone( 'Europe/London' ) )
                );
        }

        protected function tearDown(): void {
                CheckinService::set_current_time_override( null );
                Schedule::set_current_window_override( null );

                unset( $GLOBALS['wpdb'], $GLOBALS['fbm_current_caps'], $GLOBALS['fbm_test_nonces'], $GLOBALS['fbm_transients'], $_SERVER['REMOTE_ADDR'] );

                parent::tearDown();
        }

        /**
         * Provide the default schedule window for REST tests.
         *
         * @return array{day:string,start:string,end:string,timezone:string}
         */
        private function default_window(): array {
                return array(
                        'day'      => 'thursday',
                        'start'    => '11:00',
                        'end'      => '14:30',
                        'timezone' => 'Europe/London',
                );
        }

        public function test_handle_checkin_records_success_within_collection_window(): void {
                $members   = new MembersRepository( $this->wpdb );
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

                $data = $response->get_data();

                $this->assertSame( CheckinService::STATUS_SUCCESS, $data['status'] );
                $this->assertSame( 'FBM600', $data['member_ref'] );
                $this->assertNotEmpty( $data['time'] );
        }

        public function test_handle_checkin_allows_multiple_requests_before_throttle(): void {
                $members = new MembersRepository( $this->wpdb );

                for ( $i = 0; $i < 5; $i++ ) {
                        $code      = sprintf( 'FBMT%d', $i );
                        $member_id = $members->insert_active_member( $code, 'User', 'T', sprintf( 'user%d@example.com', $i ), 1 );

                        $this->assertIsInt( $member_id );

                        $request  = new \WP_REST_Request(
                                array(
                                        'manual_code' => $code,
                                        'method'      => 'manual',
                                )
                        );
                        $response = CheckinController::handle_checkin( $request );

                        $this->assertInstanceOf( \WP_REST_Response::class, $response );

                        $data = $response->get_data();
                        $this->assertSame( CheckinService::STATUS_SUCCESS, $data['status'] );
                }
        }

        public function test_handle_checkin_returns_window_metadata_when_outside_collection_hours(): void {
                $custom_window = array(
                        'day'      => 'monday',
                        'start'    => '09:00',
                        'end'      => '12:00',
                        'timezone' => 'America/New_York',
                );

                update_option( 'fbm_schedule_window', $custom_window );

                $schedule        = new Schedule();
                $expected_window = $schedule->current_window();

                CheckinService::set_current_time_override(
                        new DateTimeImmutable( '2023-08-21 08:15:00', new DateTimeZone( 'America/New_York' ) )
                );

                $members   = new MembersRepository( $this->wpdb );
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
                $this->assertArrayHasKey( 'window', $data );
                $this->assertSame( $expected_window, $data['window'] );
                $this->assertSame( Schedule::window_notice( $expected_window ), $data['message'] );
                $this->assertSame( Schedule::window_notice( $expected_window ), $data['window_notice'] );
                $this->assertSame( Schedule::window_labels( $expected_window ), $data['window_labels'] );
        }

        public function test_handle_checkin_marks_duplicate_attempts(): void {
                $members   = new MembersRepository( $this->wpdb );
                $member_id = $members->insert_active_member( 'FBM601', 'Casey', 'D', 'casey@example.com', 3 );

                $this->assertIsInt( $member_id );

                $request = new \WP_REST_Request(
                        array(
                                'manual_code' => 'FBM601',
                                'method'      => 'manual',
                        )
                );

                $first_response = CheckinController::handle_checkin( $request );

                $this->assertInstanceOf( \WP_REST_Response::class, $first_response );
                $this->assertSame( CheckinService::STATUS_SUCCESS, $first_response->get_data()['status'] );

                $duplicate_response = CheckinController::handle_checkin( $request );

                $this->assertInstanceOf( \WP_REST_Response::class, $duplicate_response );

                $duplicate = $duplicate_response->get_data();

                $this->assertSame( CheckinService::STATUS_DUPLICATE_DAY, $duplicate['status'] );
                $this->assertTrue( $duplicate['duplicate'] );
        }

        public function test_handle_checkin_returns_throttled_status_after_limit(): void {
                $members = new MembersRepository( $this->wpdb );

                for ( $i = 0; $i < 5; $i++ ) {
                        $code      = sprintf( 'FBMR%d', $i );
                        $member_id = $members->insert_active_member( $code, 'Riley', 'T', sprintf( 'riley%d@example.com', $i ), 2 );

                        $this->assertIsInt( $member_id );

                        $request  = new \WP_REST_Request(
                                array(
                                        'manual_code' => $code,
                                        'method'      => 'manual',
                                )
                        );
                        $response = CheckinController::handle_checkin( $request );
                        $this->assertInstanceOf( \WP_REST_Response::class, $response );
                        $this->assertSame( CheckinService::STATUS_SUCCESS, $response->get_data()['status'] );
                }

                $extra_member = $members->insert_active_member( 'FBMR-extra', 'Riley', 'Z', 'riley-extra@example.com', 3 );
                $this->assertIsInt( $extra_member );

                $blocked_request = new \WP_REST_Request(
                        array(
                                'manual_code' => 'FBMR-extra',
                                'method'      => 'manual',
                        )
                );

                $blocked_response = CheckinController::handle_checkin( $blocked_request );

                $this->assertInstanceOf( \WP_REST_Response::class, $blocked_response );

                $blocked_data = $blocked_response->get_data();

                $this->assertSame( CheckinService::STATUS_THROTTLED, $blocked_data['status'] );
                $this->assertSame( 429, $blocked_response->get_status() );
                $this->assertSame( 'FBMR-extra', $blocked_data['member_ref'] );
        }

        public function test_handle_checkin_requires_override_when_recent_visit_detected(): void {
                $members   = new MembersRepository( $this->wpdb );
                $member_id = $members->insert_active_member( 'FBM778', 'Morgan', 'T', 'morgan@example.com', 2 );

                $this->assertIsInt( $member_id );

                $attendance = new AttendanceRepository( $this->wpdb );
                $attendance->record(
                        'FBM778',
                        'manual',
                        5,
                        new DateTimeImmutable( '2023-08-17 11:45:00', new DateTimeZone( 'UTC' ) ),
                        'Weekly pickup'
                );

                CheckinService::set_current_time_override(
                        new DateTimeImmutable( '2023-08-24 12:29:00', new DateTimeZone( 'Europe/London' ) )
                );

                $request = new \WP_REST_Request(
                        array(
                                'manual_code' => 'FBM778',
                                'method'      => 'manual',
                        )
                );

                $response = CheckinController::handle_checkin( $request );

                $this->assertInstanceOf( \WP_REST_Response::class, $response );

                $data = $response->get_data();

                $this->assertSame( CheckinService::STATUS_RECENT_WARNING, $data['status'] );
                $this->assertTrue( $data['requires_override'] );
                $this->assertSame( 'FBM778', $data['member_ref'] );
                $this->assertCount( 1, $this->wpdb->attendance );
        }

        public function test_manager_override_records_collection_and_audit_note(): void {
                $members   = new MembersRepository( $this->wpdb );
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

                $request = new \WP_REST_Request(
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
                $this->assertNotEmpty( $data['time'] );

                $this->assertNotEmpty( $this->wpdb->attendance );
                $record = end( $this->wpdb->attendance );
                $this->assertSame( 'Emergency pickup', $record['note'] );

                $this->assertNotEmpty( $this->wpdb->attendance_overrides );
                $override = end( $this->wpdb->attendance_overrides );
                $this->assertSame( 'Emergency pickup', $override['override_note'] );
        }

        public function test_override_attempt_is_throttled_after_reaching_limit(): void {
                $GLOBALS['fbm_current_caps']['fbm_manage'] = true;

                $members = new MembersRepository( $this->wpdb );

                for ( $i = 0; $i < 5; $i++ ) {
                        $code      = sprintf( 'FBMO%d', $i );
                        $member_id = $members->insert_active_member( $code, 'Owen', 'P', sprintf( 'owen%d@example.com', $i ), 1 );

                        $this->assertIsInt( $member_id );

                        $request  = new \WP_REST_Request(
                                array(
                                        'manual_code' => $code,
                                        'method'      => 'manual',
                                )
                        );

                        $response = CheckinController::handle_checkin( $request );
                        $this->assertSame( CheckinService::STATUS_SUCCESS, $response->get_data()['status'] );
                }

                $override_member = $members->insert_active_member( 'FBMO-limit', 'Owen', 'Z', 'owen-limit@example.com', 2 );
                $this->assertIsInt( $override_member );

                $override_request = new \WP_REST_Request(
                        array(
                                'manual_code'   => 'FBMO-limit',
                                'method'        => 'manual',
                                'override'      => true,
                                'override_note' => 'Manager approved',
                        )
                );

                $override_response = CheckinController::handle_checkin( $override_request );

                $this->assertInstanceOf( \WP_REST_Response::class, $override_response );

                $override_data = $override_response->get_data();

                $this->assertSame( CheckinService::STATUS_THROTTLED, $override_data['status'] );
                $this->assertSame( 429, $override_response->get_status() );
        }
}
