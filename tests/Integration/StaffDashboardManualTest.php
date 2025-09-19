<?php
/**
 * Integration coverage for the staff dashboard manual entry flow.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Integration;

use DateTimeImmutable;
use DateTimeZone;
use FoodBankManager\Attendance\AttendanceRepository;
use FoodBankManager\Attendance\CheckinService;
use FoodBankManager\Registration\MembersRepository;
use FoodBankManager\Shortcodes\StaffDashboard;
use FoodBankManager\Token\TokenRepository;
use FoodBankManager\Token\TokenService;
use PHPUnit\Framework\TestCase;

/**
 * @covers \FoodBankManager\Shortcodes\StaffDashboard
 */
final class StaffDashboardManualTest extends TestCase {
        private \wpdb $wpdb;

        private MembersRepository $members;

        private TokenService $tokens;

        private AttendanceRepository $attendance;

        protected function setUp(): void {
                parent::setUp();

                $this->wpdb                 = new \wpdb();
                $GLOBALS['wpdb']            = $this->wpdb;
                $GLOBALS['fbm_current_caps'] = array(
                        'fbm_view'    => true,
                        'fbm_checkin' => true,
                );
                $GLOBALS['fbm_test_nonces'] = array(
                        'fbm_staff_manual_entry' => 'manual-nonce',
                );
                $GLOBALS['fbm_transients']  = array();

                $_SERVER['REQUEST_METHOD'] = 'GET';

                $this->members    = new MembersRepository( $this->wpdb );
                $this->tokens     = new TokenService( new TokenRepository( $this->wpdb ) );
                $this->attendance = new AttendanceRepository( $this->wpdb );
        }

        protected function tearDown(): void {
                CheckinService::set_current_time_override( null );

                unset(
                        $GLOBALS['wpdb'],
                        $GLOBALS['fbm_current_caps'],
                        $GLOBALS['fbm_test_nonces'],
                        $GLOBALS['fbm_transients']
                );

                $_SERVER = array();
                $_POST   = array();

                parent::tearDown();
        }

        public function test_manual_submission_requires_valid_nonce(): void {
                $member = $this->createMember( 'FBM-MAN01', 'manual@example.com' );

                $this->setCurrentTime( '2023-08-17 12:10:00' );

                $_SERVER['REQUEST_METHOD'] = 'POST';
                $_POST = array(
                        'code'                   => $member['reference'],
                        'fbm_staff_manual_nonce' => 'invalid',
                );

                $html = StaffDashboard::render();
                $this->assertStringContainsString( 'Security check failed. Please try again.', $html );
        }

        public function test_manual_submission_records_success_then_handles_override_flow(): void {
                $success = $this->createMember( 'FBM-MAN02', 'manual-success@example.com' );
                $override = $this->createMember( 'FBM-MAN03', 'manual-override@example.com' );

                $this->attendance->record(
                        $override['reference'],
                        'qr',
                        2,
                        new DateTimeImmutable( '2023-08-08 11:00:00', new DateTimeZone( 'UTC' ) ),
                        'Initial collection'
                );

                // Successful manual submission.
                $this->setCurrentTime( '2023-08-17 12:15:00' );
                $_SERVER['REQUEST_METHOD'] = 'POST';
                $_POST = array(
                        'code'                   => $success['reference'],
                        'fbm_staff_manual_nonce' => 'manual-nonce',
                );

                $html = StaffDashboard::render();
                $this->assertStringContainsString( 'Collection recorded.', $html );
                $this->assertStringContainsString( 'Manual entry', $html );

                // Duplicate attempts should display already message.
                $_SERVER['REQUEST_METHOD'] = 'POST';
                $_POST = array(
                        'code'                   => $success['reference'],
                        'fbm_staff_manual_nonce' => 'manual-nonce',
                );

                $duplicate = StaffDashboard::render();
                $this->assertStringContainsString( 'Member already collected today.', $duplicate );

                // Recent warning for override scenario.
                $this->setCurrentTime( '2023-08-17 12:25:00' );
                $_SERVER['REQUEST_METHOD'] = 'POST';
                $_POST = array(
                        'code'                   => $override['reference'],
                        'fbm_staff_manual_nonce' => 'manual-nonce',
                );

                $warning = StaffDashboard::render();
                $this->assertStringContainsString( 'Only managers can continue', $warning );
                $this->assertStringContainsString( 'Confirm override', $warning );

                // Confirm override with justification.
                $_SERVER['REQUEST_METHOD'] = 'POST';
                $_POST = array(
                        'code'                   => $override['reference'],
                        'override'               => '1',
                        'override_note'          => 'Manual approval recorded',
                        'fbm_staff_manual_nonce' => 'manual-nonce',
                );

                $overrideResult = StaffDashboard::render();
                $this->assertStringContainsString( 'Collection recorded.', $overrideResult );
                $this->assertStringNotContainsString( 'Confirm override', $overrideResult );

                $this->assertCount( 1, $this->wpdb->attendance_overrides );
                $audit = reset( $this->wpdb->attendance_overrides );
                $this->assertSame( $override['reference'], $audit['member_reference'] );
                $this->assertSame( 'Manual approval recorded', $audit['override_note'] );
        }

        /**
         * Create a member with an issued token for manual entries.
         *
         * @return array{id:int,reference:string}
         */
        private function createMember( string $reference, string $email ): array {
                $member_id = $this->members->insert_active_member( $reference, 'Jordan', 'M', $email, 2, null );
                $this->tokens->issue_with_details( $member_id, array( 'context' => 'manual-test' ) );

                return array(
                        'id'        => $member_id,
                        'reference' => $reference,
                );
        }

        /**
         * Set deterministic current time for manual submissions.
         */
        private function setCurrentTime( string $value ): void {
                        CheckinService::set_current_time_override(
                                new DateTimeImmutable( $value, new DateTimeZone( 'Europe/London' ) )
                        );
        }
}
