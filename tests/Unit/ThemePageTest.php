<?php
declare(strict_types=1);

use FoodBankManager\Admin\ThemePage;

final class ThemePageTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        fbm_grant_for_page('fbm_theme');
        if (!defined('FBM_PATH')) {
            define('FBM_PATH', dirname(__DIR__, 2) . '/');
        }
        update_option('fbm_theme', fbm_theme_defaults());
        fbm_test_trust_nonces(true);
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_POST = [];
    }

    public function test_render_shell(): void {
        ob_start();
        ThemePage::route();
        $out = ob_get_clean();
        $this->assertStringContainsString('wrap fbm-theme', $out);
        $this->assertStringContainsString('fbm-theme-form', $out);
        $this->assertStringContainsString('fbm-preview-vars', $out);
    }
}
