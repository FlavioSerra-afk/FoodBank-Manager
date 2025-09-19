<?php
/**
 * Mail failure log adapter tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Crypto;

use FoodBankManager\Crypto\Adapters\MailFailLogAdapter;
use FoodBankManager\Crypto\Crypto;
use FoodBankManager\Crypto\EncryptionSettings;
use PHPUnit\Framework\TestCase;

/**
 * @covers \FoodBankManager\Crypto\Adapters\MailFailLogAdapter
 */
final class MailFailLogAdapterTest extends TestCase {
        protected function setUp(): void {
                parent::setUp();

                $GLOBALS['fbm_options'] = array();
        }

        protected function tearDown(): void {
                unset( $GLOBALS['fbm_options'], $GLOBALS['fbm_deleted_options'] );

                EncryptionSettings::update_encrypt_new_writes( false );

                parent::tearDown();
        }

        public function test_migrate_encrypts_plaintext_entries(): void {
                $this->seedFailures();

                $adapter = new MailFailLogAdapter();
                $result  = $adapter->migrate( 10, false );

                $this->assertSame( 2, $result['changed'] );

                $entries = get_option( 'fbm_mail_failures', array() );
                $this->assertTrue( Crypto::is_envelope( $entries[0]['email'] ?? '' ) );
                $this->assertTrue( Crypto::is_envelope( $entries[1]['email'] ?? '' ) );
        }

        public function test_rotate_rewraps_envelopes_and_tracks_progress(): void {
                $this->seedFailures();

                $adapter = new MailFailLogAdapter();
                $adapter->migrate( 10, false );

                $first = $adapter->rotate( 1, false );
                $this->assertFalse( $first['complete'] );

                $progress = get_option( 'fbm_encryption_progress_mail_fail_log', array() );
                $this->assertSame( 'rotate', $progress['mode'] ?? '' );
                $this->assertNotEmpty( $progress['processed_ids'] ?? array() );

                $second = $adapter->rotate( 10, false );
                $this->assertTrue( $second['complete'] );
                $this->assertSame( array(), get_option( 'fbm_encryption_progress_mail_fail_log', array() ) );
        }

        public function test_encrypt_email_round_trip(): void {
                $adapter = new MailFailLogAdapter();
                $envelope = $adapter->encrypt_email( '123', 'user@example.com' );

                $this->assertTrue( Crypto::is_envelope( $envelope ) );
                $this->assertSame( 'user@example.com', $adapter->decrypt_email( '123', $envelope ) );
        }

        private function seedFailures(): void {
                update_option(
                        'fbm_mail_failures',
                        array(
                                array(
                                        'id'               => 'a1',
                                        'member_id'        => 1,
                                        'member_reference' => 'REF1',
                                        'email'            => 'one@example.com',
                                ),
                                array(
                                        'id'               => 'b2',
                                        'member_id'        => 2,
                                        'member_reference' => 'REF2',
                                        'email'            => 'two@example.com',
                                ),
                        ),
                        false
                );
        }
}
