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
// capability handled via $GLOBALS['fbm_user_caps']
if ( ! function_exists( 'check_admin_referer' ) ) {
        function check_admin_referer( string $action, string $name = '_wpnonce' ): void {
                if ( empty( $_POST[ $name ] ) ) {
                        throw new RuntimeException( 'missing nonce' );
                }
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
                fbm_reset_globals();
                $GLOBALS['fbm_user_caps'] = ['fb_manage_theme' => true];
                self::$redirect = '';
                $_POST          = array();
                $_SERVER        = array();
                global $fbm_test_options;
                $fbm_test_options = array();
        }

        public function testMissingNonceBlocked(): void {
                $_SERVER['REQUEST_METHOD'] = 'POST';
                $this->expectException( RuntimeException::class );
                ThemePage::route();
        }

        public function testUserWithoutCapBlocked(): void {
                $_SERVER['REQUEST_METHOD'] = 'POST';
                $_POST['_wpnonce']         = 'nonce';
                $GLOBALS['fbm_user_caps']['fb_manage_theme'] = false;
                $this->expectException( RuntimeException::class );
                ThemePage::route();
        }

        public function testSuccessfulSave(): void {
                $_SERVER['REQUEST_METHOD'] = 'POST';
                $_POST                     = array(
                        '_wpnonce'  => 'nonce',
                        'fbm_theme' => array(
                                'primary_color' => '#445566',
                        ),
                );
                try {
                        ThemePage::route();
                } catch ( RuntimeException $e ) {
                        $this->assertSame( 'redirect', $e->getMessage() );
                }
                $this->assertSame( '#445566', Options::get( 'theme.primary_color' ) );
                $this->assertStringContainsString( 'notice=saved', self::$redirect );
        }
}
