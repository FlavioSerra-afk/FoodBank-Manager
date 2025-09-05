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
        $GLOBALS['fbm_user_caps'] = [
            'manage_options'      => true,
            'fb_manage_dashboard' => false,
        ];

        Menu::register();

        $call = $GLOBALS['fbm_test_calls']['add_menu_page'][0];
        self::assertSame('manage_options', $call[2]);

        foreach ($GLOBALS['fbm_test_calls']['add_submenu_page'] as $args) {
            self::assertStringStartsWith('fb_manage_', $args[3]);
        }
    }

    public function testParentMenuUsesFbmCapWhenPresent(): void {
        $GLOBALS['fbm_user_caps'] = [
            'fb_manage_dashboard' => true,
        ];

        Menu::register();

        $call = $GLOBALS['fbm_test_calls']['add_menu_page'][0];
        self::assertSame('fb_manage_dashboard', $call[2]);

        foreach ($GLOBALS['fbm_test_calls']['add_submenu_page'] as $args) {
            self::assertStringStartsWith('fb_manage_', $args[3]);
        }
    }
}
