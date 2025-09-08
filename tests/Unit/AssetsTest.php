<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Core\Assets;
use FoodBankManager\Core\Options;

if ( ! defined( 'FBM_URL' ) ) {
        define( 'FBM_URL', '' );
}

final class AssetsTest extends TestCase {
        protected function setUp(): void {
                global $fbm_inline_styles, $fbm_options, $fbm_styles, $fbm_scripts, $fbm_user_caps;
                $fbm_inline_styles = array();
                $fbm_options      = array();
                $fbm_styles       = array();
                $fbm_scripts      = array();
                $fbm_user_caps    = array('manage_options' => true);
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
                global $fbm_styles;
                $assets = new Assets();

                $GLOBALS['fbm_test_screen_id'] = 'dashboard';
                $fbm_styles = array();
                $assets->enqueue_admin();
                $this->assertArrayNotHasKey( 'fbm-admin', $fbm_styles );

                $GLOBALS['fbm_test_screen_id'] = 'toplevel_page_fbm';
                $fbm_styles = array();
                $assets->enqueue_admin();
                $this->assertArrayHasKey( 'fbm-admin', $fbm_styles );

                $GLOBALS['fbm_test_screen_id'] = 'foodbank_page_fbm_database';
                $fbm_styles = array();
                $assets->enqueue_admin();
                $this->assertArrayHasKey( 'fbm-admin', $fbm_styles );
        }
}
