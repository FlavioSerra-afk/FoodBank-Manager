<?php
/**
 * Integration coverage for encryption migration flows.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Integration;

use FoodBankManager\Crypto\Adapters\MailFailLogAdapter;
use FoodBankManager\Crypto\Adapters\MembersPiiAdapter;
use FoodBankManager\Crypto\Crypto;
use FoodBankManager\Crypto\EncryptionSettings;
use FoodBankManager\Diagnostics\MailFailureLog;
use FoodBankManager\Registration\MembersRepository;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
final class EncryptionMigrationTest extends TestCase {
        private \wpdb $wpdb;

        protected function setUp(): void {
                parent::setUp();

                $this->wpdb             = new \wpdb();
                $GLOBALS['wpdb']        = $this->wpdb;
                $GLOBALS['fbm_options'] = array();

                EncryptionSettings::update_encrypt_new_writes( false );
        }

        protected function tearDown(): void {
                unset( $GLOBALS['wpdb'], $GLOBALS['fbm_options'], $GLOBALS['fbm_deleted_options'] );

                EncryptionSettings::update_encrypt_new_writes( false );

                parent::tearDown();
        }

        public function test_migration_and_rotation_flow(): void {
                $members = new MembersRepository( $this->wpdb );
                $log     = new MailFailureLog();

                $member_id = $members->insert_pending_member( 'REF100', 'Alice', 'A', 'alice@example.com', 1 );
                $this->assertNotNull( $member_id );

                $log->record_failure( $member_id, 'REF100', 'alice@example.com', MailFailureLog::CONTEXT_REGISTRATION, MailFailureLog::ERROR_MAIL );

                $this->assertFalse( Crypto::is_envelope( $this->wpdb->members[ $member_id ]['first_name'] ?? '' ) );
                $failures = get_option( 'fbm_mail_failures', array() );
                $this->assertFalse( Crypto::is_envelope( $failures[0]['email'] ?? '' ) );

                $member_adapter = new MembersPiiAdapter( $this->wpdb );
                $mail_adapter   = new MailFailLogAdapter();

                $member_adapter->migrate( 50, false );
                $mail_adapter->migrate( 50, false );

                $this->assertTrue( Crypto::is_envelope( $this->wpdb->members[ $member_id ]['first_name'] ?? '' ) );
                $failures = get_option( 'fbm_mail_failures', array() );
                $this->assertTrue( Crypto::is_envelope( $failures[0]['email'] ?? '' ) );

                EncryptionSettings::update_encrypt_new_writes( true );

                $new_member = $members->insert_active_member( 'REF200', 'Bob', 'B', 'bob@example.com', 2 );
                $this->assertNotNull( $new_member );
                $this->assertTrue( Crypto::is_envelope( $this->wpdb->members[ $new_member ]['first_name'] ?? '' ) );

                $log->record_failure( $new_member, 'REF200', 'bob@example.com', MailFailureLog::CONTEXT_REGISTRATION, MailFailureLog::ERROR_MAIL );
                $entries = get_option( 'fbm_mail_failures', array() );
                $this->assertTrue( Crypto::is_envelope( $entries[1]['email'] ?? '' ) );

                $first_rotation = $member_adapter->rotate( 1, false );
                $this->assertFalse( $first_rotation['complete'] );
                $this->assertArrayHasKey( 'cursor', $first_rotation );

                $member_adapter->rotate( 50, false );
                $this->assertSame( array(), get_option( 'fbm_encryption_progress_members_pii', array() ) );

                $mail_first = $mail_adapter->rotate( 1, false );
                $this->assertFalse( $mail_first['complete'] );
                $mail_adapter->rotate( 50, false );
                $this->assertSame( array(), get_option( 'fbm_encryption_progress_mail_fail_log', array() ) );

                $found = $members->find( $member_id );
                $this->assertNotNull( $found );
                $this->assertSame( 'Alice', $found['first_name'] );

                $entries = $log->entries();
                $this->assertNotEmpty( $entries );
        }
}
