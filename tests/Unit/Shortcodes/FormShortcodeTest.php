<?php
declare(strict_types=1);

namespace {
    if ( ! function_exists( 'shortcode_atts' ) ) {
        function shortcode_atts( array $pairs, array $atts, string $shortcode = '' ): array { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
            return array_merge( $pairs, $atts );
        }
    }
    if ( ! function_exists( 'sanitize_key' ) ) {
        function sanitize_key( $key ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
            return preg_replace( '/[^a-z0-9_]/', '', strtolower( (string) $key ) );
        }
    }
    if ( ! function_exists( 'sanitize_text_field' ) ) {
        function sanitize_text_field( $str ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
            return trim( strip_tags( (string) $str ) );
        }
    }
    if ( ! function_exists( 'esc_html__' ) ) {
        function esc_html__( string $text, string $domain = 'default' ): string { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
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
    if ( ! function_exists( 'admin_url' ) ) {
        function admin_url( string $path = '' ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
            return '/admin/' . ltrim( $path, '/' );
        }
    }
    if ( ! function_exists( 'wp_create_nonce' ) ) {
        function wp_create_nonce( string $action ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
            return 'nonce';
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
}

namespace FoodBankManager\UI {
    if ( ! class_exists( Theme::class ) ) {
        class Theme {
            public static function enqueue_front(): void {}
        }
    }
}

namespace FoodBankManager\Tests\Unit\Shortcodes {

use FoodBankManager\Forms\PresetsRepo;
use FoodBankManager\Shortcodes\FormShortcode;
use PHPUnit\Framework\TestCase;

final class FormShortcodeTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        $GLOBALS['fbm_options_store'] = array();
        if ( ! defined( 'FBM_PATH' ) ) {
            define( 'FBM_PATH', dirname( __DIR__, 3 ) . '/' );
        }
        $schema = array(
            'meta'   => array( 'name' => 'Test', 'slug' => 'test', 'captcha' => true ),
            'fields' => array(
                array( 'id' => 'first', 'type' => 'text', 'label' => 'First', 'required' => true ),
            ),
        );
        PresetsRepo::upsert( $schema );
    }

    public function testRendersCaptchaWhenEnabled(): void {
        $html = FormShortcode::render( array( 'preset' => 'test' ) );
        $this->assertStringContainsString( 'name="captcha"', $html );
    }

    public function testNoCaptchaWhenDisabled(): void {
        $GLOBALS['fbm_options_store']['forms.captcha_provider'] = 'off';
        $schema = array(
            'meta'   => array( 'name' => 'NoCap', 'slug' => 'nocap', 'captcha' => false ),
            'fields' => array(
                array( 'id' => 'first', 'type' => 'text', 'label' => 'First', 'required' => true ),
            ),
        );
        PresetsRepo::upsert( $schema );
        $html = FormShortcode::render( array( 'preset' => 'nocap' ) );
        $this->assertStringNotContainsString( 'name="captcha"', $html );
    }
}
}
