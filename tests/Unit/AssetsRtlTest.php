<?php

declare(strict_types=1);

use FoodBankManager\Core\Assets;
use FoodBankManager\Core\Options;

final class AssetsRtlTest extends \BaseTestCase {
    /** @runInSeparateProcess */
    public function test_high_contrast_disables_blur(): void {
        $GLOBALS['fbm_options'] = array('fbm_options' => array('theme' => array('admin' => array('preset' => 'high_contrast'))));
        $GLOBALS['fbm_test_screen_id'] = 'toplevel_page_fbm';
        ob_start();
        Assets::print_admin_head();
        $css = ob_get_clean();
        $this->assertStringContainsString('--fbm-glass-bg:#000000', $css);
        $this->assertStringContainsString('--fbm-glass-blur:0px', $css);
        $this->assertStringContainsString('@supports (backdrop-filter: blur(1px))', $css);
        $this->assertStringContainsString('@media (prefers-reduced-transparency: reduce)', $css);
        $this->assertStringContainsString('@media (forced-colors: active)', $css);
    }
}
