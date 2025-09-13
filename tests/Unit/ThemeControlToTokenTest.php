<?php
use FBM\Core\Options;
use FoodBankManager\UI\Theme;

if (!defined('FBM_URL')) {
    define('FBM_URL', '');
}

final class ThemeControlToTokenTest extends \BaseTestCase {
    public function test_menu_tokens_present_in_css(): void {
        $t = Theme::defaults();
        $t['menu']['item_height'] = 52;
        Options::update('theme', $t);
        $css = Theme::css_variables_scoped();
        $this->assertStringContainsString('.fbm-scope', $css);
        $this->assertStringContainsString('--fbm-menu-item-h:52px', $css);
    }
}
