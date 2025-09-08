<?php
declare(strict_types=1);

namespace {
    use BaseTestCase;
    use FoodBankManager\Admin\EntryPage;

    /**
     * @runTestsInSeparateProcesses
     */
    final class EntryPageTest extends BaseTestCase {
        protected function setUp(): void {
            parent::setUp();
            fbm_grant_manager();
            fbm_test_trust_nonces(true);
            fbm_test_set_request_nonce('fbm_entry_view');
            $GLOBALS['fbm_test_screen_id'] = 'foodbank_page_fbm_database';
            if (function_exists('header_remove')) {
                header_remove();
            }
            if (!class_exists('FoodBankManager\\Database\\ApplicationsRepo', false)) {
                require_once __DIR__ . '/../../Support/ApplicationsRepoStub.php';
            }
            if (!defined('ABSPATH')) { define('ABSPATH', __DIR__); }
            if (!defined('FBM_PATH')) { define('FBM_PATH', dirname(__DIR__, 3) . '/'); }
        }

        public function testViewMasksEmailWithoutCapability(): void {
            fbm_test_set_request_nonce('fbm_entry_view');
            $_GET = array(
                'fbm_action' => 'view_entry',
                'entry_id'   => '1',
                '_wpnonce'   => $_POST['_wpnonce'],
            );
            $_REQUEST = $_GET;
            ob_start();
            EntryPage::handle();
            $html = ob_get_clean();
            $this->assertStringContainsString('j***@example.com', $html);
            $this->assertStringNotContainsString('john@example.com', $html);
            $this->assertStringNotContainsString('Unmask', $html);
        }

        public function testUnmaskShowsPlaintextWithCapability(): void {
            fbm_grant_admin();
            fbm_test_set_request_nonce('fbm_entry_view');
            $_GET = array(
                'fbm_action' => 'view_entry',
                'entry_id'   => '1',
                '_wpnonce'   => $_POST['_wpnonce'],
            );
            $_REQUEST = $_GET;
            ob_start();
            EntryPage::handle();
            $html = ob_get_clean();
            $this->assertStringContainsString('Unmask', $html);
            $this->assertStringContainsString('j***@example.com', $html);

            fbm_test_set_request_nonce('fbm_entry_unmask', 'fbm_nonce');
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST['fbm_action'] = 'unmask_entry';
            $_POST['fbm_nonce']  = $_POST['fbm_nonce'];
            $_REQUEST            = array_merge($_GET, $_POST);
            ob_start();
            EntryPage::handle();
            $html = ob_get_clean();
            $this->assertStringContainsString('john@example.com', $html);
        }

        public function testUnmaskDeniedWithoutNonce(): void {
            fbm_grant_admin();
            fbm_test_set_request_nonce('fbm_entry_view');
            fbm_test_trust_nonces(false);
            $_GET = array(
                'fbm_action' => 'view_entry',
                'entry_id'   => '1',
                '_wpnonce'   => $_POST['_wpnonce'],
            );
            $_REQUEST = $_GET;
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST['fbm_action'] = 'unmask_entry';
            $this->expectException(\RuntimeException::class);
            EntryPage::handle();
        }

        public function testPdfDeniedWithoutNonce(): void {
            fbm_test_set_request_nonce('fbm_entry_view');
            fbm_test_trust_nonces(false);
            $_GET = array(
                'fbm_action' => 'view_entry',
                'entry_id'   => '1',
                '_wpnonce'   => $_POST['_wpnonce'],
            );
            $_REQUEST = $_GET;
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST['fbm_action'] = 'entry_pdf';
            $this->expectException(\RuntimeException::class);
            EntryPage::handle();
        }

        public function testPdfExportHandlesEngines(): void {
            fbm_test_set_request_nonce('fbm_entry_view');
            $_GET = array(
                'fbm_action' => 'view_entry',
                'entry_id'   => '2',
                '_wpnonce'   => $_POST['_wpnonce'],
            );
            $_REQUEST = $_GET;
            fbm_test_set_request_nonce('fbm_entry_pdf', 'fbm_nonce');
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST['fbm_action'] = 'entry_pdf';
            $_POST['fbm_nonce']  = $_POST['fbm_nonce'];
            $_REQUEST            = array_merge($_GET, $_POST);
            ob_start();
            EntryPage::handle();
            $body = ob_get_clean();
            $headers = implode("\n", headers_list());
            $date = gmdate('Ymd');
            if (!class_exists('Mpdf\\Mpdf') && !class_exists('TCPDF')) {
                $this->assertStringContainsString('<h1>Entry</h1>', $body);
                $this->assertStringContainsString('Content-Type: text/html', $headers);
                $this->assertStringContainsString('filename="entry-2-' . $date . '.html"', $headers);
            } else {
                $this->assertStringContainsString('Content-Type: application/pdf', $headers);
                $this->assertStringContainsString('filename="entry-2-' . $date . '.pdf"', $headers);
            }
        }
    }
}

