<?php
/**
 * Members PII adapter tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Crypto;

use FoodBankManager\Crypto\Adapters\MembersPiiAdapter;
use FoodBankManager\Crypto\Crypto;
use FoodBankManager\Crypto\EncryptionSettings;
use PHPUnit\Framework\TestCase;

/**
 * @covers \FoodBankManager\Crypto\Adapters\MembersPiiAdapter
 */
final class MembersPiiAdapterTest extends TestCase {
        private \wpdb $wpdb;

        protected function setUp(): void {
                parent::setUp();

                $this->wpdb             = new \wpdb();
                $GLOBALS['wpdb']        = $this->wpdb;
                $GLOBALS['fbm_options'] = array();
        }

        protected function tearDown(): void {
                unset( $GLOBALS['wpdb'], $GLOBALS['fbm_options'], $GLOBALS['fbm_deleted_options'] );

                EncryptionSettings::update_encrypt_new_writes( false );

                parent::tearDown();
        }

        public function test_migrate_encrypts_plaintext_rows(): void {
                $this->seedMembers();

                $adapter = new MembersPiiAdapter( $this->wpdb );
                $result  = $adapter->migrate( 10, false );

                $this->assertSame( 2, $result['changed'] );
                $this->assertTrue( Crypto::is_envelope( $this->wpdb->members[1]['first_name'] ?? '' ) );
                $this->assertTrue( Crypto::is_envelope( $this->wpdb->members[1]['last_initial'] ?? '' ) );
        }

        public function test_rotate_reencrypts_batches_and_clears_progress(): void {
                $this->seedMembers();

                $adapter = new MembersPiiAdapter( $this->wpdb );
                $adapter->migrate( 10, false );

                $first   = $adapter->rotate( 1, false );
                $progress = get_option( 'fbm_encryption_progress_members_pii', array() );

                $this->assertFalse( $first['complete'] );
                $this->assertSame( $this->wpdb->members[1]['id'], $first['cursor'] );
                $this->assertIsArray( $progress );
                $this->assertSame( 'rotate', $progress['mode'] ?? '' );

                $second = $adapter->rotate( 10, false );

                $this->assertTrue( $second['complete'] );
                $this->assertSame( array(), get_option( 'fbm_encryption_progress_members_pii', array() ) );
        }

        public function test_encrypt_new_values_honours_setting(): void {
                $this->seedMembers();

                $adapter = new MembersPiiAdapter( $this->wpdb );

                EncryptionSettings::update_encrypt_new_writes( true );
                $adapter->encrypt_new_values( 3, array( 'first_name' => 'Charlie', 'last_initial' => 'D' ) );

                $this->assertTrue( Crypto::is_envelope( $this->wpdb->members[3]['first_name'] ?? '' ) );
                $this->assertTrue( Crypto::is_envelope( $this->wpdb->members[3]['last_initial'] ?? '' ) );
        }

        public function test_decrypt_row_returns_plaintext(): void {
                $envelope = Crypto::encrypt( 'Delta', 'wp_fbm_members', 'first_name', '4' );
                $row      = array(
                        'id'          => 4,
                        'first_name'  => $envelope,
                        'last_initial'=> Crypto::encrypt( 'E', 'wp_fbm_members', 'last_initial', '4' ),
                );

                $adapter = new MembersPiiAdapter( $this->wpdb );
                $decrypted = $adapter->decrypt_row( $row );

                $this->assertSame( 'Delta', $decrypted['first_name'] );
                $this->assertSame( 'E', $decrypted['last_initial'] );
        }

        private function seedMembers(): void {
                $this->wpdb->members[1] = array(
                        'id'           => 1,
                        'first_name'   => 'Alice',
                        'last_initial' => 'B',
                );

                $this->wpdb->members[2] = array(
                        'id'           => 2,
                        'first_name'   => 'Bridget',
                        'last_initial' => 'C',
                );

                $this->wpdb->members[3] = array(
                        'id'           => 3,
                        'first_name'   => 'Charlie',
                        'last_initial' => 'D',
                );
        }
}
