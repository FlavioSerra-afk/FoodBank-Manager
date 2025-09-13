<?php
final class A11yTokensTest extends \BaseTestCase {
    public function test_css_contains_accent_and_focus_rules(): void {
        if (!defined('FBM_PATH')) {
            define('FBM_PATH', dirname(__DIR__, 2) . '/');
        }
        $admin  = file_get_contents(FBM_PATH . 'assets/css/admin.css');
        $public = file_get_contents(FBM_PATH . 'assets/css/public.css');
        foreach ([$admin, $public] as $css) {
            $this->assertStringContainsString('accent-color', $css);
            $this->assertStringContainsString(':focus-visible', $css);
            $this->assertStringContainsString('@media (forced-colors: active)', $css);
        }
    }
}
