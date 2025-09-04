<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Core\Assets;
use FoodBankManager\Core\Options;

if ( ! function_exists( 'wp_register_style' ) ) {
        function wp_register_style( $handle, $src = '', $deps = array(), $ver = false, $media = 'all' ) {
                global $fbm_registered_styles;
                $fbm_registered_styles[ $handle ] = compact( 'src', 'deps', 'ver', 'media' );
        }
}
if ( ! function_exists( 'wp_add_inline_style' ) ) {
        function wp_add_inline_style( $handle, $css ) {
                global $fbm_inline_styles;
                $fbm_inline_styles[ $handle ] = $css;
                return true;
        }
}
if ( ! function_exists( 'wp_enqueue_style' ) ) {
        function wp_enqueue_style( $handle, $src = '', $deps = array(), $ver = false, $media = 'all' ) {
                global $fbm_enqueued_styles;
                $fbm_enqueued_styles[] = $handle;
        }
}
if ( ! function_exists( 'wp_enqueue_script' ) ) {
        function wp_enqueue_script( $handle, $src = '', $deps = array(), $ver = false, $in_footer = false ) {
                global $fbm_enqueued_scripts;
                $fbm_enqueued_scripts[] = $handle;
        }
}
if ( ! function_exists( 'current_user_can' ) ) {
        function current_user_can( string $cap ): bool {
                return true;
        }
}
if ( ! defined( 'FBM_URL' ) ) {
        define( 'FBM_URL', '' );
}

final class AssetsTest extends TestCase {
        protected function setUp(): void {
                global $fbm_inline_styles, $fbm_test_options, $fbm_enqueued_styles, $fbm_registered_styles;
                $fbm_inline_styles    = array();
                $fbm_test_options     = array();
                $fbm_enqueued_styles  = array();
                $fbm_registered_styles = array();
                $GLOBALS['fbm_test_screen_id'] = null;
        }

        public function testThemeCssContainsVariables(): void {
                Options::update(
                        array(
                                'theme' => array(
                                        'primary_color'    => '#010203',
                                        'density'          => 'compact',
                                        'font_family'      => 'inter',
                                        'dark_mode_default' => true,
                                        'custom_css'       => '.x{color:red;}',
                                ),
                        )
                );
                $ref    = new \ReflectionClass( Assets::class );
                $method = $ref->getMethod( 'theme_css' );
                $method->setAccessible( true );
                $css = (string) $method->invoke( null );
                $this->assertStringContainsString( '--fbm-primary:#010203', $css );
                $this->assertStringContainsString( '--fbm-density:compact', $css );
                $this->assertStringContainsString( '--fbm-font:"Inter"', $css );
                $this->assertStringContainsString( '--fbm-dark:1', $css );
                $this->assertStringContainsString( '.x{color:red;}', $css );
        }

        public function testAdminCssOnlyEnqueuedOnFbmScreens(): void {
                global $fbm_enqueued_styles;
                $assets = new Assets();

                $GLOBALS['fbm_test_screen_id'] = 'dashboard';
                $fbm_enqueued_styles = array();
                $assets->enqueue_admin();
                $this->assertNotContains( 'fbm-admin', $fbm_enqueued_styles );

                $GLOBALS['fbm_test_screen_id'] = 'toplevel_page_fbm';
                $fbm_enqueued_styles = array();
                $assets->enqueue_admin();
                $this->assertContains( 'fbm-admin', $fbm_enqueued_styles );

                $GLOBALS['fbm_test_screen_id'] = 'foodbank_page_fbm_database';
                $fbm_enqueued_styles = array();
                $assets->enqueue_admin();
                $this->assertContains( 'fbm-admin', $fbm_enqueued_styles );
        }
}
