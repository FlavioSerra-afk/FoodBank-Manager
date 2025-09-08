<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Admin\SettingsPage;
use FoodBankManager\Core\Options;

final class SettingsPageTest extends TestCase {

    protected function setUp(): void {
        parent::setUp();
        fbm_test_reset_globals();
        fbm_grant_manager();
        fbm_test_trust_nonces(true);
        fbm_test_set_request_nonce('fbm_branding_save', '_fbm_nonce');
        $_POST = $_SERVER = $_REQUEST = array();
        global $fbm_test_options;
        $fbm_test_options = array();
    }

    public function testMissingNonceBlocked(): void {
        fbm_test_trust_nonces(false);
        unset($_POST['_fbm_nonce'], $_REQUEST['_fbm_nonce']);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['fbm_action']       = 'branding_save';
        $this->expectException( RuntimeException::class );
        SettingsPage::route();
    }

    public function testUserWithoutCapBlocked(): void {
        fbm_test_reset_globals();
        fbm_test_trust_nonces(true);
        fbm_test_set_request_nonce('fbm_branding_save', '_fbm_nonce');
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['fbm_action'] = 'branding_save';
        $_REQUEST            = $_POST;
        $this->expectException( RuntimeException::class );
        SettingsPage::route();
    }

    /** @runInSeparateProcess */
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
        $this->assertStringContainsString( 'notice=saved', (string) $GLOBALS['__last_redirect'] );
        $this->assertStringContainsString( 'tab=branding', (string) $GLOBALS['__last_redirect'] );
    }
}
