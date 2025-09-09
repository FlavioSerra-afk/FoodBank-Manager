<?php

declare(strict_types=1);

use FBM\UI\ThemePresets;

final class ThemePresetsTest extends BaseTestCase {
    public function test_high_contrast_tokens_differ(): void {
        $light = ThemePresets::tokens('light');
        $hc    = ThemePresets::tokens('high_contrast');
        $this->assertNotSame($light['color-bg'], $hc['color-bg']);
        $this->assertNotSame($light['color-fg'], $hc['color-fg']);
        $this->assertArrayHasKey('focus', $hc);
    }

    public function test_css_vars_contains_tokens(): void {
        $css = ThemePresets::css_vars('high_contrast');
        $this->assertStringContainsString('--fbm-color-bg', $css);
        $this->assertStringContainsString('--fbm-focus', $css);
    }
}
