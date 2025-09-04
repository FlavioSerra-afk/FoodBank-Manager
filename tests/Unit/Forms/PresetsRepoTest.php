<?php
declare(strict_types=1);

namespace {
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
    if ( ! function_exists( 'delete_option' ) ) {
        function delete_option( string $key ): bool { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
            unset( $GLOBALS['fbm_options_store'][ $key ] );
            return true;
        }
    }
}

namespace FoodBankManager\Tests\Unit\Forms {

use FoodBankManager\Forms\PresetsRepo;
use PHPUnit\Framework\TestCase;

final class PresetsRepoTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        $GLOBALS['fbm_options_store'] = array();
    }

    public function testUpsertGetDelete(): void {
        $schema = array(
            'meta'   => array( 'name' => 'Test', 'slug' => 'my form', 'captcha' => false ),
            'fields' => array(
                array( 'id' => 'first', 'type' => 'text', 'label' => 'First', 'required' => true ),
            ),
        );
        PresetsRepo::upsert( $schema );
        $list = PresetsRepo::list();
        $this->assertCount( 1, $list );
        $this->assertSame( 'myform', $list[0]['slug'] );
        $preset = PresetsRepo::get_by_slug( 'myform' );
        $this->assertSame( 'Test', $preset['meta']['name'] );
        PresetsRepo::delete( 'myform' );
        $this->assertCount( 0, PresetsRepo::list() );
    }
}
}
