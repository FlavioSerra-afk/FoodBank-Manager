<?php
declare(strict_types=1);

use FoodBankManager\Admin\Menu;

final class MenuVisibilityTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        $ref = new \ReflectionClass(Menu::class);
        $prop = $ref->getProperty('registered');
        $prop->setAccessible(true);
        $prop->setValue(null, false);
    }

    public function testParentMenuFallsBackToManageOptions(): void {
        fbm_grant_caps(['manage_options']);
        Menu::register();
        $call = $GLOBALS['fbm_test_calls']['add_menu_page'][0];
        $this->assertSame('fbm', $call['slug']);
        $this->assertSame('manage_options', $call['cap']);
        foreach ($GLOBALS['fbm_test_calls']['add_submenu_page'] as $sm) {
            if (str_starts_with($sm['cap'], 'fbm_manage_')) {
                continue;
            }
            $this->assertStringStartsWith('fb_manage_', $sm['cap']);
        }
    }

    public function testParentMenuUsesFbmCapWhenPresent(): void {
        fbm_grant_manager();
        Menu::register();
        $call = $GLOBALS['fbm_test_calls']['add_menu_page'][0];
        $this->assertSame('fb_manage_dashboard', $call['cap']);
        foreach ($GLOBALS['fbm_test_calls']['add_submenu_page'] as $sm) {
            if (str_starts_with($sm['cap'], 'fbm_manage_')) {
                continue;
            }
            $this->assertStringStartsWith('fb_manage_', $sm['cap']);
        }
    }
}
