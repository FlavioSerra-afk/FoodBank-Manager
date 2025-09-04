<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Admin\SettingsPage;
use FoodBankManager\Core\Options;

if ( ! function_exists( 'wp_unslash' ) ) {
    function wp_unslash( $value ) {
        return is_array( $value ) ? array_map( 'wp_unslash', $value ) : stripslashes( (string) $value );
    }
}
if ( ! function_exists( 'current_user_can' ) ) {
    function current_user_can( string $cap ): bool {
        return SettingsPageTest::$can;
    }
}
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
        SettingsPageTest::$redirect = $url;
        throw new RuntimeException( 'redirect' );
    }
}

final class SettingsPageTest extends TestCase {
    public static bool $can      = true;
    public static string $redirect = '';

    protected function setUp(): void {
        parent::setUp();
        self::$can     = true;
        self::$redirect = '';
        $_POST         = array();
        $_SERVER       = array();
        global $fbm_test_options;
        $fbm_test_options = array();
    }

    public function testMissingNonceBlocked(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['fbm_action']       = 'branding_save';
        $this->expectException( RuntimeException::class );
        SettingsPage::route();
    }

    public function testUserWithoutCapBlocked(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['fbm_action']       = 'branding_save';
        $_POST['_fbm_nonce']       = 'nonce';
        self::$can                 = false;
        $this->expectException( RuntimeException::class );
        SettingsPage::route();
    }

    public function testSuccessfulSaveSanitizes(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST                     = array(
            'fbm_action' => 'branding_save',
            '_fbm_nonce' => 'nonce',
            'branding'   => array(
                'site_name' => ' Test ',
                'logo_url'  => ' https://example.com/logo.png ',
                'color'     => 'blue',
            ),
        );
        try {
            SettingsPage::route();
        } catch ( RuntimeException $e ) {
            $this->assertSame( 'redirect', $e->getMessage() );
        }
        $this->assertSame( 'Test', Options::get( 'branding.site_name' ) );
        $this->assertSame( 'https://example.com/logo.png', Options::get( 'branding.logo_url' ) );
        $this->assertSame( 'blue', Options::get( 'branding.color' ) );
        $this->assertStringContainsString( 'notice=saved', self::$redirect );
        $this->assertStringContainsString( 'tab=branding', self::$redirect );
    }
}
