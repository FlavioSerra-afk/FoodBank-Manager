<?php
declare(strict_types=1);

namespace FoodBankManager\Admin;

/** @backupGlobals disabled */
final class ShortcodesHelperTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        fbm_grant_for_page('fbm_shortcodes');
        if (!defined('FBM_PATH')) {
            define('FBM_PATH', dirname(__DIR__, 3) . '/');
        }
        if (!defined('FBM_URL')) {
            define('FBM_URL', '');
        }
    }

    public function testExamplesRendered(): void {
        ob_start();
        ShortcodesPage::route();
        $html = (string) ob_get_clean();
        $this->assertStringContainsString('[fbm_form id="123" preset="basic_intake" mask_sensitive="true"]', $html);
        $this->assertStringContainsString('[fbm_dashboard compare="true" range="last_30" preset="manager"]', $html);
        $this->assertStringContainsString('Docs/Shortcodes.md', $html);
    }
}
