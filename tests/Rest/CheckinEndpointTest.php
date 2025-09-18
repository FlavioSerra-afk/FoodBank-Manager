<?php // phpcs:ignoreFile
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
use FoodBankManager\Token\TokenRepository;
use FoodBankManager\Token\TokenService;
use PHPUnit\Framework\TestCase;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use function esc_html__;
use function update_option;

/**
 * @covers \FoodBankManager\Rest\CheckinController
 * @covers \FoodBankManager\Attendance\CheckinService
 */
final class CheckinEndpointTest extends TestCase {
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
                );

                $GLOBALS['fbm_test_nonces'] = array(
                        'wp_rest' => 'valid-nonce',
                );

                $_SERVER['REMOTE_ADDR'] = '198.51.100.25';

                Schedule::set_current_window_override( null );
                CheckinService::set_current_time_override(
                        new DateTimeImmutable( '2023-08-17 12:00:00', new DateTimeZone( 'Europe/London' ) )
                );
        }

        protected function tearDown(): void {
                CheckinService::set_current_time_override( null );
                Schedule::set_current_window_override( null );

                unset(
                        $GLOBALS['wpdb'],
                        $GLOBALS['fbm_current_caps'],
                        $GLOBALS['fbm_test_nonces'],
                        $GLOBALS['fbm_options'],
                        $GLOBALS['fbm_transients'],
                        $_SERVER['REMOTE_ADDR']
                );

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

        public function test_verify_permissions_requires_capability(): void {
                $GLOBALS['fbm_current_caps']['fbm_checkin'] = false;

                $request = new WP_REST_Request();
                $request->set_param( '_wpnonce', 'valid-nonce' );

                $result = CheckinController::verify_permissions( $request );

                $this->assertInstanceOf( WP_Error::class, $result );
                $this->assertSame( 'fbm_forbidden', $result->get_error_code() );
        }

        public function test_verify_permissions_requires_nonce(): void {
                $request = new WP_REST_Request();

                $result = CheckinController::verify_permissions( $request );

                $this->assertInstanceOf( WP_Error::class, $result );
                $this->assertSame( 'fbm_invalid_nonce', $result->get_error_code() );
        }

        public function test_verify_permissions_passes_with_valid_nonce_and_capability(): void {
                $request = new WP_REST_Request();
                $request->set_param( '_wpnonce', 'valid-nonce' );

                $result = CheckinController::verify_permissions( $request );

                $this->assertTrue( $result );
        }

        public function test_handle_checkin_records_success_response(): void {
                $member = $this->create_member_with_token( 'FBM600' );
                $request = new WP_REST_Request(
                        array(
                                'code' => $member['token'],
                        )
                );

                $response = CheckinController::handle_checkin( $request );

                $this->assertInstanceOf( WP_REST_Response::class, $response );
                $this->assertSame( 200, $response->get_status() );

                $data = $response->get_data();

                $this->assertSame( 'success', $data['status'] );
                $this->assertSame( esc_html__( 'Collection recorded.', 'foodbank-manager' ), $data['message'] );
                $this->assertSame( 'FBM600', $data['member_ref'] );
                $this->assertSame( '2023-08-17T11:00:00+00:00', $data['time'] );
                $this->assertSame( $this->default_window(), $data['window'] );
                $this->assertSame( Schedule::window_labels( $this->default_window() ), $data['window_labels'] );
                $this->assertSame( Schedule::window_notice( $this->default_window() ), $data['window_notice'] );
                $this->assertFalse( (bool) $data['requires_override'] );
                $this->assertCount( 1, $this->wpdb->attendance );
        }

        public function test_handle_checkin_is_idempotent_per_day(): void {
                $member  = $this->create_member_with_token( 'FBM601' );
                $request = new WP_REST_Request(
                        array(
                                'code' => $member['token'],
                        )
                );

                $first_response = CheckinController::handle_checkin( $request );
                $this->assertSame( 'success', $first_response->get_data()['status'] );

                $duplicate_response = CheckinController::handle_checkin( $request );

                $this->assertInstanceOf( WP_REST_Response::class, $duplicate_response );
                $this->assertSame( 200, $duplicate_response->get_status() );

                $duplicate = $duplicate_response->get_data();

                $this->assertSame( 'already', $duplicate['status'] );
                $this->assertSame( esc_html__( 'Member already collected today.', 'foodbank-manager' ), $duplicate['message'] );
                $this->assertSame( 'FBM601', $duplicate['member_ref'] );
                $this->assertSame( '2023-08-17T11:00:00+00:00', $duplicate['time'] );
                $this->assertSame( $this->default_window(), $duplicate['window'] );
                $this->assertFalse( (bool) $duplicate['requires_override'] );
                $this->assertCount( 1, $this->wpdb->attendance );
        }

        public function test_handle_checkin_throttles_after_limit(): void {
                $tokens = array();

                for ( $i = 0; $i < 5; $i++ ) {
                        $tokens[] = $this->create_member_with_token( sprintf( 'FBMT%d', $i ) );
                }

                foreach ( $tokens as $token_data ) {
                        $response = CheckinController::handle_checkin(
                                new WP_REST_Request(
                                        array(
                                                'code' => $token_data['token'],
                                        )
                                )
                        );

                        $this->assertSame( 'success', $response->get_data()['status'] );
                }

                $extra    = $this->create_member_with_token( 'FBMT-extra' );
                $throttled = CheckinController::handle_checkin(
                        new WP_REST_Request(
                                array(
                                        'code' => $extra['token'],
                                )
                        )
                );

                $this->assertInstanceOf( WP_REST_Response::class, $throttled );
                $this->assertSame( 429, $throttled->get_status() );

                $data = $throttled->get_data();

                $this->assertSame( 'throttled', $data['status'] );
                $this->assertSame( 'FBMT-extra', $data['member_ref'] );
                $this->assertNull( $data['time'] );
                $this->assertCount( 5, $this->wpdb->attendance );
        }

        public function test_handle_checkin_rejects_revoked_token(): void {
                $member = $this->create_member_with_token( 'FBM700' );

                $repository = new TokenRepository( $this->wpdb );
                $repository->revoke_member( $member['id'], '2023-08-16 12:00:00' );

                $response = CheckinController::handle_checkin(
                        new WP_REST_Request(
                                array(
                                        'code' => $member['token'],
                                )
                        )
                );

                $this->assertInstanceOf( WP_REST_Response::class, $response );
                $this->assertSame( 200, $response->get_status() );

                $data = $response->get_data();

                $this->assertSame( 'revoked', $data['status'] );
                $this->assertSame( esc_html__( 'This code has been revoked.', 'foodbank-manager' ), $data['message'] );
        }

        public function test_handle_checkin_rejects_inactive_member(): void {
                $member = $this->create_member_with_token( 'FBM701' );

                $this->wpdb->members[ $member['id'] ]['status'] = MembersRepository::STATUS_PENDING;

                $response = CheckinController::handle_checkin(
                        new WP_REST_Request(
                                array(
                                        'code' => $member['token'],
                                )
                        )
                );

                $this->assertInstanceOf( WP_REST_Response::class, $response );
                $this->assertSame( 200, $response->get_status() );

                $data = $response->get_data();

                $this->assertSame( 'revoked', $data['status'] );
                $this->assertSame( esc_html__( 'This member is not currently active.', 'foodbank-manager' ), $data['message'] );
                $this->assertSame( 'FBM701', $data['member_ref'] );
        }

        public function test_handle_checkin_rejects_invalid_token_format(): void {
                $response = CheckinController::handle_checkin(
                        new WP_REST_Request(
                                array(
                                        'code' => 'invalid',
                                )
                        )
                );

                $this->assertInstanceOf( WP_REST_Response::class, $response );
                $this->assertSame( 200, $response->get_status() );

                $data = $response->get_data();

                $this->assertSame( 'invalid', $data['status'] );
        }

        public function test_handle_checkin_requires_token_parameter(): void {
                $response = CheckinController::handle_checkin( new WP_REST_Request() );

                $this->assertInstanceOf( WP_REST_Response::class, $response );
                $this->assertSame( 200, $response->get_status() );

                $data = $response->get_data();

                $this->assertSame( 'invalid', $data['status'] );
        }

        public function test_handle_checkin_records_manual_code_successfully(): void {
                $member  = $this->create_member_with_token( 'FBM750' );
                $request = new WP_REST_Request(
                        array(
                                'manual_code' => $member['reference'],
                                'method'      => 'manual',
                        )
                );

                $response = CheckinController::handle_checkin( $request );

                $this->assertInstanceOf( WP_REST_Response::class, $response );
                $this->assertSame( 200, $response->get_status() );

                $data = $response->get_data();

                $this->assertSame( 'success', $data['status'] );
                $this->assertSame( 'FBM750', $data['member_ref'] );
                $this->assertSame( esc_html__( 'Collection recorded.', 'foodbank-manager' ), $data['message'] );
                $this->assertFalse( (bool) $data['requires_override'] );
        }

        public function test_handle_checkin_requires_override_before_recent_warning_override(): void {
                $member = $this->create_member_with_token( 'FBM751' );

                $attendance = new AttendanceRepository( $this->wpdb );
                $attendance->record(
                        $member['reference'],
                        'manual',
                        1,
                        new DateTimeImmutable( '2023-08-15 11:00:00', new DateTimeZone( 'UTC' ) ),
                        null
                );

                $warning_request = new WP_REST_Request(
                        array(
                                'manual_code' => $member['reference'],
                                'method'      => 'manual',
                        )
                );

                $warning_response = CheckinController::handle_checkin( $warning_request );
                $warning_data     = $warning_response->get_data();

                $this->assertSame( 'recent_warning', $warning_data['status'] );
                $this->assertTrue( (bool) $warning_data['requires_override'] );
                $this->assertSame( '2023-08-15T11:00:00+00:00', $warning_data['time'] );

                $override_request = new WP_REST_Request(
                        array(
                                'manual_code'   => $member['reference'],
                                'method'        => 'manual',
                                'override'      => true,
                                'override_note' => 'Manager approval',
                        )
                );

                $override_response = CheckinController::handle_checkin( $override_request );
                $override_data     = $override_response->get_data();

                $this->assertSame( 'success', $override_data['status'] );
                $this->assertFalse( (bool) $override_data['requires_override'] );
                $this->assertSame( 'FBM751', $override_data['member_ref'] );
        }

        /**
         * Create an active member with an issued token.
         *
         * @param string $reference Canonical member reference.
         *
         * @return array{id:int,reference:string,token:string}
         */
        private function create_member_with_token( string $reference ): array {
                $members = new MembersRepository( $this->wpdb );
                $member_id = $members->insert_active_member(
                        $reference,
                        'Alex',
                        'T',
                        strtolower( $reference ) . '@example.com',
                        1
                );

                $this->assertIsInt( $member_id );

                $token_service = new TokenService( new TokenRepository( $this->wpdb ) );
                $token         = $token_service->issue( $member_id );

                return array(
                        'id'        => $member_id,
                        'reference' => $reference,
                        'token'     => $token,
                );
        }
}
