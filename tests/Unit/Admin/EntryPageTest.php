<?php
declare(strict_types=1);

namespace Tests\Unit\Admin;

use FBM\Admin\EntryPage;
use Tests\Support\Exceptions\FbmDieException;
use Tests\Support\Rbac;

/** @runTestsInSeparateProcesses */
final class EntryPageTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        Rbac::grantManager();
        if (function_exists('header_remove')) {
            header_remove();
        }
        if (!class_exists('FoodBankManager\\Database\\ApplicationsRepo', false)) {
            require_once __DIR__ . '/../../Support/ApplicationsRepoStub.php';
        }
        if (!class_exists('FBM\\Tests\\Support\\WPDBStub', false)) {
            require_once __DIR__ . '/../../Support/WPDBStub.php';
        }
        if (!isset($GLOBALS['wpdb'])) {
            $GLOBALS['wpdb'] = new \FBM\Tests\Support\WPDBStub();
        }
        if (!defined('ABSPATH')) {
            define('ABSPATH', __DIR__);
        }
        if (!defined('FBM_PATH')) {
            define('FBM_PATH', dirname(__DIR__, 3) . '/');
        }
    }

    public function testRenderDenied(): void {
        Rbac::revokeAll();
        fbm_seed_nonce('unit-seed');
        fbm_test_set_request_nonce('fbm_entry_view');
        $_GET = array(
            'entry_id' => '1',
        );
        $_SERVER['REQUEST_METHOD'] = 'GET';
        ob_start();
        ( new EntryPage() )->render();
        $html = (string) ob_get_clean();
        $this->assertStringContainsString('<div class="wrap fbm-admin">', $html);
        $this->assertStringContainsString('You do not have permission to access this page.', $html);
    }

    public function testRenderMasksEmailWithoutSensitiveCap(): void {
        fbm_seed_nonce('unit-seed');
        fbm_test_set_request_nonce('fbm_entry_view');
        $_GET = array(
            'entry_id' => '1',
            '_wpnonce' => $_POST['_wpnonce'],
        );
        $_REQUEST = $_GET;
        ob_start();
        ( new EntryPage() )->render();
        $html = (string) ob_get_clean();
        $this->assertStringContainsString('<div class="wrap fbm-admin">', $html);
        $this->assertStringContainsString('j***@example.com', $html);
        $this->assertStringNotContainsString('john@example.com', $html);
        $this->assertStringNotContainsString('Unmask', $html);
    }

    public function testUnmaskShowsPlaintextWithCapability(): void {
        Rbac::grantAdmin();
        fbm_seed_nonce('unit-seed');
        fbm_test_set_request_nonce('fbm_entry_view');
        $_GET = array(
            'entry_id' => '1',
            '_wpnonce' => $_POST['_wpnonce'],
        );
        $_REQUEST = $_GET;
        ob_start();
        ( new EntryPage() )->render();
        ob_end_clean();

        fbm_seed_nonce('unit-seed');
        fbm_test_set_request_nonce('fbm_entry_unmask', 'fbm_nonce');
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['fbm_action'] = 'unmask_entry';
        $_REQUEST            = array_merge( $_GET, $_POST );
        ob_start();
        ( new EntryPage() )->render();
        $html = (string) ob_get_clean();
        $this->assertStringContainsString('john@example.com', $html);
    }

    public function testUnmaskDeniedWithoutNonce(): void {
        Rbac::grantAdmin();
        fbm_seed_nonce('unit-seed');
        fbm_test_trust_nonces(false);
        fbm_test_set_request_nonce('fbm_entry_view');
        $_GET = array(
            'entry_id' => '1',
            '_wpnonce' => $_POST['_wpnonce'],
        );
        $_REQUEST = $_GET;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['fbm_action'] = 'unmask_entry';
        $this->expectException( FbmDieException::class );
        ( new EntryPage() )->render();
    }

    public function testPdfDeniedWithoutNonce(): void {
        fbm_seed_nonce('unit-seed');
        fbm_test_trust_nonces(false);
        fbm_test_set_request_nonce('fbm_entry_view');
        $_GET = array(
            'entry_id' => '1',
            '_wpnonce' => $_POST['_wpnonce'],
        );
        $_REQUEST = $_GET;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['fbm_action'] = 'entry_pdf';
        $this->expectException( FbmDieException::class );
        ( new EntryPage() )->render();
    }

    public function testPdfExportHandlesEngines(): void {
        fbm_seed_nonce('unit-seed');
        fbm_test_set_request_nonce('fbm_entry_view');
        $_GET = array(
            'entry_id' => '2',
            '_wpnonce' => $_POST['_wpnonce'],
        );
        $_REQUEST = $_GET;
        fbm_seed_nonce('unit-seed');
        fbm_test_set_request_nonce('fbm_entry_pdf', 'fbm_nonce');
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['fbm_action'] = 'entry_pdf';
        $_POST['fbm_nonce']  = $_POST['fbm_nonce'];
        $_REQUEST            = array_merge( $_GET, $_POST );
        ob_start();
        ( new EntryPage() )->render();
        $body = ob_get_clean();
        if ( ! class_exists( 'Mpdf\\Mpdf' ) && ! class_exists( 'TCPDF' ) ) {
            $this->assertStringContainsString('<h1>Entry</h1>', $body);
        } else {
            $this->assertNotEmpty( $body );
        }
    }
}

