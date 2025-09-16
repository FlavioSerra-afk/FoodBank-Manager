<?php
/**
 * Check-in controller tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Rest;

use DateTimeImmutable;
use DateTimeZone;
use FoodBankManager\Attendance\CheckinService;
use FoodBankManager\Registration\MembersRepository;
use FoodBankManager\Rest\CheckinController;
use FoodBankManager\Token\TokenRepository;
use FoodBankManager\Token\TokenService;
use PHPUnit\Framework\TestCase;

/**
 * @covers \FoodBankManager\Rest\CheckinController
 */
final class CheckinControllerTest extends TestCase {
        private \wpdb $wpdb;

        protected function setUp(): void {
                parent::setUp();

                $this->wpdb        = new \wpdb();
                $GLOBALS['wpdb'] = $this->wpdb;

                CheckinService::set_current_time_override(
                        new DateTimeImmutable( '2023-08-17 12:00:00', new DateTimeZone( 'Europe/London' ) )
                );

                $GLOBALS['fbm_current_caps'] = array(
                        'fbm_checkin' => true,
                        'fbm_manage'  => false,
                );
        }

        protected function tearDown(): void {
                CheckinService::set_current_time_override( null );

                unset( $GLOBALS['fbm_current_caps'] );
                unset( $GLOBALS['wpdb'] );

                parent::tearDown();
        }

        public function test_handle_checkin_records_attendance_with_valid_token(): void {
                $members_repository = new MembersRepository( $this->wpdb );
                $member_id          = $members_repository->insert_active_member( 'FBM100', 'Jane', 'D', 'jane@example.com', 2 );

                $this->assertIsInt( $member_id );

                $token_service = new TokenService( new TokenRepository( $this->wpdb ) );
                $token         = $token_service->issue( $member_id );

                $request  = new \WP_REST_Request(
                        array(
                                'token'  => $token,
                                'method' => 'qr',
                        )
                );
                $response = CheckinController::handle_checkin( $request );

                $this->assertInstanceOf( \WP_REST_Response::class, $response );

                $data = $response->get_data();

                $this->assertSame( CheckinService::STATUS_SUCCESS, $data['status'] );
                $this->assertArrayHasKey( 'member_ref', $data );
                $this->assertSame( 'FBM100', $data['member_ref'] );
        }

        public function test_handle_checkin_records_attendance_with_manual_code(): void {
                $members_repository = new MembersRepository( $this->wpdb );
                $member_id          = $members_repository->insert_active_member( 'FBM200', 'John', 'Q', 'john@example.com', 1 );

                $this->assertIsInt( $member_id );

                $request  = new \WP_REST_Request(
                        array(
                                'manual_code' => ' FBM200 ',
                                'method'      => 'manual',
                        )
                );
                $response = CheckinController::handle_checkin( $request );

                $this->assertInstanceOf( \WP_REST_Response::class, $response );

                $data = $response->get_data();

                $this->assertSame( CheckinService::STATUS_SUCCESS, $data['status'] );
                $this->assertArrayHasKey( 'member_ref', $data );
                $this->assertSame( 'FBM200', $data['member_ref'] );
        }

        public function test_handle_checkin_reports_duplicate_day_on_second_collection(): void {
                $members_repository = new MembersRepository( $this->wpdb );
                $member_id          = $members_repository->insert_active_member( 'FBM600', 'Avery', 'B', 'avery@example.com', 2 );

                $this->assertIsInt( $member_id );

                $initial_request  = new \WP_REST_Request(
                        array(
                                'manual_code' => 'FBM600',
                                'method'      => 'manual',
                        )
                );
                $initial_response = CheckinController::handle_checkin( $initial_request );

                $this->assertInstanceOf( \WP_REST_Response::class, $initial_response );

                $first_data = $initial_response->get_data();

                $this->assertSame( CheckinService::STATUS_SUCCESS, $first_data['status'] );

                $second_request  = new \WP_REST_Request(
                        array(
                                'manual_code' => 'FBM600',
                                'method'      => 'manual',
                        )
                );
                $second_response = CheckinController::handle_checkin( $second_request );

                $this->assertInstanceOf( \WP_REST_Response::class, $second_response );

                $second_data = $second_response->get_data();

                $this->assertSame( CheckinService::STATUS_DUPLICATE_DAY, $second_data['status'] );
                $this->assertSame( 'Member already collected today.', $second_data['message'] );
                $this->assertSame( 'FBM600', $second_data['member_ref'] );
        }

        public function test_handle_checkin_rejects_requests_outside_collection_window(): void {
                $members_repository = new MembersRepository( $this->wpdb );
                $member_id          = $members_repository->insert_active_member( 'FBM500', 'Quinn', 'L', 'quinn@example.com', 1 );

                $this->assertIsInt( $member_id );

                CheckinService::set_current_time_override(
                        new DateTimeImmutable( '2023-08-16 10:00:00', new DateTimeZone( 'Europe/London' ) )
                );

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
                $this->assertSame( 'Collections are only available on Thursdays between 11:00 and 14:30.', $data['message'] );
                $this->assertArrayHasKey( 'time', $data );
                $this->assertNull( $data['time'] );
        }

