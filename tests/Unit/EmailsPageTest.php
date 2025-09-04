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

namespace FoodBankManager\Admin {
    function wp_safe_redirect( string $url, int $status = 302 ): void {
        \EmailsPageTest::$redirect = $url;
        if ( class_exists( '\\DiagnosticsPageTest', false ) ) {
            \DiagnosticsPageTest::$redirect = $url;
        }
        if ( class_exists( '\\SettingsPageTest', false ) ) {
            \SettingsPageTest::$redirect = $url;
        }
        throw new \RuntimeException( 'redirect' );
    }
}

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

final class EmailsPageTest extends TestCase {
    public static string $redirect = '';

    protected function setUp(): void {
        if ( ! defined( 'FBM_PATH' ) ) {
            define( 'FBM_PATH', dirname( __DIR__, 1 ) . '/../' );
        }
        if ( ! defined( 'ABSPATH' ) ) {
            define( 'ABSPATH', __DIR__ );
        }
        $_GET = array();
        $_POST = array();
        $_SERVER = array();
        \ShortcodesPageTest::$can = true;
        self::$redirect = '';
        global $fbm_test_options;
        $fbm_test_options = array();
    }

    public function testCapabilityRequired(): void {
        \ShortcodesPageTest::$can = false;
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
        \ShortcodesPageTest::$can = false;
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
        $this->assertStringContainsString( 'notice=saved', self::$redirect );
        $this->assertStringContainsString( 'tpl=applicant_confirmation', self::$redirect );
    }
}
}

