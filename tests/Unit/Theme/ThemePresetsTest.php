<?php

declare(strict_types=1);

use FoodBankManager\UI\Theme;

final class ThemePresetsTest extends \BaseTestCase {
    public function test_high_contrast_disables_blur(): void {
        update_option('fbm_theme', Theme::defaults());
        $tokens = Theme::sanitize(array_replace_recursive(Theme::defaults(), array('admin' => array('preset' => 'high_contrast'))));
        $css    = Theme::css_vars($tokens['admin'], '.t');
        $this->assertStringContainsString('--fbm-glass-blur:0px', $css);
        $this->assertStringContainsString('--fbm-glass-alpha:0.00', $css);
    }

    public function test_default_glass_has_blur(): void {
        update_option('fbm_theme', Theme::defaults());
        $tokens = Theme::sanitize(Theme::defaults());
        $css    = Theme::css_vars($tokens['admin'], '.t');
        $this->assertStringContainsString('--fbm-glass-blur:', $css);
        $this->assertStringNotContainsString('--fbm-glass-blur:0px', $css);
    }
}
