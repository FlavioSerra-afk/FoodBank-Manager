<?php

declare(strict_types=1);

use FoodBankManager\Core\Assets;
use FoodBankManager\Core\Options;

final class AssetsRtlTest extends \BaseTestCase {
    /** @runInSeparateProcess */
    public function test_high_contrast_force_on_emits_rtl_selector(): void {
        Options::save(['theme' => ['preset' => 'high_contrast', 'rtl' => 'force_on']]);
        $GLOBALS['fbm_test_screen_id'] = 'toplevel_page_fbm';
        ob_start();
        Assets::print_admin_head();
        $css = ob_get_clean();
        $this->assertStringContainsString('--fbm-color-bg:#000000', $css);
        $this->assertStringContainsString('--fbm-glass-blur:0', $css);
        $this->assertStringContainsString('html[dir=&quot;rtl&quot;] .fbm-admin', $css);
    }

    /** @runInSeparateProcess */
    public function test_force_off_sets_ltr_only(): void {
        Options::save(['theme' => ['preset' => 'light', 'rtl' => 'force_off']]);
        $GLOBALS['fbm_test_screen_id'] = 'toplevel_page_fbm';
        ob_start();
        Assets::print_admin_head();
        $css = ob_get_clean();
        $this->assertStringContainsString('.fbm-admin{direction:ltr;}', $css);
        $this->assertStringNotContainsString('html[dir=&quot;rtl&quot;] .fbm-admin', $css);
    }
}
