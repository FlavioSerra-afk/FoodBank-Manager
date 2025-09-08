<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Admin\Notices;

final class NoticesTest extends TestCase {

    /** @runInSeparateProcess */
    public function testMissingKekBailsOnNonFbmScreen(): void {
        fbm_test_reset_globals();
        $GLOBALS['fbm_test_screen_id'] = 'dashboard';
        fbm_grant_caps(['manage_options']);
        Notices::missing_kek();
        ob_start();
        Notices::render();
        $out = ob_get_clean();
        $this->assertSame('', $out);
        $this->assertSame(0, Notices::getRenderCount());
    }

    /** @runInSeparateProcess */
    public function testMissingKekShowsOnFbmScreen(): void {
        fbm_test_reset_globals();
        $GLOBALS['fbm_test_screen_id'] = 'foodbank_page_fbm_diagnostics';
        fbm_grant_caps(['manage_options']);
        if (!defined('FBM_KEK_BASE64')) {
            define('FBM_KEK_BASE64', 'dummy');
        }
        Notices::missing_kek();
        ob_start();
        Notices::render();
        $out = ob_get_clean();
        $this->assertStringContainsString('FoodBank Manager encryption key is not configured.', $out);
        $this->assertSame(1, Notices::getRenderCount());
    }

    /** @runInSeparateProcess */
    public function testCapsFixNoticeShownForAdminsWithoutCaps(): void {
        fbm_test_reset_globals();
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
