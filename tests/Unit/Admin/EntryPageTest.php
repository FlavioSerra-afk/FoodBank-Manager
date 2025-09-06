<?php
declare(strict_types=1);

namespace {
    use PHPUnit\Framework\TestCase;

    if (!function_exists('wp_unslash')) {
        function wp_unslash($value) {
            return is_array($value) ? array_map('wp_unslash', $value) : stripslashes((string) $value);
        }
    }
    if (!function_exists('wp_die')) {
        function wp_die($msg = ''): void { throw new \RuntimeException((string) $msg); }
    }
    if (!function_exists('wp_nonce_field')) {
        function wp_nonce_field($action, $name): void { echo '<input type="hidden" name="'.$name.'" value="n" />'; }
    }
    if (!function_exists('esc_html__')) {
        function esc_html__(string $text, string $domain = 'default'): string { return $text; }
    }
    if (!function_exists('esc_html_e')) {
        function esc_html_e(string $text, string $domain = 'default'): void { echo $text; }
    }
    if (!function_exists('esc_html')) {
        function esc_html($text) { return (string) $text; }
    }
    if (!function_exists('sanitize_file_name')) {
        function sanitize_file_name($f) { return preg_replace('/[^A-Za-z0-9.\-_]/', '', (string) $f); }
    }
}


namespace {
    use PHPUnit\Framework\TestCase;
    use FoodBankManager\Admin\EntryPage;

    /**
     * @runTestsInSeparateProcesses
     */
    final class EntryPageTest extends TestCase {
        public static array $caps = [];

        protected function setUp(): void {
            self::$caps = ['fb_manage_database' => true, 'fb_view_sensitive' => false];
            fbm_test_trust_nonces(true);
            $_GET = $_POST = $_SERVER = $_REQUEST = [];
            $GLOBALS['fbm_test_screen_id'] = 'foodbank_page_fbm_database';
            $GLOBALS['fbm_caps'] = self::$caps;
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
            self::$caps['fb_view_sensitive'] = true;
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
            self::$caps['fb_view_sensitive'] = true;
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

