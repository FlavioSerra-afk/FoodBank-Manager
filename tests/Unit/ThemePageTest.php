<?php
declare(strict_types=1);

use FoodBankManager\Admin\ThemePage;

final class ThemePageTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        fbm_grant_for_page('fbm_theme');
        if ( ! defined( 'FBM_PATH' ) ) {
            define( 'FBM_PATH', dirname(__DIR__, 2) . '/' );
        }
        fbm_test_trust_nonces(true);
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_POST = array();
    }

    public function test_render_admin_tab_no_script(): void {
        $_GET['tab'] = 'admin';
        ob_start();
        ThemePage::route();
        $out = ob_get_clean();
        $this->assertStringContainsString('wrap fbm-admin', $out);
        $this->assertStringContainsString('fbm_settings[theme][admin][style]', $out);
        $this->assertStringNotContainsString('<script', $out);
    }

    public function test_render_front_tab_has_match_toggle(): void {
        $_GET['tab'] = 'front';
        ob_start();
        ThemePage::route();
        $out = ob_get_clean();
        $this->assertStringContainsString('fbm_settings[theme][front][enabled]', $out);
        $this->assertStringContainsString('fbm_settings[theme][match_front_to_admin]', $out);
    }
}
