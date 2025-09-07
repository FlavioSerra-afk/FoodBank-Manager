<?php
declare(strict_types=1);

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
