<?php
declare(strict_types=1);

namespace FoodBankManager\Admin {
    function current_user_can( string $cap ): bool {
        return \ShortcodesPageTest::$can;
    }
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
    function wp_kses_post( $data ) {
        return (string) $data;
    }
    function plugins_url( string $path, string $plugin ): string {
        return $path;
    }
    function wp_json_encode( $data ) {
        return json_encode( $data );
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
    if ( ! function_exists( 'wp_kses_post' ) ) {
        function wp_kses_post( $data ) {
            return (string) $data;
        }
    }
    if ( ! function_exists( 'plugins_url' ) ) {
        function plugins_url( string $path, string $plugin ): string {
            return $path;
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
    if ( ! function_exists( '__' ) ) {
        function __( string $text, string $domain = 'default' ): string {
            return $text;
        }
    }
}

namespace {
use PHPUnit\Framework\TestCase;
use FoodBankManager\Admin\ShortcodesPage;

final class ShortcodesPageTest extends TestCase {
    public static bool $can = true;

    protected function setUp(): void {
        self::$can = true;
        if ( ! defined( 'FBM_PATH' ) ) {
            define( 'FBM_PATH', dirname( __DIR__, 2 ) . '/' );
        }
        if ( ! defined( 'FBM_FILE' ) ) {
            define( 'FBM_FILE', FBM_PATH . 'foodbank-manager.php' );
        }
    }

    public function testDiscoverShortcodes(): void {
        $shortcodes = ShortcodesPage::discover();
        $tags       = array_column( $shortcodes, 'tag' );
        sort( $tags );
        $this->assertSame( array( 'fbm_attendance_manager', 'fbm_entries', 'fbm_form' ), $tags );
        $map = array();
        foreach ( $shortcodes as $sc ) {
            $map[ $sc['tag'] ] = $sc['atts'];
        }
        $this->assertSame( array( 'id' => '1' ), $map['fbm_form'] );
        $this->assertSame( array(), $map['fbm_entries'] );
        $this->assertSame( array(), $map['fbm_attendance_manager'] );
    }

    public function testCapabilityRequired(): void {
        self::$can = false;
        $this->expectException( \RuntimeException::class );
        ShortcodesPage::route();
    }

    public function testTemplateEscapes(): void {
        $shortcodes = array(
            array(
                'tag'  => 'fbm_form',
                'atts' => array( 'email' => '<script>bad</script>' ),
            ),
        );
        ob_start();
        /** @psalm-suppress UnresolvableInclude */
        include FBM_PATH . 'templates/admin/shortcodes.php';
        $html = (string) ob_get_clean();
        $this->assertStringNotContainsString( '<script>bad</script>', $html );
    }
}
}
