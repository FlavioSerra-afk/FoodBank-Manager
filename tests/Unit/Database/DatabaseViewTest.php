<?php
declare(strict_types=1);

namespace Tests\Unit\Database;

use FoodBankManager\Admin\EntryPage;
use Tests\Support\Rbac;

final class DatabaseViewTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        if ( ! defined( 'FBM_PATH' ) ) {
            define( 'FBM_PATH', dirname( __DIR__, 3 ) . '/' );
        }
        if ( ! class_exists( '\\FoodBankManager\\Database\\ApplicationsRepo', false ) ) {
            require_once __DIR__ . '/../../Support/ApplicationsRepoStub.php';
        }
    }

    public function testViewMasksByDefault(): void {
        Rbac::grantManager();
        $_GET = array( 'entry_id' => '1', '_wpnonce' => wp_create_nonce( 'fbm_entry_view' ) );
        $_SERVER['REQUEST_METHOD'] = 'GET';
        ob_start();
        ( new EntryPage() )->render();
        $html = (string) ob_get_clean();
        $this->assertStringContainsString( 'j***@example.com', $html );
    }

    public function testViewUnmasksWithCapability(): void {
        Rbac::grantAdmin();
        $_GET  = array( 'entry_id' => '1', '_wpnonce' => wp_create_nonce( 'fbm_entry_view' ) );
        $_POST = array(
            'fbm_action' => 'unmask_entry',
            'fbm_nonce'  => wp_create_nonce( 'fbm_entry_unmask' ),
        );
        $_SERVER['REQUEST_METHOD'] = 'POST';
        ob_start();
        ( new EntryPage() )->render();
        $html = (string) ob_get_clean();
        $this->assertStringContainsString( 'john@example.com', $html );
    }
}
