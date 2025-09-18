<?php
/**
 * Staff dashboard manual entry integration tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Shortcodes;

use DateTimeImmutable;
use DateTimeZone;
use FoodBankManager\Attendance\AttendanceRepository;
use FoodBankManager\Attendance\CheckinService;
use FoodBankManager\Core\Schedule;
use FoodBankManager\Registration\MembersRepository;
use FoodBankManager\Shortcodes\StaffDashboard;
use FoodBankManager\Token\Token;
use FoodBankManager\Token\TokenRepository;
use PHPUnit\Framework\TestCase;

/**
 * Validates server-side manual entry fallbacks for the staff dashboard.
 *
 * @covers \FoodBankManager\Shortcodes\StaffDashboard
 * @covers \FoodBankManager\Attendance\CheckinService
 */
final class StaffDashboardManualEntryTest extends TestCase {
		/**
		 * Prepare shared fixtures before each test.
		 */
	protected function setUp(): void {
			parent::setUp();

			$GLOBALS['fbm_user_logged_in'] = true;
			$GLOBALS['fbm_current_caps']   = array(
				'fbm_view'    => true,
				'fbm_checkin' => true,
			);

			$GLOBALS['fbm_test_nonces'] = array(
				'fbm_staff_manual_entry' => 'fbm_staff_manual_entry-nonce',
			);

			global $wpdb;
			$wpdb                      = new \wpdb(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Test fixture setup.
			$GLOBALS['fbm_transients'] = array();

			Schedule::set_current_window_override(
				array(
					'day'      => 'thursday',
					'start'    => '11:00',
					'end'      => '14:30',
					'timezone' => 'Europe/London',
				)
			);

			CheckinService::set_current_time_override(
				new DateTimeImmutable( '2023-08-17 12:15:00', new DateTimeZone( 'Europe/London' ) )
			);
	}

		/**
		 * Clean up shared overrides.
		 */
	protected function tearDown(): void {
			CheckinService::set_current_time_override( null );
			Schedule::set_current_window_override( null );

			unset( $_POST );
			unset( $_SERVER['REQUEST_METHOD'] );

			parent::tearDown();
	}

		/**
		 * Records a collection via manual entry when the token is valid.
		 */
	public function test_manual_entry_records_collection_successfully(): void {
			$token = $this->issue_token_for_reference( 'FBM900' );

			$this->simulate_post( $token );

			$output = StaffDashboard::render();

			$this->assertStringContainsString( 'Collection recorded.', $output );
			$this->assertStringContainsString( 'fbm-staff-dashboard__manual-status--success', $output );
	}

		/**
		 * Surfaces duplicate feedback when the member has already collected today.
		 */
	public function test_manual_entry_returns_duplicate_feedback_when_already_checked_in(): void {
			$reference = 'FBM901';
			$token     = $this->issue_token_for_reference( $reference );

			$this->record_manual_collection( $reference );

			$this->simulate_post( $token );

			$output = StaffDashboard::render();

			$this->assertStringContainsString( 'Member already collected today.', $output );
			$this->assertStringContainsString( 'fbm-staff-dashboard__manual-status--already', $output );
	}

		/**
		 * Provides validation feedback when the submitted code is invalid.
		 */
	public function test_manual_entry_rejects_invalid_code(): void {
			$this->simulate_post( 'invalid-token' );

			$output = StaffDashboard::render();

			$this->assertStringContainsString( 'Enter a valid collection code.', $output );
			$this->assertStringContainsString( 'fbm-staff-dashboard__manual-status--invalid', $output );
	}

		/**
		 * Issue a token for a known active member reference.
		 *
		 * @param string $reference Member reference string.
		 */
	private function issue_token_for_reference( string $reference ): string {
		/** WordPress database fixture.
		 *
		 * @var \wpdb $wpdb
		 */
		$wpdb = $GLOBALS['wpdb'];

			$members = new MembersRepository( $wpdb );
			$member  = $members->insert_active_member( $reference, 'Test', 'A', $reference . '@example.com', 1 );

			$this->assertIsInt( $member );

			$token_repository = new TokenRepository( $wpdb );
			$token_service    = new Token( $token_repository );
			$issued           = $token_service->issue( $member );

			return $issued['payload'];
	}

		/**
		 * Seed a manual collection to force duplicate detection.
		 *
		 * @param string $reference Member reference string.
		 */
	private function record_manual_collection( string $reference ): void {
		/** WordPress database fixture.
		 *
		 * @var \wpdb $wpdb
		 */
		$wpdb = $GLOBALS['wpdb'];

			$attendance = new AttendanceRepository( $wpdb );
			$service    = new CheckinService( $attendance, new Schedule() );

			$result = $service->record( $reference, 'manual', 1 );

			$this->assertSame( CheckinService::STATUS_SUCCESS, $result['status'] );
	}

		/**
		 * Populate superglobals to simulate a manual form submission.
		 *
		 * @param string $code Manual code payload.
		 */
	private function simulate_post( string $code ): void {
			$_SERVER['REQUEST_METHOD'] = 'POST';
			$_POST                     = array(
				'code'                   => $code,
				'fbm_staff_manual_nonce' => 'fbm_staff_manual_entry-nonce',
			);
	}
}
