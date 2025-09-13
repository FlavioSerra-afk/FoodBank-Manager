<?php
use FoodBankManager\Core\Assets;
use FoodBankManager\Core\Options;

if (!defined('FBM_URL')) {
    define('FBM_URL', '');
}

final class BodyClassScopeTest extends \BaseTestCase {
    public function test_body_class_scoped_to_fbm_pages(): void {
        Options::update('theme', array('apply_admin_chrome' => true));
        $assets = new Assets();
        $assets->register();
        $_GET['page'] = 'fbm_reports';
        $classes = apply_filters('admin_body_class', '');
        $this->assertStringContainsString(' fbm-themed ', $classes);
        $_GET['page'] = 'other_page';
        $classes = apply_filters('admin_body_class', '');
        $this->assertStringNotContainsString('fbm-themed', $classes);
    }
}
