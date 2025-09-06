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
// capability handled via $GLOBALS['fbm_user_caps']
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
    public static string $redirect = '';

    protected function setUp(): void {
        parent::setUp();
        fbm_test_reset_globals();
        fbm_grant_for_page('fbm_settings');
        fbm_test_trust_nonces(true);
        self::$redirect = '';
        $_POST = $_SERVER = $_REQUEST = array();
        global $fbm_test_options;
        $fbm_test_options = array();
    }

    public function testMissingNonceBlocked(): void {
        fbm_test_trust_nonces(false);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['fbm_action']       = 'branding_save';
        $this->expectException( RuntimeException::class );
        SettingsPage::route();
    }

    public function testUserWithoutCapBlocked(): void {
        fbm_test_reset_globals();
        fbm_test_trust_nonces(true);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        fbm_test_set_request_nonce('fbm_branding_save', '_fbm_nonce');
        $_POST['fbm_action'] = 'branding_save';
        $_REQUEST            = $_POST;
        $this->expectException( RuntimeException::class );
        SettingsPage::route();
    }

    public function testSuccessfulSaveSanitizes(): void {
        fbm_test_set_request_nonce('fbm_branding_save', '_fbm_nonce');
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST                     = array(
            'fbm_action' => 'branding_save',
            '_fbm_nonce' => $_POST['_fbm_nonce'],
            'branding'   => array(
                'site_name' => ' Test ',
                'logo_url'  => ' https://example.com/logo.png ',
                'color'     => ' Pink<script> ',
            ),
        );
        $_REQUEST = $_POST;
        try {
            SettingsPage::route();
        } catch ( RuntimeException $e ) {
            $this->assertSame( 'redirect', $e->getMessage() );
        }
        $this->assertSame( 'Test', Options::get( 'branding.site_name' ) );
        $this->assertSame( 'https://example.com/logo.png', Options::get( 'branding.logo_url' ) );
        $this->assertSame( 'default', Options::get( 'branding.color' ) );
        $this->assertStringContainsString( 'notice=saved', self::$redirect );
        $this->assertStringContainsString( 'tab=branding', self::$redirect );
    }
}
