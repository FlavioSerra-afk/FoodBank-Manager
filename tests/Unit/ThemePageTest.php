<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Admin\ThemePage;
use FoodBankManager\Core\Options;

if ( ! function_exists( 'wp_unslash' ) ) {
        function wp_unslash( $value ) {
                return is_array( $value ) ? array_map( 'wp_unslash', $value ) : stripslashes( (string) $value );
        }
}
if ( ! function_exists( 'wp_die' ) ) {
        function wp_die( $message = '' ) {
                throw new RuntimeException( (string) $message );
        }
}
if ( ! function_exists( 'menu_page_url' ) ) {
        function menu_page_url( string $slug, bool $echo = true ): string {
                return 'admin.php?page=' . $slug;
        }
}
if ( ! function_exists( 'add_query_arg' ) ) {
        function add_query_arg( array $args, string $url ): string {
                return $url . '?' . http_build_query( $args );
        }
}
if ( ! function_exists( 'wp_safe_redirect' ) ) {
        function wp_safe_redirect( string $url, int $status = 302 ): void {
                ThemePageTest::$redirect = $url;
                throw new RuntimeException( 'redirect' );
        }
}
if ( ! function_exists( 'map_deep' ) ) {
        function map_deep( $value, $callback ) {
                if ( is_array( $value ) ) {
                        return array_map( function ( $v ) use ( $callback ) {
                                return map_deep( $v, $callback );
                        }, $value );
                }
                return $callback( $value );
        }
}

final class ThemePageTest extends TestCase {
        public static string $redirect = '';

        protected function setUp(): void {
        parent::setUp();
        fbm_test_reset_globals();
        fbm_grant_for_page('fbm_theme');
        fbm_test_trust_nonces(true);
        self::$redirect = '';
        $_POST = $_SERVER = $_REQUEST = array();
        global $fbm_test_options;
        $fbm_test_options = array();
        }

        public function testMissingNonceBlocked(): void {
                fbm_test_trust_nonces(false);
                $_SERVER['REQUEST_METHOD'] = 'POST';
                $this->expectException( RuntimeException::class );
                ThemePage::route();
        }

        public function testUserWithoutCapBlocked(): void {
                fbm_test_reset_globals();
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
                $this->assertStringContainsString( 'notice=saved', self::$redirect );
        }
}
