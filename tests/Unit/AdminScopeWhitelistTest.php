<?php
use FoodBankManager\Core\Assets;
use FoodBankManager\Core\Options;

if (!defined('FBM_URL')) {
    define('FBM_URL', '');
}

final class AdminScopeWhitelistTest extends \BaseTestCase {
    public function test_whitelisted_slugs_enqueue_theme(): void {
        Options::update('theme', array('apply_admin_chrome' => true));
        $assets = new Assets();
        $assets->register();
        foreach ( \FBM\Core\AdminScope::SLUGS as $slug ) {
            $_GET['page'] = $slug;
            $GLOBALS['fbm_styles'] = [];
            $GLOBALS['fbm_inline_styles'] = [];
            $classes = apply_filters('admin_body_class', '');
            do_action('admin_enqueue_scripts', $slug);
            $this->assertArrayHasKey('fbm-admin', $GLOBALS['fbm_styles'], $slug);
            $this->assertArrayHasKey('fbm-admin', $GLOBALS['fbm_inline_styles'], $slug);
            $this->assertStringContainsString('fbm-themed', $classes, $slug);
        }
        $_GET['page'] = 'not_fbm';
        $GLOBALS['fbm_styles'] = [];
        $GLOBALS['fbm_inline_styles'] = [];
        $classes = apply_filters('admin_body_class', '');
        do_action('admin_enqueue_scripts', 'plugins.php');
        $this->assertArrayNotHasKey('fbm-admin', $GLOBALS['fbm_styles']);
        $this->assertArrayNotHasKey('fbm-admin', $GLOBALS['fbm_inline_styles']);
        $this->assertStringNotContainsString('fbm-themed', $classes);
    }
}
