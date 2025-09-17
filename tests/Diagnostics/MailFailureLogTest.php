<?php
/**
 * Mail failure log tests.
 *
 * @package FoodBankManager\Tests
 */

declare(strict_types=1);

namespace FBM\Tests\Diagnostics;

use FoodBankManager\Diagnostics\MailFailureLog;
use PHPUnit\Framework\TestCase;

/**
 * @covers \FoodBankManager\Diagnostics\MailFailureLog
 */
final class MailFailureLogTest extends TestCase {
        protected function setUp(): void {
                parent::setUp();
                unset( $GLOBALS['fbm_options'] );
        }

        public function test_records_failure_with_redacted_email(): void {
                $log = new MailFailureLog();

                $log->record_failure( 12, 'FBM-ABCD', 'User@example.com', MailFailureLog::CONTEXT_REGISTRATION, MailFailureLog::ERROR_MAIL );

                $entries = $log->entries();

                $this->assertCount( 1, $entries );

                $entry = $entries[0];

                $this->assertSame( 'FBM-ABCD', $entry['member_reference'] );
                $this->assertSame( 'u**r@example.com', $entry['email'] );
                $this->assertSame( MailFailureLog::CONTEXT_REGISTRATION, $entry['context'] );
                $this->assertSame( MailFailureLog::ERROR_MAIL, $entry['error'] );
        }

        public function test_rate_limit_blocks_until_interval_passed(): void {
                $log = new MailFailureLog();

                $log->record_failure( 44, 'FBM-1234', 'case@example.com', MailFailureLog::CONTEXT_REGISTRATION, MailFailureLog::ERROR_MAIL );

                $entries = $log->entries();
                $this->assertTrue( $log->can_attempt( $entries[0] ) );

                $log->note_attempt( $entries[0]['id'] );

                $entry_after_attempt = $log->find( $entries[0]['id'] );
                $this->assertNotNull( $entry_after_attempt );
                $this->assertFalse( $log->can_attempt( $entry_after_attempt ) );

                $stored = get_option( 'fbm_mail_failures', array() );
                $stored[0]['last_attempt_at'] = time() - MailFailureLog::rate_limit_interval() - 1;
                update_option( 'fbm_mail_failures', $stored );

                $refreshed = $log->find( $entries[0]['id'] );
                $this->assertNotNull( $refreshed );
                $this->assertTrue( $log->can_attempt( $refreshed ) );
        }

        public function test_resolve_member_removes_entries(): void {
                $log = new MailFailureLog();

                $log->record_failure( 1, 'FBM-1111', 'alpha@example.com', MailFailureLog::CONTEXT_REGISTRATION, MailFailureLog::ERROR_MAIL );
                $log->record_failure( 2, 'FBM-2222', 'beta@example.com', MailFailureLog::CONTEXT_REGISTRATION, MailFailureLog::ERROR_MAIL );

                $log->resolve_member( 1 );

                $entries = $log->entries();

                $this->assertCount( 1, $entries );
                $this->assertSame( 2, $entries[0]['member_id'] );
        }
}
