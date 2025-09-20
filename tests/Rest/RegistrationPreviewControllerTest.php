<?php
// phpcs:ignoreFile
/**
 * Registration preview controller tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Rest;

use FoodBankManager\Rest\RegistrationPreviewController;
use PHPUnit\Framework\TestCase;
use WP_REST_Request;

/**
 * @covers \FoodBankManager\Rest\RegistrationPreviewController
 */
final class RegistrationPreviewControllerTest extends TestCase {
        protected function setUp(): void {
                parent::setUp();

                $GLOBALS['fbm_current_caps']['fbm_manage'] = false;
                $GLOBALS['fbm_test_nonces']['wp_rest']     = 'valid-rest';
        }

        public function test_can_preview_requires_valid_nonce_and_capability(): void {
                $request = new WP_REST_Request();
                $request->set_header( 'X-WP-Nonce', 'invalid' );

                $this->assertFalse( RegistrationPreviewController::can_preview( $request ) );

                $GLOBALS['fbm_current_caps']['fbm_manage'] = true;
                $request->set_header( 'X-WP-Nonce', 'valid-rest' );

                $this->assertTrue( RegistrationPreviewController::can_preview( $request ) );
        }

        public function test_handle_preview_returns_sanitized_markup(): void {
                $GLOBALS['fbm_current_caps']['fbm_manage'] = true;

                $request = new WP_REST_Request(
                        array( 'template' => '<div>[text* first-name]</div><script>alert(1)</script>' ),
                        array( 'X-WP-Nonce' => 'valid-rest' )
                );

                $response = RegistrationPreviewController::handle_preview( $request );
                $this->assertSame( 200, $response->get_status() );
                $data     = $response->get_data();

                $this->assertArrayHasKey( 'markup', $data );
                $this->assertArrayHasKey( 'warnings', $data );
                $this->assertArrayHasKey( 'nonce', $data );
                $this->assertStringContainsString( 'fbm-registration-preview', $data['markup'] );
                $this->assertStringContainsString( '<input', $data['markup'] );
                $this->assertStringNotContainsString( '<script', $data['markup'] );
                $this->assertSame( 'nonce-fbm_registration_preview_modal', $data['nonce'] );
        }
}
