<?php
declare(strict_types=1);

namespace {
    if ( ! function_exists( 'shortcode_atts' ) ) {
        function shortcode_atts( array $pairs, array $atts, string $shortcode = '' ): array { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
            return array_merge( $pairs, $atts );
        }
    }
    if ( ! function_exists( 'sanitize_text_field' ) ) {
        function sanitize_text_field( $str ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
            return trim( strip_tags( (string) $str ) );
        }
    }
    if ( ! function_exists( 'sanitize_key' ) ) {
        function sanitize_key( $key ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
            return preg_replace( '/[^a-z0-9_]/', '', strtolower( (string) $key ) );
        }
    }
    if ( ! function_exists( 'esc_html__' ) ) {
        function esc_html__( string $text, string $domain = 'default' ): string { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
            return $text;
        }
    }
    if ( ! function_exists( '__' ) ) {
        function __( string $text, string $domain = 'default' ): string { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
            return $text;
        }
    }
    if ( ! function_exists( 'esc_html' ) ) {
        function esc_html( $text ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
            return htmlspecialchars( (string) $text, ENT_QUOTES );
        }
    }
    if ( ! function_exists( 'esc_attr' ) ) {
        function esc_attr( $text ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
            return htmlspecialchars( (string) $text, ENT_QUOTES );
        }
    }
    if ( ! function_exists( 'esc_url' ) ) {
        function esc_url( $url ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
            return (string) $url;
        }
    }
    if ( ! function_exists( 'esc_js' ) ) {
        function esc_js( $text ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
            return addslashes( (string) $text );
        }
    }
    if ( ! function_exists( 'wp_create_nonce' ) ) {
        function wp_create_nonce( string $action ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
            return 'nonce';
        }
    }
    if ( ! function_exists( 'admin_url' ) ) {
        function admin_url( string $path = '' ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
            return '/admin/' . ltrim( $path, '/' );
        }
    }
    if ( ! function_exists( 'wp_unslash' ) ) {
        function wp_unslash( $value ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
            return $value;
        }
    }
    if ( ! function_exists( 'get_transient' ) ) {
        function get_transient( string $key ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
            return false;
        }
    }
    if ( ! function_exists( 'delete_transient' ) ) {
        function delete_transient( string $key ): void { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
        }
    }
    if ( ! function_exists( 'get_option' ) ) {
        $GLOBALS['fbm_options_store'] = array();
        function get_option( string $key, $default = false ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
            return $GLOBALS['fbm_options_store'][ $key ] ?? $default;
        }
    }
    if ( ! function_exists( 'update_option' ) ) {
        function update_option( string $key, $value ): bool { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
            $GLOBALS['fbm_options_store'][ $key ] = $value;
            return true;
        }
    }
    if ( ! function_exists( 'wp_die' ) ) {
        function wp_die( $message = '' ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
            throw new \RuntimeException( (string) $message );
        }
    }
}

namespace FoodBankManager\UI {
    class Theme {
        public static function enqueue_front(): void {}
    }
}

namespace FoodBankManager\Security {
    class Helpers {
        public static function sanitize_text( string $text ): string {
            return \sanitize_text_field( $text );
        }
        public static function mask_email( string $email ): string {
            $parts = explode( '@', $email );
            if ( count( $parts ) !== 2 ) {
                return $email;
            }
            $local = $parts[0];
            $domain = $parts[1];
            return ( $local !== '' ? substr( $local, 0, 1 ) . '***' : '' ) . '@' . $domain;
        }
        public static function mask_postcode( string $pc ): string {
            $pc = trim( $pc );
            if ( strlen( $pc ) < 5 ) {
                return $pc;
            }
            return substr( $pc, 0, 2 ) . '* ' . substr( $pc, 4, 1 ) . '**';
        }
    }
}

namespace FoodBankManager\Admin {
    if ( ! function_exists( __NAMESPACE__ . '\\wp_die' ) ) {
        function wp_die( $message = '' ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
            throw new \RuntimeException( (string) $message );
        }
    }
    if ( ! function_exists( __NAMESPACE__ . '\\esc_html__' ) ) {
        function esc_html__( string $text, string $domain = 'default' ): string { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
            return $text;
        }
    }
    if ( ! function_exists( __NAMESPACE__ . '\\esc_html_e' ) ) {
        function esc_html_e( string $text, string $domain = 'default' ): void { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
            echo $text;
        }
    }
    if ( ! function_exists( __NAMESPACE__ . '\\esc_html' ) ) {
        function esc_html( $text ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
            return \esc_html( $text );
        }
    }
    if ( ! function_exists( __NAMESPACE__ . '\\esc_attr' ) ) {
        function esc_attr( $text ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
            return \esc_attr( $text );
        }
    }
    if ( ! function_exists( __NAMESPACE__ . '\\esc_url' ) ) {
        function esc_url( $url ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
            return $url;
        }
    }
}

namespace {
use PHPUnit\Framework\TestCase;
use FoodBankManager\Forms\Presets;
use FoodBankManager\Core\Options;
use FoodBankManager\Admin\FormsPage;
use FoodBankManager\Shortcodes\Form;

final class FormsPresetsTest extends TestCase {

    protected function setUp(): void {
        parent::setUp();
        fbm_test_reset_globals();
        $GLOBALS['fbm_options_store'] = array();
        if ( ! defined( 'FBM_PATH' ) ) {
            define( 'FBM_PATH', dirname( __DIR__, 2 ) . '/' );
        }
    }

    public function testResolveBuiltin(): void {
        $fields = Presets::resolve( 'basic_intake' );
        $this->assertNotEmpty( $fields );
        $this->assertSame( 'first_name', $fields[0]['name'] );
    }

    public function testOptionsSanitizeCustomPresets(): void {
        $raw = array(
            'custom' => array(
                array( 'name' => 'x', 'type' => 'text', 'label' => 'X', 'required' => true, 'bad' => 'zz' ),
                array( 'name' => 'y', 'type' => 'unknown', 'label' => 'Y' ),
            ),
        );
        Options::set_form_presets_custom( $raw );
        $stored = Options::get_form_presets_custom();
        $this->assertArrayHasKey( 'custom', $stored );
        $this->assertSame( 'x', $stored['custom'][0]['name'] );
        $this->assertArrayNotHasKey( 'bad', $stored['custom'][0] );
        $this->assertCount( 1, $stored['custom'] );
    }

    public function testFormsPageRequiresCap(): void {
        fbm_test_reset_globals();
        $this->expectException( \RuntimeException::class );
        FormsPage::route();
    }

    public function testFormsPageListsPresets(): void {
        fbm_test_reset_globals();
        fbm_grant_for_page('fbm_forms');
        ob_start();
        FormsPage::route();
        $html = (string) ob_get_clean();
        $this->assertStringContainsString( '[fbm_form preset', $html );
    }

    public function testShortcodePresetFallback(): void {
        $html = Form::render( array( 'preset' => 'nope' ) );
        $this->assertStringContainsString( 'name="name"', $html );
    }
}
}
