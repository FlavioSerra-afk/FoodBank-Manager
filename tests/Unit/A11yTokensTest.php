<?php
final class A11yTokensTest extends \BaseTestCase {
    public function test_admin_css_contains_focus_and_forced_colors(): void {
        if (!defined('FBM_PATH')) {
            define('FBM_PATH', dirname(__DIR__, 2) . '/');
        }
        $css = file_get_contents(FBM_PATH . 'assets/css/admin.css');
        $this->assertStringContainsString(':focus-visible', $css);
        $this->assertStringContainsString('@media (forced-colors: active)', $css);
    }
}
