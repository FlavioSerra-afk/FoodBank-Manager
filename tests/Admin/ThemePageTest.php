<?php

declare(strict_types=1);

namespace FBM\Tests\Admin;

use FoodBankManager\Admin\ThemePage;
use PHPUnit\Framework\TestCase;
use WP_Error;

final class ThemePageTest extends TestCase {

        protected function setUp(): void {
                parent::setUp();

                $GLOBALS['fbm_options']       = array();
                $GLOBALS['fbm_last_redirect'] = null;
                $GLOBALS['fbm_test_nonces']   = array();
                $GLOBALS['fbm_current_caps']['fbm_manage'] = false;

                update_option( 'fbm_theme', ThemePage::default_theme() );

                $_POST    = array();
                $_GET     = array();
                $_REQUEST = array();
        }

        protected function tearDown(): void {
                $_POST    = array();
                $_GET     = array();
                $_REQUEST = array();

                $GLOBALS['fbm_last_redirect'] = null;
                $GLOBALS['fbm_test_nonces']   = array();
                $GLOBALS['fbm_current_caps']['fbm_manage'] = false;

                parent::tearDown();
        }

        public function testSanitizeRejectsInvalidPreset(): void {
                $payload = array(
                        'style'  => 'basic',
                        'preset' => 'unsupported',
                        'accent' => '#0B5FFF',
                        'glass'  => ThemePage::default_theme()['glass'],
                );

                $result = ThemePage::sanitize( $payload );

                $this->assertInstanceOf( WP_Error::class, $result );
                $this->assertSame( 'fbm_theme_invalid_preset', $result->get_error_code() );
        }

        public function testHandleSavePersistsValidPayload(): void {
                $GLOBALS['fbm_current_caps']['fbm_manage'] = true;

                $GLOBALS['fbm_test_nonces'] = array(
                        'fbm_theme_save' => 'valid-nonce',
                );

                $payload = array(
                        'style' => 'glass',
                        'preset' => 'dark',
                        'accent' => '#A1B2C3',
                        'glass' => array(
                                'alpha'  => '0.45',
                                'blur'   => '8',
                                'elev'   => '12',
                                'radius' => '18',
                                'border' => '2',
                        ),
                );

                $_POST = array(
                        'fbm_theme'       => $payload,
                        'fbm_theme_nonce' => 'valid-nonce',
                );

                $_REQUEST = $_POST;

                ThemePage::handle_save();

                $expected = ThemePage::sanitize( $payload );
                $this->assertIsArray( $expected );

                $stored = get_option( 'fbm_theme' );

                $this->assertSame( $expected, $stored );

                $redirect = $GLOBALS['fbm_last_redirect'] ?? null;
                $this->assertIsArray( $redirect );

                $parts = parse_url( $redirect['location'] ?? '' );
                $this->assertIsArray( $parts );

                $query = array();
                parse_str( $parts['query'] ?? '', $query );

                $this->assertSame( 'success', $query['fbm_theme_status'] ?? null );
                $this->assertSame( 'Theme settings saved.', $query['fbm_theme_message'] ?? null );
        }

        public function testHandleSaveRejectsInvalidPayload(): void {
                $GLOBALS['fbm_current_caps']['fbm_manage'] = true;

                $GLOBALS['fbm_test_nonces'] = array(
                        'fbm_theme_save' => 'invalid-test',
                );

                $_POST = array(
                        'fbm_theme'       => array(
                                'style' => 'basic',
                                'preset' => 'light',
                                'accent' => 'not-a-color',
                                'glass'  => array(),
                        ),
                        'fbm_theme_nonce' => 'invalid-test',
                );

                $_REQUEST = $_POST;

                ThemePage::handle_save();

                $stored = get_option( 'fbm_theme' );

                $this->assertSame( ThemePage::default_theme(), $stored );

                $redirect = $GLOBALS['fbm_last_redirect'] ?? null;
                $this->assertIsArray( $redirect );

                $parts = parse_url( $redirect['location'] ?? '' );
                $query = array();
                parse_str( $parts['query'] ?? '', $query );

                $this->assertSame( 'error', $query['fbm_theme_status'] ?? null );
                $this->assertSame( 'Accent color must be a valid hex value.', $query['fbm_theme_message'] ?? null );
        }

        public function testFilterAllowedOptionsRegistersTheme(): void {
                $options  = array();
                $filtered = ThemePage::filter_allowed_options( $options );

                $this->assertArrayHasKey( 'fbm_theme', $filtered );
                $this->assertContains( 'fbm_theme', $filtered['fbm_theme'] );
        }
}
