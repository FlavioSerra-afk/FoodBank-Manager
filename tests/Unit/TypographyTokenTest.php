<?php
use FBM\Core\Options;
use FoodBankManager\UI\Theme;

if (!defined('FBM_URL')) {
    define('FBM_URL', '');
}

final class TypographyTokenTest extends \BaseTestCase {
    public function test_typography_tokens_present(): void {
        update_option('fbm_theme', fbm_theme_defaults());
        $t = Theme::defaults();
        $t['typography']['h3']['size'] = 22;
        $t['typography']['link']['hover'] = '#123456';
        Options::update('theme', $t);
        $css = Theme::css_variables_scoped();
        $this->assertStringContainsString('--fbm-h3:22px', $css);
        $this->assertStringContainsString('--fbm-link-hover:#123456', $css);
        $this->assertStringContainsString('.fbm-scope h3', $css);
    }
}
