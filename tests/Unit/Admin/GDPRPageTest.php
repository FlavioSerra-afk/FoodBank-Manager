<?php
declare(strict_types=1);

namespace FBM\Exports;

class SarExporterStub {
    public static array $last = array();
    public static function stream( array $subject, bool $masked, string $name ): void {
        self::$last = array( 'masked' => $masked, 'subject' => $subject, 'name' => $name );
        throw new \RuntimeException( 'stream' );
    }
}

namespace Tests\Unit\Admin;

use FBM\Admin\GDPRPage;
use Tests\Support\Exceptions\FbmDieException;
use Tests\Support\Rbac;

/**
 */
/** @runTestsInSeparateProcesses */
final class GDPRPageTest extends \BaseTestCase {
        protected function setUp(): void {
            parent::setUp();
            Rbac::grantManager();
            fbm_test_trust_nonces( true );
            fbm_test_set_request_nonce();
            if ( ! class_exists( 'FoodBankManager\\Database\\ApplicationsRepo', false ) ) {
                require_once __DIR__ . '/../../Support/ApplicationsRepoStub.php';
            }
        if ( ! class_exists( 'FoodBankManager\\Attendance\\AttendanceRepo', false ) ) {
            require_once __DIR__ . '/../../Support/AttendanceRepoStub.php';
        }
        if ( ! class_exists( 'FBM\\Mail\\LogRepo', false ) ) {
            require_once __DIR__ . '/../../Support/LogRepoStub.php';
        }
        if ( ! class_exists( 'FBM\\Tests\\Support\\WPDBStub', false ) ) {
            require_once __DIR__ . '/../../Support/WPDBStub.php';
        }
        $GLOBALS['wpdb'] = new \FBM\Tests\Support\WPDBStub();
        if ( ! defined( 'ABSPATH' ) ) {
            define( 'ABSPATH', __DIR__ );
        }
            if ( ! defined( 'FBM_PATH' ) ) {
                define( 'FBM_PATH', dirname( __DIR__, 3 ) . '/' );
            }
            if ( ! class_exists( '\\FBM\\Exports\\SarExporter', false ) ) {
                class_alias( \FBM\Exports\SarExporterStub::class, '\\FBM\\Exports\\SarExporter' );
            }
        }

        public function testRenderDenied(): void {
            Rbac::revokeAll();
            ob_start();
            GDPRPage::render();
            $html = (string) ob_get_clean();
            $this->assertStringContainsString( '<div class="wrap fbm-admin">', $html );
            $this->assertStringContainsString( 'You do not have permission to access this page.', $html );
        }

        public function testSearchPreview(): void {
            $_GET['email'] = 'user@example.com';
            ob_start();
            GDPRPage::render();
            $html = (string) ob_get_clean();
            $this->assertStringContainsString( 'Applications: 1', $html );
        }

        public function testExportRequiresNonce(): void {
            fbm_test_trust_nonces( false );
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST['fbm_action']       = 'export';
            $_REQUEST                  = $_POST;
            $this->expectException( FbmDieException::class );
            GDPRPage::route();
        }

        public function testMaskedByDefault(): void {
            fbm_seed_nonce( 'unit-seed' );
            fbm_test_set_request_nonce( 'fbm_gdpr_export', '_fbm_nonce' );
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST['fbm_action']       = 'export';
            $_POST['app_id']           = '5';
            $_REQUEST                  = $_POST;
            try {
                GDPRPage::route();
            } catch ( \RuntimeException $e ) {
                $this->assertSame( 'stream', $e->getMessage() );
            }
            $this->assertTrue( \FBM\Exports\SarExporter::$last['masked'] );
        }

        public function testUnmaskedWithCapability(): void {
            Rbac::grantAdmin();
            fbm_seed_nonce( 'unit-seed' );
            fbm_test_set_request_nonce( 'fbm_gdpr_export', '_fbm_nonce' );
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST['fbm_action']       = 'export';
            $_POST['app_id']           = '5';
            $_REQUEST                  = $_POST;
            try {
                GDPRPage::route();
            } catch ( \RuntimeException $e ) {
                $this->assertSame( 'stream', $e->getMessage() );
            }
            $this->assertFalse( \FBM\Exports\SarExporter::$last['masked'] );
        }
    }

