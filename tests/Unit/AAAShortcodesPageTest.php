<?php
declare(strict_types=1);

namespace FoodBankManager\Admin {
    function wp_die( $message = '' ) {
        throw new \RuntimeException( (string) $message );
    }
    function esc_html__( string $text, string $domain = 'default' ): string {
        return $text;
    }
    function esc_html_e( string $text, string $domain = 'default' ): void {
        echo $text;
    }
    function esc_html( $text ) {
        return htmlspecialchars( (string) $text, ENT_QUOTES );
    }
    function esc_attr( $text ) {
        return htmlspecialchars( (string) $text, ENT_QUOTES );
    }
    function esc_url( $url ) {
        return (string) $url;
    }
    function wp_json_encode( $data ) {
        return json_encode( $data );
    }
    function do_shortcode( string $shortcode ): string {
        \ShortcodesPageTest::$last_shortcode = $shortcode;
        return '<div>ok</div><script>alert(1)</script>';
    }
    function wp_kses_post( $data ) {
        return strip_tags( (string) $data, '<div>' );
    }
    function __( string $text, string $domain = 'default' ): string {
        return $text;
    }
}

namespace {
    if ( ! function_exists( 'esc_html_e' ) ) {
        function esc_html_e( string $text, string $domain = 'default' ): void {
            echo $text;
        }
    }
    if ( ! function_exists( 'esc_html' ) ) {
        function esc_html( $text ) {
            return htmlspecialchars( (string) $text, ENT_QUOTES );
        }
    }
    if ( ! function_exists( 'esc_attr' ) ) {
        function esc_attr( $text ) {
            return htmlspecialchars( (string) $text, ENT_QUOTES );
        }
    }
    if ( ! function_exists( 'esc_url' ) ) {
        function esc_url( $url ) {
            return (string) $url;
        }
    }
    if ( ! function_exists( 'wp_json_encode' ) ) {
        function wp_json_encode( $data ) {
            return json_encode( $data );
        }
    }
    if ( ! function_exists( 'esc_html__' ) ) {
        function esc_html__( string $text, string $domain = 'default' ): string {
            return $text;
        }
    }
    if ( ! function_exists( 'esc_js' ) ) {
        function esc_js( $text ) {
            return addslashes( (string) $text );
        }
    }
    if ( ! function_exists( '__' ) ) {
        function __( string $text, string $domain = 'default' ): string {
            return $text;
        }
    }
    if ( ! function_exists( 'selected' ) ) {
        function selected( $value, $current, $echo = true ) {
            $res = $value === $current ? 'selected="selected"' : '';
            if ( $echo ) {
                echo $res;
            }
            return $res;
        }
    }
}

namespace {
use PHPUnit\Framework\TestCase;
use FoodBankManager\Admin\ShortcodesPage;

final class ShortcodesPageTest extends TestCase {
    public static string $last_shortcode = '';

    protected function setUp(): void {
        fbm_test_reset_globals();
        fbm_grant_for_page('fbm_shortcodes');
        fbm_test_trust_nonces(true);
        $_REQUEST = array();
        self::$last_shortcode = '';
        if ( ! defined( 'FBM_PATH' ) ) {
            define( 'FBM_PATH', dirname( __DIR__, 2 ) . '/' );
        }
    }

    protected function tearDown(): void {
        parent::tearDown();
        $_POST = array();
        $_SERVER['REQUEST_METHOD'] = 'GET';
        self::$last_shortcode = '';
    }

    public function testDiscoverShortcodesMetadata(): void {
        $shortcodes = ShortcodesPage::discover();
        $map = array();
        foreach ( $shortcodes as $sc ) {
            $map[ $sc['tag'] ] = $sc['atts'];
        }
        $this->assertArrayHasKey( 'fbm_form', $map );
        $this->assertSame( 'int', $map['fbm_form']['id']['type'] );
        $this->assertSame( '1', $map['fbm_form']['id']['default'] );
    }

    public function testCapabilityRequired(): void {
        fbm_test_reset_globals();
        $this->expectException( \RuntimeException::class );
        ShortcodesPage::route();
    }

    public function testInvalidNonceBlocksPreview(): void {
        fbm_test_trust_nonces(false);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['fbm_action']       = 'shortcode_preview';
        $_POST['tag']              = 'fbm_form';
        $this->expectException( \RuntimeException::class );
        ShortcodesPage::route();
    }

    public function testUnknownShortcodeRejected(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        fbm_test_set_request_nonce('fbm_shortcodes_preview');
        $_POST['fbm_action'] = 'shortcode_preview';
        $_POST['tag']        = 'fbm_bad';
        $_REQUEST            = $_POST;
        ob_start();
        ShortcodesPage::route();
        $html = (string) ob_get_clean();
        $this->assertStringContainsString( 'Invalid shortcode', $html );
        $this->assertSame( '', self::$last_shortcode );
    }

    public function testAttributeSanitizer(): void {
        $ref    = new \ReflectionClass( ShortcodesPage::class );
        $method = $ref->getMethod( 'sanitize_atts' );
        $method->setAccessible( true );
        $meta = array(
            'foo'    => array( 'type' => 'string', 'default' => 'def' ),
            'num'    => array( 'type' => 'int', 'default' => '1' ),
            'choice' => array( 'type' => 'enum', 'default' => 'a', 'options' => array( 'a', 'b' ) ),
        );
        $raw = array(
            'foo'    => '<b>' . str_repeat( 'x', 300 ),
            'num'    => '7abc',
            'choice' => 'c',
            'bad'    => 'zzz',
        );
        /** @var array<string,string> $res */
        $res = $method->invoke( null, $meta, $raw );
        $this->assertSame( '7', $res['num'] );
        $this->assertArrayNotHasKey( 'choice', $res );
        $this->assertArrayNotHasKey( 'bad', $res );
        $this->assertSame( 256, strlen( $res['foo'] ) );
    }

    public function testPreviewFiltersHtml(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        fbm_test_set_request_nonce('fbm_shortcodes_preview');
        $_POST = array(
            'fbm_action' => 'shortcode_preview',
            'tag'        => 'fbm_form',
            'atts'       => array( 'id' => '1' ),
            '_wpnonce'   => $_POST['_wpnonce'], // retained from helper
        );
        $_REQUEST = $_POST;
        ob_start();
        ShortcodesPage::route();
        $html = (string) ob_get_clean();
        $this->assertStringContainsString( '<div class="fbm-preview"><div>ok</div>alert(1)</div>', $html );
        $this->assertStringNotContainsString( '<script>alert', $html );
        $this->assertSame( '[fbm_form id="1" preset="basic_intake" mask_sensitive="true"]', self::$last_shortcode );
    }
}
}
