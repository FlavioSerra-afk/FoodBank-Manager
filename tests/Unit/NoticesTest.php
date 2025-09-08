<?php
declare(strict_types=1);

use FoodBankManager\Admin\Notices;

final class NoticesTest extends BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        if (!defined('FBM_KEK_BASE64')) {
            define('FBM_KEK_BASE64', base64_encode(str_repeat('K', 32)));
        }
        $GLOBALS['fbm_test_options'] = ['emails' => ['from_email' => 'from@example.com']];
        $GLOBALS['fbm_options']      = ['fbm_options' => $GLOBALS['fbm_test_options']];
    }
    public function testMissingKekBailsOnNonFbmScreen(): void {
        $GLOBALS['fbm_test_screen_id'] = 'dashboard';
        fbm_grant_admin();
        Notices::missing_kek();
        ob_start();
        Notices::render();
        $out = ob_get_clean();
        $this->assertSame('', $out);
        $this->assertSame(0, Notices::getRenderCount());
    }

    public function testMissingKekShowsOnFbmScreen(): void {
        $GLOBALS['fbm_test_screen_id'] = 'foodbank_page_fbm_diagnostics';
        fbm_grant_admin();
        Notices::missing_kek();
        ob_start();
        Notices::render();
        $out = ob_get_clean();
        $this->assertStringContainsString('FoodBank Manager encryption key is not configured.', $out);
        $this->assertSame(1, Notices::getRenderCount());
    }

    public function testCapsFixNoticeShownForAdminsWithoutCaps(): void {
        $GLOBALS['fbm_test_screen_id'] = 'foodbank_page_fbm_diagnostics';
        fbm_grant_caps(['manage_options']);
        ob_start();
        Notices::render_caps_fix_notice();
        Notices::render();
        $out = ob_get_clean();
        $this->assertSame(1, Notices::getRenderCount());
        $this->assertStringContainsString('page=fbm_diagnostics', $out);
    }
}
