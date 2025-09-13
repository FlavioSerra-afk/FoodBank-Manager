<?php
declare(strict_types=1);

use FoodBankManager\UI\Theme;

final class AssetsRtlTest extends \BaseTestCase {
    /** @runInSeparateProcess */
    public function test_body_classes_include_rtl(): void {
        if (!function_exists('is_rtl')) {
            function is_rtl(): bool { return true; }
        }
        \FoodBankManager\Core\Options::update('theme', array('apply_admin' => true, 'apply_front_menus' => true));
        $front = Theme::body_class(array());
        $this->assertContains('fbm-rtl', $front);
    }
}