        public function test_handle_checkin_rejects_revoked_token(): void {
                $members_repository = new MembersRepository( $this->wpdb );
                $member_id          = $members_repository->insert_active_member( 'FBM300', 'Casey', 'R', 'casey@example.com', 3 );

                $this->assertIsInt( $member_id );

                $token_service = new TokenService( new TokenRepository( $this->wpdb ) );
                $token         = $token_service->issue( $member_id );

                $this->assertTrue( $token_service->revoke( $member_id ) );

                $request  = new \WP_REST_Request( array( 'token' => $token ) );
                $response = CheckinController::handle_checkin( $request );

                $this->assertInstanceOf( \WP_Error::class, $response );
                $this->assertSame( 'fbm_invalid_token', $response->get_error_code() );
        }

        public function test_handle_checkin_rejects_inactive_token(): void {
                $members_repository = new MembersRepository( $this->wpdb );
                $member_id          = $members_repository->insert_active_member( 'FBM350', 'Harper', 'T', 'harper@example.com', 2 );

                $this->assertIsInt( $member_id );

                $this->wpdb->members[ $member_id ]['status'] = 'inactive';

                $token_service = new TokenService( new TokenRepository( $this->wpdb ) );
                $token         = $token_service->issue( $member_id );

                $request  = new \WP_REST_Request( array( 'token' => $token ) );
                $response = CheckinController::handle_checkin( $request );

                $this->assertInstanceOf( \WP_Error::class, $response );
                $this->assertSame( 'fbm_inactive_member', $response->get_error_code() );
        }

        public function test_handle_checkin_rejects_inactive_manual_code(): void {
                $members_repository = new MembersRepository( $this->wpdb );
                $member_id          = $members_repository->insert_active_member( 'FBM400', 'Morgan', 'S', 'morgan@example.com', 4 );

                $this->assertIsInt( $member_id );

                $this->wpdb->members[ $member_id ]['status'] = 'inactive';

                $request  = new \WP_REST_Request( array( 'manual_code' => 'FBM400' ) );
                $response = CheckinController::handle_checkin( $request );

                $this->assertInstanceOf( \WP_Error::class, $response );
                $this->assertSame( 'fbm_inactive_member', $response->get_error_code() );
        }

        public function test_handle_checkin_rejects_override_without_permission(): void {
                $members_repository = new MembersRepository( $this->wpdb );
                $member_id          = $members_repository->insert_active_member( 'FBM910', 'Robin', 'A', 'robin@example.com', 2 );

                $this->assertIsInt( $member_id );

                $request = new \WP_REST_Request(
                        array(
                                'manual_code'   => 'FBM910',
                                'override'      => '1',
                                'override_note' => 'Emergency collection needed.',
                        )
                );

                $response = CheckinController::handle_checkin( $request );

                $this->assertInstanceOf( \WP_Error::class, $response );
                $this->assertSame( 'fbm_override_forbidden', $response->get_error_code() );
        }

        public function test_handle_checkin_requires_note_for_override(): void {
                $members_repository = new MembersRepository( $this->wpdb );
                $member_id          = $members_repository->insert_active_member( 'FBM920', 'Ash', 'B', 'ash@example.com', 3 );

                $this->assertIsInt( $member_id );

                $GLOBALS['fbm_current_caps']['fbm_manage'] = true;

                $request = new \WP_REST_Request(
                        array(
                                'manual_code' => 'FBM920',
                                'override'    => true,
                        )
                );

                $response = CheckinController::handle_checkin( $request );

                $this->assertInstanceOf( \WP_Error::class, $response );
                $this->assertSame( 'fbm_override_note_required', $response->get_error_code() );
        }

        public function test_handle_checkin_records_override_and_logs_audit(): void {
                $members_repository = new MembersRepository( $this->wpdb );
                $member_id          = $members_repository->insert_active_member( 'FBM930', 'Sky', 'C', 'sky@example.com', 1 );

                $this->assertIsInt( $member_id );

                $GLOBALS['fbm_current_caps']['fbm_manage'] = true;

                $initial_request = new \WP_REST_Request( array( 'manual_code' => 'FBM930' ) );
                $initial_response = CheckinController::handle_checkin( $initial_request );

                $this->assertInstanceOf( \WP_REST_Response::class, $initial_response );

                CheckinService::set_current_time_override(
                        new DateTimeImmutable( '2023-08-24 11:59:00', new DateTimeZone( 'Europe/London' ) )
                );

                $override_request = new \WP_REST_Request(
                        array(
                                'manual_code'   => 'FBM930',
                                'override'      => '1',
                                'override_note' => 'Urgent need approved.',
                        )
                );

                $override_response = CheckinController::handle_checkin( $override_request );

                $this->assertInstanceOf( \WP_REST_Response::class, $override_response );

                $data = $override_response->get_data();

                $this->assertSame( CheckinService::STATUS_SUCCESS, $data['status'] );
                $this->assertSame( 'Collection recorded.', $data['message'] );

                $this->assertNotEmpty( $this->wpdb->attendance_overrides );
                $overrides     = array_values( $this->wpdb->attendance_overrides );
                $latest_audit = $overrides[ count( $overrides ) - 1 ];

                $this->assertSame( 'FBM930', $latest_audit['member_reference'] );
                $this->assertSame( 1, $latest_audit['override_by'] );
                $this->assertSame( 'Urgent need approved.', $latest_audit['override_note'] );
                $this->assertArrayHasKey( 'override_at', $latest_audit );
        }
}
