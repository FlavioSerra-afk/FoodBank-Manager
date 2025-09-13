<?php
use FBM\Shortcodes\DashboardShortcode;

if (!defined('FBM_URL')) {
    define('FBM_URL', '');
}

final class FrontendParityTest extends \BaseTestCase {
    public function test_dashboard_shortcode_enqueues_public_css_and_vars(): void {
        fbm_grant_caps(['fb_manage_dashboard']);
        $hash = md5('7d||all|0');
        $base = 'fbm_dash_1_7d_' . $hash . '_';
        $GLOBALS['fbm_transients'][$base . 'series'] = array();
        $GLOBALS['fbm_transients'][$base . 'totals'] = array('present' => 0);
        $GLOBALS['fbm_transients'][$base . 'prev'] = array('present' => 0);
        $GLOBALS['fbm_styles'] = [];
        $GLOBALS['fbm_inline_styles'] = [];
        DashboardShortcode::render();
        $this->assertArrayHasKey('fbm-public', $GLOBALS['fbm_styles']);
        $this->assertArrayHasKey('fbm-public', $GLOBALS['fbm_inline_styles']);
        $this->assertStringContainsString('.fbm-scope{', $GLOBALS['fbm_inline_styles']['fbm-public']);
    }
}
