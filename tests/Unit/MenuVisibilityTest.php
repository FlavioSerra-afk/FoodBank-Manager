<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Admin\Menu;

final class MenuVisibilityTest extends TestCase {
    protected function setUp(): void {
        $GLOBALS['fbm_test_calls'] = ['add_menu_page'=>[], 'add_submenu_page'=>[]];
        $GLOBALS['fbm_current_user_caps'] = [
            'fb_manage_dashboard' => false,
            'manage_options'      => true,
        ];
    }

    protected function tearDown(): void {
        unset($GLOBALS['fbm_current_user_caps']);
    }

    public function testParentMenuFallsBackToManageOptions(): void {
        Menu::register();

        $call = $GLOBALS['fbm_test_calls']['add_menu_page'][0];
        self::assertSame('manage_options', $call[2]);

        foreach ($GLOBALS['fbm_test_calls']['add_submenu_page'] as $args) {
            self::assertStringStartsWith('fb_manage_', $args[3]);
        }
    }
}
