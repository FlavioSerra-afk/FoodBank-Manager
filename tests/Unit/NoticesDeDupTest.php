<?php
declare(strict_types=1);

use FoodBankManager\Admin\Notices;

final class NoticesDeDupTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        $GLOBALS['fbm_test_screen_id'] = null;
        fbm_grant_admin();
        if (!defined('FBM_KEK_BASE64')) {
            define('FBM_KEK_BASE64', base64_encode(str_repeat('K', 32)));
        }
        $GLOBALS['fbm_test_options'] = ['emails' => ['from_email' => 'from@example.com']];
        $GLOBALS['fbm_options'] = ['fbm_options' => $GLOBALS['fbm_test_options']];
        do_action('fbm_test_reset_notices');
    }

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

    public function testRenderBailsOnNonFbmScreen(): void {
        $GLOBALS['fbm_test_screen_id'] = 'dashboard';
        Notices::missing_kek();
        ob_start();
        Notices::render();
        $out = ob_get_clean();
        $this->assertSame('', $out);
        $this->assertSame(0, Notices::getRenderCount());
    }

    public function testRenderCountTracksSingleRender(): void {
        $GLOBALS['fbm_test_screen_id'] = 'toplevel_page_fbm';
        Notices::boot();
        Notices::render();
        Notices::render();
        $this->assertSame(1, Notices::getRenderCount());
    }
}
