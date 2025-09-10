<?php
declare(strict_types=1);

namespace Tests\Unit\Database;

use FoodBankManager\Database\Columns;
use FoodBankManager\Security\Crypto;

final class ColumnsTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        if ( ! defined( 'FBM_KEK_BASE64' ) ) {
            define( 'FBM_KEK_BASE64', base64_encode( str_repeat( 'K', SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES ) ) );
        }
        if ( ! extension_loaded( 'sodium' ) && ! class_exists( 'ParagonIE_Sodium_Compat' ) ) {
            $this->markTestSkipped( 'libsodium missing' );
        }
    }

    /** @return array<string,mixed> */
    private function sampleRow(): array {
        $pii  = array( 'email' => 'john@example.com', 'last_name' => 'Doe' );
        $blob = Crypto::encryptSensitive( $pii );
        return array(
            'id' => 1,
            'created_at' => '2025-09-01 00:00:00',
            'status' => 'new',
            'has_files' => 0,
            'data_json' => wp_json_encode( array( 'first_name' => 'John', 'postcode' => 'AB1 2CD' ) ),
            'pii_encrypted_blob' => $blob,
        );
    }

    public function testMasking(): void {
        $row  = $this->sampleRow();
        $cols = Columns::for_admin_list( false );
        $email = $cols['email']['value']( $row );
        $this->assertSame( 'j***@example.com', $email );
        $postcode = $cols['postcode']['value']( $row );
        $this->assertSame( 'AB* 2**', $postcode );
    }

    public function testUnmask(): void {
        $row  = $this->sampleRow();
        $cols = Columns::for_admin_list( true );
        $email = $cols['email']['value']( $row );
        $this->assertSame( 'john@example.com', $email );
        $name = $cols['name']['value']( $row );
        $this->assertSame( 'John Doe', $name );
    }
}
