<?php
/**
 * Check-in controller tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Rest;

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
        }

        protected function tearDown(): void {
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
}
