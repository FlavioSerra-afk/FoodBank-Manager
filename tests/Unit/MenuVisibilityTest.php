<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Admin\Menu;

final class MenuVisibilityTest extends TestCase {
    protected function setUp(): void {
        fbm_test_reset_globals();
        $ref = new \ReflectionClass(Menu::class);
        $prop = $ref->getProperty('registered');
        $prop->setAccessible(true);
        $prop->setValue(null, false);
    }

    protected function tearDown(): void {
        fbm_test_reset_globals();
    }

    public function testParentMenuFallsBackToManageOptions(): void {
        fbm_test_reset_globals();
        fbm_grant_admin_only();
        Menu::register();
        $call = $GLOBALS['fbm_test_calls']['add_menu_page'][0];
        $this->assertSame('fbm', $call['slug']);
        $this->assertSame('manage_options', $call['cap']);
        foreach ($GLOBALS['fbm_test_calls']['add_submenu_page'] as $sm) {
            $this->assertStringStartsWith('fb_manage_', $sm['cap']);
        }
    }

    public function testParentMenuUsesFbmCapWhenPresent(): void {
        fbm_test_reset_globals();
        fbm_grant_fbm_all();
        Menu::register();
        $call = $GLOBALS['fbm_test_calls']['add_menu_page'][0];
        $this->assertSame('fb_manage_dashboard', $call['cap']);
        foreach ($GLOBALS['fbm_test_calls']['add_submenu_page'] as $sm) {
            $this->assertStringStartsWith('fb_manage_', $sm['cap']);
        }
    }
}
