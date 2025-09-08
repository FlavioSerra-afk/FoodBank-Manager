<?php
declare(strict_types=1);

namespace FBM\Exports {
    class SarExporter {
        public static array $last = array();
        public static function stream( array $subject, bool $masked, string $name ): void {
            self::$last = array( 'masked' => $masked, 'subject' => $subject, 'name' => $name );
            throw new \RuntimeException('stream');
        }
    }
}

namespace {
    use BaseTestCase;
    use FBM\Admin\GDPRPage;

    final class GDPRPageTest extends BaseTestCase {
        protected function setUp(): void {
            parent::setUp();
            fbm_grant_manager();
            fbm_test_trust_nonces(true);
            fbm_test_set_request_nonce();
            if (!class_exists('FoodBankManager\\Database\\ApplicationsRepo', false)) {
                require_once __DIR__ . '/../../Support/ApplicationsRepoStub.php';
            }
            if (!class_exists('FoodBankManager\\Attendance\\AttendanceRepo', false)) {
                require_once __DIR__ . '/../../Support/AttendanceRepoStub.php';
            }
            if (!class_exists('FBM\\Mail\\LogRepo', false)) {
                require_once __DIR__ . '/../../Support/LogRepoStub.php';
            }
        }

        public function testSearchPreview(): void {
            if ( ! defined( 'ABSPATH' ) ) { define( 'ABSPATH', __DIR__ ); }
            if ( ! defined( 'FBM_PATH' ) ) { define( 'FBM_PATH', dirname( __DIR__, 3 ) . '/' ); }
            $_GET['email'] = 'user@example.com';
            ob_start();
            include FBM_PATH . 'templates/admin/gdpr.php';
            $html = ob_get_clean();
            $this->assertStringContainsString( 'Applications: 1', $html );
        }

        public function testExportRequiresNonce(): void {
            fbm_test_trust_nonces(false);
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST['fbm_action'] = 'export';
            $_REQUEST            = $_POST;
            $this->expectException( \RuntimeException::class );
            GDPRPage::route();
        }

        public function testMaskedByDefault(): void {
            fbm_test_set_request_nonce('fbm_gdpr_export', '_fbm_nonce');
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST['fbm_action'] = 'export';
            $_POST['app_id'] = '5';
            $_REQUEST = $_POST;
            try {
                GDPRPage::route();
            } catch ( \RuntimeException $e ) {
                $this->assertSame( 'stream', $e->getMessage() );
            }
            $this->assertTrue( \FBM\Exports\SarExporter::$last['masked'] );
        }

        public function testUnmaskedWithCapability(): void {
            fbm_grant_admin();
            fbm_test_trust_nonces(true);
            fbm_test_set_request_nonce('fbm_gdpr_export', '_fbm_nonce');
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST['fbm_action'] = 'export';
            $_POST['app_id'] = '5';
            $_REQUEST = $_POST;
            try {
                GDPRPage::route();
            } catch ( \RuntimeException $e ) {
                $this->assertSame( 'stream', $e->getMessage() );
            }
            $this->assertFalse( \FBM\Exports\SarExporter::$last['masked'] );
        }
    }
}
