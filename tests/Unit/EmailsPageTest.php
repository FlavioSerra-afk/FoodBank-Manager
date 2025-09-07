<?php
declare(strict_types=1);

namespace FoodBankManager\Core {
    function wp_strip_all_tags( string $text ): string {
        return strip_tags( $text );
    }
    function wp_kses_post( $data ) {
        return strip_tags( (string) $data, '<p>' );
    }
}

namespace FoodBankManager\Admin {}

namespace {
use PHPUnit\Framework\TestCase;
use FoodBankManager\Admin\EmailsPage;
use FoodBankManager\Core\Options;

if ( ! function_exists( 'wp_unslash' ) ) {
    function wp_unslash( $value ) {
        return is_array( $value ) ? array_map( 'wp_unslash', $value ) : stripslashes( (string) $value );
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
if ( ! function_exists( 'wp_nonce_field' ) ) {
    function wp_nonce_field( $action, $name ) {}
}
if ( ! function_exists( 'esc_textarea' ) ) {
    function esc_textarea( $text ) {
        return htmlspecialchars( (string) $text, ENT_QUOTES );
    }
}
if ( ! function_exists( 'get_bloginfo' ) ) {
    function get_bloginfo( $show = '', $filter = 'raw' ) {
        return 'Test Site';
    }
}

if ( ! function_exists( 'wp_send_json' ) ) {
    function wp_send_json( $response ) {
        echo json_encode( $response );
        throw new RuntimeException( 'json' );
    }
}

final class EmailsPageTest extends TestCase {

    protected function setUp(): void {
        fbm_test_reset_globals();
        fbm_grant_for_page('fbm_emails');
        if ( ! defined( 'FBM_PATH' ) ) {
            define( 'FBM_PATH', dirname( __DIR__, 1 ) . '/../' );
        }
        if ( ! defined( 'ABSPATH' ) ) {
            define( 'ABSPATH', __DIR__ );
        }
        $_GET = array();
        $_POST = array();
        $_SERVER = array();
        global $fbm_test_options;
        $fbm_test_options = array();
    }

    public function testCapabilityRequired(): void {
        fbm_test_reset_globals();
        $this->expectException( RuntimeException::class );
        EmailsPage::route();
    }

    public function testListRenders(): void {
        ob_start();
        EmailsPage::route();
        $html = (string) ob_get_clean();
        $this->assertStringContainsString( 'tpl=applicant_confirmation', $html );
        $this->assertStringContainsString( 'tpl=admin_notification', $html );
    }

    public function testSaveMissingNonceBlocked(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = array(
            'fbm_action' => 'emails_save',
            'tpl'        => 'applicant_confirmation',
            'subject'    => 'Hi',
            'body_html'  => '<p>Hello</p>',
        );
        $this->expectException( RuntimeException::class );
        EmailsPage::route();
    }

    public function testSaveRequiresCapability(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = array(
            'fbm_action' => 'emails_save',
            '_fbm_nonce' => 'nonce',
            'tpl'        => 'applicant_confirmation',
            'subject'    => 'Hi',
            'body_html'  => '<p>Hello</p>',
        );
        fbm_test_reset_globals();
        $this->expectException( RuntimeException::class );
        EmailsPage::route();
    }

    public function testSuccessfulSaveSanitizes(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $long_subject = '<b>' . str_repeat( 'a', 300 ) . '</b>';
        $long_body    = '<script>alert(1)</script><p>' . str_repeat( 'b', 33000 ) . '</p>';
        $_POST        = array(
            'fbm_action' => 'emails_save',
            '_fbm_nonce' => 'nonce',
            'tpl'        => 'applicant_confirmation',
            'subject'    => $long_subject,
            'body_html'  => $long_body,
        );
        try {
            EmailsPage::route();
        } catch ( RuntimeException $e ) {
            $this->assertSame( 'redirect', $e->getMessage() );
        }
        $data = Options::get_template( 'applicant_confirmation' );
        $this->assertSame( 255, strlen( $data['subject'] ) );
        $this->assertStringNotContainsString( '<script', $data['body_html'] );
        $this->assertSame( 32768, strlen( $data['body_html'] ) );
        $this->assertStringContainsString( 'notice=saved', (string) $GLOBALS['__last_redirect'] );
        $this->assertStringContainsString( 'tpl=applicant_confirmation', (string) $GLOBALS['__last_redirect'] );
    }

    public function testPreviewMissingNonceBlocked(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = array(
            'fbm_action' => 'emails_preview',
            'tpl'        => 'applicant_confirmation',
        );
        unset( $_REQUEST['_fbm_nonce'] );
        $GLOBALS['fbm_test_trust_nonces'] = false;
        $this->expectException( RuntimeException::class );
        try {
            EmailsPage::route();
        } finally {
            $GLOBALS['fbm_test_trust_nonces'] = true;
        }
    }

    public function testPreviewRequiresCapability(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = array(
            'fbm_action' => 'emails_preview',
            '_fbm_nonce' => 'nonce',
            'tpl'        => 'applicant_confirmation',
        );
        fbm_test_reset_globals();
        $this->expectException( RuntimeException::class );
        EmailsPage::route();
    }

    public function testPreviewSubstitutesTokens(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = array(
            'fbm_action' => 'emails_preview',
            '_fbm_nonce' => 'nonce',
            'tpl'        => 'applicant_confirmation',
            'subject'    => 'Hi {first_name} {unknown}',
            'body_html'  => '<p>Hello {first_name} {unknown}</p><script>bad</script>',
            'fbm_ajax'   => '1',
        );
        ob_start();
        try {
            EmailsPage::route();
        } catch ( RuntimeException $e ) {
            $this->assertSame( 'json', $e->getMessage() );
        }
        $out = (string) ob_get_clean();
        $data = json_decode( $out, true );
        $this->assertSame( 'Hi *** {unknown}', $data['subject'] );
        $this->assertStringContainsString( '***', $data['body_html'] );
        $this->assertStringContainsString( '{unknown}', $data['body_html'] );
        $this->assertStringNotContainsString( '<script', $data['body_html'] );
    }

    public function testResetMissingNonceBlocked(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = array(
            'fbm_action' => 'emails_reset',
            'tpl'        => 'applicant_confirmation',
        );
        $this->expectException( RuntimeException::class );
        EmailsPage::route();
    }

    public function testResetRequiresCapability(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = array(
            'fbm_action' => 'emails_reset',
            '_fbm_nonce' => 'nonce',
            'tpl'        => 'applicant_confirmation',
        );
        fbm_test_reset_globals();
        $this->expectException( RuntimeException::class );
        EmailsPage::route();
    }

    public function testResetRestoresDefaults(): void {
        Options::set_template(
            'applicant_confirmation',
            array(
                'subject'   => 'Hi',
                'body_html' => '<p>Hello</p>',
            )
        );
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = array(
            'fbm_action' => 'emails_reset',
            '_fbm_nonce' => 'nonce',
            'tpl'        => 'applicant_confirmation',
        );
        try {
            EmailsPage::route();
        } catch ( RuntimeException $e ) {
            $this->assertSame( 'redirect', $e->getMessage() );
        }
        $data = Options::get_template( 'applicant_confirmation' );
        $this->assertSame( '', $data['subject'] );
        $this->assertSame( '', $data['body_html'] );
        $this->assertStringContainsString( 'notice=reset', (string) $GLOBALS['__last_redirect'] );
        $this->assertStringContainsString( 'tpl=applicant_confirmation', (string) $GLOBALS['__last_redirect'] );

        $_SERVER = array();
        $_GET['tpl'] = 'applicant_confirmation';
        ob_start();
        EmailsPage::route();
        $html = (string) ob_get_clean();
        $this->assertStringContainsString( 'We received your application', $html );
    }
}
}

