<?php
use FoodBankManager\Core\Assets;

final class AdminAssetsScopeTest extends \BaseTestCase {
    public function test_no_theme_css_outside_fbm(): void {
        $assets = new Assets();
        $GLOBALS['fbm_styles'] = [];
        $GLOBALS['fbm_inline_styles'] = [];
        $GLOBALS['fbm_test_screen_id'] = 'plugins';
        $assets->enqueue_admin();
        $this->assertArrayNotHasKey('fbm-admin', $GLOBALS['fbm_inline_styles']);
    }
}
