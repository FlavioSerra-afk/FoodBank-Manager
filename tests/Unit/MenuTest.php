<?php
declare(strict_types=1);

use FoodBankManager\Admin\Menu;

final class MenuTest extends BaseTestCase {
    public function testRegisterIdempotent(): void {
        Menu::register();
        Menu::register(); // should be idempotent

        self::assertCount(1, $GLOBALS['fbm_test_calls']['add_menu_page']);
        $subs = $GLOBALS['fbm_test_calls']['add_submenu_page'];
        self::assertGreaterThanOrEqual(10, count($subs));

        $this->assertSame('fbm', $subs[0]['slug']);

        $slugs = array_column($subs, 'slug');
        foreach (['fbm','fbm_attendance','fbm_reports','fbm_database','fbm_forms','fbm_form_builder','fbm_emails','fbm_settings','fbm_permissions','fbm_diagnostics','fbm_theme','fbm_shortcodes'] as $expected) {
            self::assertContains($expected, $slugs);
        }
    }
}
