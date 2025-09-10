<?php
declare(strict_types=1);

namespace Tests\Unit\Database;

use FoodBankManager\Admin\DatabasePage;
use FoodBankManager\Database\Columns;
use FoodBankManager\Security\Crypto;

final class DatabaseExportTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        if ( ! defined( 'FBM_KEK_BASE64' ) ) {
            define( 'FBM_KEK_BASE64', base64_encode( str_repeat( 'K', SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES ) ) );
        }
        if ( ! extension_loaded( 'sodium' ) && ! class_exists( 'ParagonIE_Sodium_Compat' ) ) {
            $this->markTestSkipped( 'libsodium missing' );
        }
    }

    /** @return array<int,array<string,mixed>> */
    private function rows(): array {
        $pii  = array( 'email' => 'john@example.com', 'last_name' => 'Doe' );
        $blob = Crypto::encryptSensitive( $pii );
        return array(
            array(
                'id' => 1,
                'created_at' => '2025-09-01 00:00:00',
                'status' => 'new',
                'has_files' => 0,
                'data_json' => wp_json_encode( array( 'first_name' => 'John', 'postcode' => 'AB1 2CD' ) ),
                'pii_encrypted_blob' => $blob,
            ),
        );
    }

    public function testBuildExportRowsRespectsSelectedColumns(): void {
        $rows = $this->rows();
        $defs = Columns::for_admin_list( false );
        $sel_defs = array(
            'id' => $defs['id'],
            'email' => $defs['email'],
        );
        $ref = new \ReflectionClass( DatabasePage::class );
        $m = $ref->getMethod( 'build_export_rows' );
        $m->setAccessible( true );
        $out = $m->invoke( null, $rows, $sel_defs );
        $this->assertSame( array( 'id' => '1', 'email' => 'j***@example.com' ), $out[0] );
    }
}
