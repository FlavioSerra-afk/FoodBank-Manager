<?php
declare(strict_types=1);

namespace Tests\Unit\Admin;

use FoodBankManager\Admin\DiagnosticsReport;
use FBM\Tests\Support\WPDBStub;

final class DiagnosticsReportTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        \fbm_grant_caps(['fb_manage_diagnostics']);
        if (!class_exists(WPDBStub::class)) {
            require_once __DIR__ . '/../../Support/WPDBStub.php';
        }
        $GLOBALS['wpdb'] = new WPDBStub();
    }

    protected function tearDown(): void {
        unset($GLOBALS['wpdb']);
        delete_option('cron');
        parent::tearDown();
    }

    public function testRenderContainsCopyButton(): void {
        $html = $this->render();
        $this->assertStringContainsString('Copy report', $html);
    }

    private function render(): string {
        ob_start();
        DiagnosticsReport::render();
        return (string) ob_get_clean();
    }
}
