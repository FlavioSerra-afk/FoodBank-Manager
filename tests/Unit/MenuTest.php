<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Admin\Menu;

if ( ! function_exists( 'current_action' ) ) {
    function current_action() { return 'admin_menu'; }
}
if ( ! function_exists( 'esc_html__' ) ) {
    function esc_html__( string $text, string $domain = 'default' ): string {
        return $text;
    }
}
if ( ! function_exists( 'esc_html' ) ) {
    function esc_html( $text ) {
        return (string) $text;
    }
}

final class MenuTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        fbm_test_reset_globals();
    }
    public function testRegisterIdempotent(): void {
        Menu::register();
        Menu::register(); // should be idempotent

        self::assertCount(1, $GLOBALS['fbm_test_calls']['add_menu_page']);
        $subs = $GLOBALS['fbm_test_calls']['add_submenu_page'];
        self::assertGreaterThanOrEqual(9, count($subs));

        [$parent, $page_title, $menu_title, $cap, $slug] = $subs[0];
        self::assertSame('fbm', $slug);

        $slugs = array_column($subs, 4);
        foreach (['fbm','fbm_attendance','fbm_database','fbm_forms','fbm_emails','fbm_settings','fbm_permissions','fbm_diagnostics','fbm_theme','fbm_shortcodes'] as $expected) {
            self::assertContains($expected, $slugs);
        }
    }
}
