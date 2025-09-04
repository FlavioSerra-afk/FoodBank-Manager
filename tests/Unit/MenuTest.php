<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Admin\Menu;

if ( ! function_exists( 'add_menu_page' ) ) {
    function add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $callback = '', $icon_url = '', $position = null ) {
        global $fbm_menu_slugs;
        $fbm_menu_slugs[] = $menu_slug;
        return 'toplevel_page_' . $menu_slug;
    }
}
if ( ! function_exists( 'add_submenu_page' ) ) {
    function add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $callback = '' ) {
        global $fbm_submenu_slugs;
        $fbm_submenu_slugs[] = $menu_slug;
        return $parent_slug . '_page_' . $menu_slug;
    }
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
        global $fbm_menu_slugs, $fbm_submenu_slugs;
        $fbm_menu_slugs    = array();
        $fbm_submenu_slugs = array();
    }

    public function testRegisterIdempotent(): void {
        global $fbm_menu_slugs, $fbm_submenu_slugs;
        Menu::register();
        $menu_first    = $fbm_menu_slugs;
        $submenu_first = $fbm_submenu_slugs;
        Menu::register();
        $this->assertSame( $menu_first, $fbm_menu_slugs );
        $this->assertSame( $submenu_first, $fbm_submenu_slugs );
        $this->assertSame( array( 'fbm' ), $fbm_menu_slugs );
        $this->assertSame(
            array(
                'fbm',
                'fbm_attendance',
                'fbm_database',
                'fbm_forms',
                'fbm_emails',
                'fbm_settings',
                'fbm_permissions',
                'fbm_diagnostics',
                'fbm_theme',
                'fbm_shortcodes',
            ),
            $fbm_submenu_slugs
        );
    }
}
