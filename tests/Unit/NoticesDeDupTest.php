<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Admin\Notices;

final class NoticesDeDupTest extends TestCase {
    protected function setUp(): void {
        fbm_test_reset_globals();
        $GLOBALS['fbm_test_screen_id'] = null;
        fbm_grant_admin_only();
        if (!defined('FBM_KEK_BASE64')) {
            define('FBM_KEK_BASE64', 'dummy');
        }
    }

    /** @runInSeparateProcess */
    public function testRenderPrintsOncePerRequest(): void {
        $GLOBALS['fbm_test_screen_id'] = 'toplevel_page_fbm';
        Notices::missing_kek();
        ob_start();
        Notices::render();
        Notices::render();
        $out = ob_get_clean();
        $this->assertStringContainsString('encryption key is not configured', $out);
        $this->assertSame(1, Notices::getRenderCount());
    }

    /** @runInSeparateProcess */
    public function testRenderBailsOnNonFbmScreen(): void {
        $GLOBALS['fbm_test_screen_id'] = 'dashboard';
        Notices::missing_kek();
        ob_start();
        Notices::render();
        $out = ob_get_clean();
        $this->assertSame('', $out);
        $this->assertSame(0, Notices::getRenderCount());
    }

    /** @runInSeparateProcess */
    public function testRenderCountTracksSingleRender(): void {
        $GLOBALS['fbm_test_screen_id'] = 'toplevel_page_fbm';
        Notices::boot();
        Notices::render();
        Notices::render();
        $this->assertSame(1, Notices::getRenderCount());
    }
}
