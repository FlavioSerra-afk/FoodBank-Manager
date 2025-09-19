<?php
/**
 * Integration coverage for staff check-in flows.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Integration;

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
final class CheckinFlowTest extends TestCase {
        private \wpdb $wpdb;

        private MembersRepository $members;

        private TokenService $tokens;

        protected function setUp(): void {
                parent::setUp();

                $this->wpdb              = new \wpdb();
                $GLOBALS['wpdb']         = $this->wpdb;
                $GLOBALS['fbm_transients'] = array();
                unset( $GLOBALS['fbm_last_token_lookup_hash'] );

                $_SERVER['REMOTE_ADDR'] = '198.51.100.10';

                $this->members = new MembersRepository( $this->wpdb );
                $this->tokens  = new TokenService( new TokenRepository( $this->wpdb ) );
        }

        protected function tearDown(): void {
                CheckinService::set_current_time_override( null );

                unset( $GLOBALS['wpdb'], $GLOBALS['fbm_transients'], $GLOBALS['fbm_last_token_lookup_hash'], $_SERVER['REMOTE_ADDR'] );

                parent::tearDown();
        }

        public function test_qr_checkin_success_duplicate_and_throttle(): void {
                $member = $this->createMember( 'FBM-TST01', 'success@example.com' );

                $this->setCurrentTime( '2023-08-17 12:15:00' );
                $first = $this->processPayload( array( 'code' => $member['token'] ) );
                $this->assertSame( 'success', $first['status'] );
                $this->assertSame( 'Collection recorded.', $first['message'] );
                $this->assertFalse( $first['requires_override'] );

                $this->setCurrentTime( '2023-08-17 12:16:00' );
                $duplicate = $this->processPayload( array( 'code' => $member['token'] ) );
                $this->assertSame( 'already', $duplicate['status'] );
                $this->assertStringContainsString( 'Member already collected today.', $duplicate['message'] );
                $this->assertNotNull( $duplicate['time'] );

                for ( $i = 0; $i < 4; $i++ ) {
                        $this->setCurrentTime( sprintf( '2023-08-17 12:17:%02d', $i ) );
                        $this->processPayload( array( 'code' => $member['token'] ) );
                }

                $this->setCurrentTime( '2023-08-17 12:18:00' );
                $throttled = $this->processPayload( array( 'code' => $member['token'] ) );
                $this->assertSame( 'throttled', $throttled['status'] );
                $this->assertSame( 'Please wait a moment before trying again.', $throttled['message'] );
        }

        public function test_recent_warning_requires_override_and_override_records_audit(): void {
                $member = $this->createMember( 'FBM-TST02', 'recent@example.com' );

                $this->setCurrentTime( '2023-08-08 12:05:00' );
                $this->processPayload( array( 'code' => $member['token'] ) );

                $this->setCurrentTime( '2023-08-12 11:15:00' );
                $warning = $this->processPayload( array( 'code' => $member['token'] ) );
                $this->assertSame( 'recent_warning', $warning['status'] );
                $this->assertTrue( $warning['requires_override'] );
                $this->assertNotNull( $warning['time'] );

                $this->setCurrentTime( '2023-08-12 11:20:00' );
                $override = $this->processPayload(
                        array(
                                'manual_code'   => $member['reference'],
                                'method'        => 'manual',
                                'override'      => true,
                                'override_note' => 'Manager approved override',
                        )
                );

                $this->assertSame( 'success', $override['status'] );
                $this->assertFalse( $override['requires_override'] );
                $this->assertSame( 'Collection recorded.', $override['message'] );

                $this->assertCount( 1, $this->wpdb->attendance_overrides );
                $audit = reset( $this->wpdb->attendance_overrides );
                $this->assertSame( $member['reference'], $audit['member_reference'] );
                $this->assertSame( 'Manager approved override', $audit['override_note'] );
        }

        public function test_revoked_token_and_invalid_payload_responses(): void {
                $member = $this->createMember( 'FBM-TST03', 'revoked@example.com' );

                $this->tokens->revoke( $member['id'] );

                $this->setCurrentTime( '2023-08-17 13:00:00' );
                $revoked = $this->processPayload( array( 'code' => $member['token'] ) );
                $this->assertSame( 'revoked', $revoked['status'] );
                $this->assertSame( 'This code has been revoked.', $revoked['message'] );

                $this->assertArrayHasKey( 'fbm_last_token_lookup_hash', $GLOBALS );
                $hash = $GLOBALS['fbm_last_token_lookup_hash'];
                $this->assertIsString( $hash );
                $this->assertSame( 64, strlen( $hash ) );
                $this->assertNotSame( $member['token'], $hash );

                $invalid = $this->processPayload( array( 'code' => 'invalid-token' ) );
                $this->assertSame( 'invalid', $invalid['status'] );
                $this->assertSame( 'Enter a valid collection code.', $invalid['message'] );
                $this->assertNull( $invalid['member_ref'] );
        }

        /**
         * Create an active member with an issued token.
         *
         * @return array{id:int,reference:string,token:string}
         */
        private function createMember( string $reference, string $email ): array {
                $member_id = $this->members->insert_active_member( $reference, 'Taylor', 'Q', $email, 3, null );
                $issuance  = $this->tokens->issue_with_details( $member_id, array( 'context' => 'integration-test' ) );

                return array(
                        'id'        => $member_id,
                        'reference' => $reference,
                        'token'     => $issuance['token'],
                );
        }

        /**
         * Invoke the check-in processor with a payload.
         *
         * @param array<string,mixed> $payload Payload overrides.
         * @param string|null         $fingerprint Optional fingerprint.
         *
         * @return array<string,mixed>
         */
        private function processPayload( array $payload, ?string $fingerprint = '198.51.100.10' ): array {
                return CheckinController::process_checkin_payload( $payload, $this->wpdb, $fingerprint, 1 );
        }

        /**
         * Set the deterministic current time for the check-in service.
         */
        private function setCurrentTime( string $value ): void {
                CheckinService::set_current_time_override(
                        new DateTimeImmutable( $value, new DateTimeZone( 'Europe/London' ) )
                );
        }
}
