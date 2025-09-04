<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Admin\Menu;

if ( ! function_exists( 'add_menu_page' ) ) {
    function add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $callback = '', $icon_url = '', $position = null ) {
        global $fbm_menu_calls;
        $fbm_menu_calls++; return 'toplevel_page_' . $menu_slug; }
}
if ( ! function_exists( 'add_submenu_page' ) ) {
    function add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $callback = '' ) {
        global $fbm_submenu_calls;
        $fbm_submenu_calls++; return $parent_slug . '_page_' . $menu_slug; }
}
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
        global $fbm_menu_calls, $fbm_submenu_calls;
        $fbm_menu_calls = 0;
        $fbm_submenu_calls = 0;
    }

    public function testRegisterIdempotent(): void {
        global $fbm_menu_calls, $fbm_submenu_calls;
        Menu::register();
        $menu_first     = $fbm_menu_calls;
        $submenu_first  = $fbm_submenu_calls;
        Menu::register();
        $this->assertSame( $menu_first, $fbm_menu_calls );
        $this->assertSame( $submenu_first, $fbm_submenu_calls );
        $this->assertGreaterThan( 0, $menu_first );
        $this->assertGreaterThan( 0, $submenu_first );
    }
}
