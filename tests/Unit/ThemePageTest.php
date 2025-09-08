<?php
declare(strict_types=1);

use FoodBankManager\Admin\ThemePage;
use FoodBankManager\Core\Options;

final class ThemePageTest extends BaseTestCase {

        private function loadTheme(): string {
                if ( class_exists( 'FoodBankManager\\UITest\\ThemeReal', false ) ) {
                        return 'FoodBankManager\\UITest\\ThemeReal';
                }
                $code = file_get_contents( __DIR__ . '/../../includes/UI/Theme.php' );
                $code = preg_replace( '/^<\?php[^\n]*\n/', '', $code );
                $code = str_replace( 'namespace FoodBankManager\\UI;', 'namespace FoodBankManager\\UITest;', $code );
                $code = str_replace( 'class Theme', 'class ThemeReal', $code );
                eval( $code );
                return 'FoodBankManager\\UITest\\ThemeReal';
        }

        protected function setUp(): void {
        parent::setUp();
        fbm_grant_for_page('fbm_theme');
        fbm_test_trust_nonces(true);
        if (!defined('FBM_PATH')) {
                define('FBM_PATH', dirname(__DIR__, 2) . '/');
        }
        if (!defined('ABSPATH')) {
                define('ABSPATH', FBM_PATH);
        }
        }

        public function testMissingNonceBlocked(): void {
                fbm_test_trust_nonces(false);
                $_SERVER['REQUEST_METHOD'] = 'POST';
                $this->expectException( RuntimeException::class );
                ThemePage::route();
        }

        public function testUserWithoutCapBlocked(): void {
                fbm_grant_viewer();
                fbm_test_trust_nonces(true);
                $_SERVER['REQUEST_METHOD'] = 'POST';
                fbm_test_set_request_nonce('fbm_theme_save');
                $this->expectException( RuntimeException::class );
                ThemePage::route();
        }

        public function testSuccessfulSave(): void {
                fbm_test_set_request_nonce('fbm_theme_save');
                $_SERVER['REQUEST_METHOD'] = 'POST';
                $_POST                     = array(
                        '_wpnonce'  => $_POST['_wpnonce'],
                        'fbm_theme' => array(
                                'primary_color' => '#445566',
                        ),
                );
                $_REQUEST = $_POST;
                try {
                        ThemePage::route();
                } catch ( RuntimeException $e ) {
                        $this->assertSame( 'redirect', $e->getMessage() );
                }
        $this->assertSame( '#445566', Options::get( 'theme.primary_color' ) );
        $this->assertStringContainsString( 'notice=saved', (string) $GLOBALS['__last_redirect'] );
        }

        public function testInvalidTokensClampToDefaults(): void {
                $class  = $this->loadTheme();
                $tokens = $class::sanitize( array(
                        'primary_color'    => 'bad',
                        'density'          => 'nope',
                        'font_family'      => 'foo',
                        'dark_mode_default' => 'maybe',
                ) );
        $this->assertSame( '#3b82f6', $tokens['primary_color'] );
        $this->assertSame( 'comfortable', $tokens['density'] );
        $this->assertSame( 'system', $tokens['font'] );
        $this->assertFalse( $tokens['dark_mode'] );
        $css = $class::to_css_vars( $tokens, '.fbm-admin' );
        $this->assertSame( '.fbm-admin{--fbm-primary:#3b82f6;--fbm-density:comfortable;--fbm-font:system-ui, sans-serif;--fbm-dark:0;}', $css );
        }

        public function testCssVarsOutput(): void {
                Options::update( array( 'theme' => array(
                        'primary_color'    => '#112233',
                        'density'          => 'compact',
                        'font_family'      => 'roboto',
                        'dark_mode_default' => true,
                ) ) );
                $class  = $this->loadTheme();
                $tokens = $class::admin();
        $css    = $class::to_css_vars( $tokens, '.fbm-admin' );
        $expected = '.fbm-admin{--fbm-primary:#112233;--fbm-density:compact;--fbm-font:"Roboto", system-ui, sans-serif;--fbm-dark:1;}';
        $this->assertSame( $expected, $css );
        }

        public function testPreviewMarkupScoped(): void {
                $tpl = file_get_contents( FBM_PATH . 'templates/admin/theme.php' );
                $this->assertStringContainsString( 'wrap fbm-admin', $tpl );
                $this->assertStringContainsString( 'fbm-theme-preview', $tpl );
                $this->assertStringNotContainsString( ':root', $tpl );
        }
}
