<?php
/**
 * Coverage for the FoodBank Manager privacy eraser integration.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Privacy;

use FoodBankManager\Privacy\Eraser;
use FoodBankManager\Registration\MembersRepository;
use PHPUnit\Framework\TestCase;
use function get_option;

/**
 * @covers \FoodBankManager\Privacy\Eraser
 */
final class EraserTest extends TestCase {
	protected function setUp(): void {
			parent::setUp();

			$GLOBALS['fbm_options'] = array(
				'fbm_mail_failures'        => array(
					array(
						'member_id' => 1,
						'reason'    => 'bounce',
					),
					array(
						'member_id' => 2,
						'reason'    => 'other',
					),
				),
				'fbm_members_action_audit' => array(
					array(
						'member_id' => 1,
						'action'    => 'note',
					),
					array(
						'member_id' => 3,
						'action'    => 'other',
					),
				),
			);

			$GLOBALS['wpdb']          = new \wpdb();
			$GLOBALS['wpdb']->members = array(
				1 => array(
					'id'                  => 1,
					'member_reference'    => 'FBM0001',
					'email'               => 'rosa@example.test',
					'first_name'          => 'Rosa',
					'last_initial'        => 'L',
					'status'              => 'active',
					'household_size'      => 3,
					'activated_at'        => '2024-01-02 12:00:00',
					'consent_recorded_at' => '2024-01-01 09:05:00',
				),
			);

			$GLOBALS['wpdb']->tokens = array(
				1 => array(
					'member_id'  => 1,
					'token_hash' => 'hash-value',
					'issued_at'  => '2024-01-01 09:00:00',
					'version'    => 'v1',
					'meta'       => '{}',
					'revoked_at' => null,
				),
			);

			$GLOBALS['wpdb']->attendance = array(
				1 => array(
					'id'               => 1,
					'member_reference' => 'FBM0001',
					'collected_at'     => '2024-03-04 12:10:00',
					'collected_date'   => '2024-03-04',
					'method'           => 'qr',
					'note'             => 'Sensitive note',
					'recorded_by'      => 42,
				),
			);

			$GLOBALS['wpdb']->attendance_overrides = array(
				1 => array(
					'id'               => 1,
					'attendance_id'    => 1,
					'member_reference' => 'FBM0001',
					'override_by'      => 99,
					'override_note'    => 'Manual override note',
					'override_at'      => '2024-03-05 09:00:00',
				),
			);
	}

	public function test_erase_anonymizes_member_and_related_records(): void {
			$result = Eraser::erase( 'rosa@example.test' );

			$this->assertTrue( $result['items_removed'] );
			$this->assertTrue( $result['done'] );
			$this->assertFalse( $result['items_retained'] );

			$member = $GLOBALS['wpdb']->members[1];
			$this->assertSame( 'erased-1', $member['member_reference'] );
			$this->assertSame( 'Erased', $member['first_name'] );
			$this->assertSame( 'X', $member['last_initial'] );
			$this->assertSame( MembersRepository::STATUS_REVOKED, $member['status'] );
			$this->assertSame( 'deleted-member-1@example.invalid', $member['email'] );
			$this->assertNull( $member['activated_at'] ?? null );
			$this->assertNull( $member['consent_recorded_at'] ?? null );

			$attendance = $GLOBALS['wpdb']->attendance[1];
			$this->assertSame( 'erased-1', $attendance['member_reference'] );
			$this->assertSame( '', $attendance['note'] );

			$override = $GLOBALS['wpdb']->attendance_overrides[1];
			$this->assertSame( 'erased-1', $override['member_reference'] );
			$this->assertSame( '', $override['override_note'] );

			$this->assertArrayNotHasKey( 1, $GLOBALS['wpdb']->tokens );

			$audit_log = get_option( 'fbm_members_action_audit' );
			$this->assertCount( 1, $audit_log );
			$this->assertSame( 3, $audit_log[0]['member_id'] );

			$mail_failures = get_option( 'fbm_mail_failures' );
			$this->assertCount( 1, $mail_failures );
			$this->assertSame( 2, $mail_failures[0]['member_id'] );
	}

	public function test_unknown_identifier_returns_noop_response(): void {
			$result = Eraser::erase( 'unknown@example.test' );

			$this->assertFalse( $result['items_removed'] );
			$this->assertFalse( $result['items_retained'] );
			$this->assertTrue( $result['done'] );
			$this->assertSame( array(), $result['messages'] );
	}
}
