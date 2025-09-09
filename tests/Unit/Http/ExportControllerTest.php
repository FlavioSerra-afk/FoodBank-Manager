<?php
declare(strict_types=1);

namespace FoodBankManager\Tests\Unit\Http {
    use \BaseTestCase;
    use Tests\Support\Rbac;
    use Tests\Support\Exceptions\FbmDieException;
    use FBM\Http\ExportController;

    final class ExportControllerTest extends BaseTestCase {
        protected function setUp(): void {
            parent::setUp();
            Rbac::grantManager();
            $GLOBALS['fbm_user_caps']['fbm_export'] = true;
            fbm_seed_nonce('unit-seed');
            if (!function_exists('fbm_return_false')) {
                function fbm_return_false() { return false; }
            }
            add_filter('fbm_http_exit', 'fbm_return_false');
            if (!class_exists('FoodBankManager\\Database\\ApplicationsRepo', false)) {
                require_once __DIR__ . '/../../Support/ApplicationsRepoStub.php';
            }
        }

        protected function tearDown(): void {
            remove_filter('fbm_http_exit', 'fbm_return_false');
            parent::tearDown();
        }

        public function testEntryPdf(): void {
            $_GET = array(
                'action'   => 'fbm_export_entry_pdf',
                'id'       => 2,
                '_wpnonce' => fbm_nonce('fbm_export_entry_pdf'),
            );
            $_REQUEST = $_GET;
            ob_start();
            ExportController::handle();
            ob_end_clean();
            $sent = $GLOBALS['__fbm_sent_headers'] ?? array();
            $this->assertNotEmpty($sent);
        }

        public function testEntryPdfDenied(): void {
            Rbac::revokeAll();
            $_GET = array(
                'action'   => 'fbm_export_entry_pdf',
                'id'       => 1,
                '_wpnonce' => fbm_nonce('fbm_export_entry_pdf'),
            );
            $_REQUEST = $_GET;
            $this->expectException(FbmDieException::class);
            ExportController::handle();
        }

        public function testEntriesZip(): void {
            $_GET = array(
                'action'   => 'fbm_export_entries_pdf_zip',
                'ids'      => '1,2',
                '_wpnonce' => fbm_nonce('fbm_export_entries_pdf_zip'),
            );
            $_REQUEST = $_GET;
            ob_start();
            ExportController::handle();
            $body = ob_get_clean();
            $this->assertStringStartsWith('PK', (string) $body);
            $sent = $GLOBALS['__fbm_sent_headers'] ?? array();
            $found = false;
            foreach ($sent as $h) {
                if (str_contains($h, 'application/zip')) {
                    $found = true;
                }
            }
            $this->assertTrue($found);
        }

        public function testDashboardXlsx(): void {
            require_once __DIR__ . '/../../Support/DashboardExportControllerStubs.php';
            $_GET = array(
                'action'   => 'fbm_export_dashboard_xlsx',
                '_wpnonce' => fbm_nonce('fbm_export_dashboard_xlsx'),
            );
            $_REQUEST = $_GET;
            ob_start();
            ExportController::handle();
            $body = ob_get_clean();
            $this->assertStringStartsWith('PK', (string) $body);
            $sent = $GLOBALS['__fbm_sent_headers'] ?? array();
            $found = false;
            foreach ($sent as $h) {
                if (str_contains($h, 'spreadsheetml.sheet')) {
                    $found = true;
                }
            }
            $this->assertTrue($found);
        }
    }
}
