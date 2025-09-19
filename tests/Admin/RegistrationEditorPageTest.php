<?php
/**
 * Registration editor admin page tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Admin;

use FoodBankManager\Admin\RegistrationEditorPage;
use PHPUnit\Framework\TestCase;

/**
 * @covers \FoodBankManager\Admin\RegistrationEditorPage
 */
final class RegistrationEditorPageTest extends TestCase {
        protected function setUp(): void {
                parent::setUp();

                $GLOBALS['fbm_settings_errors'] = array();
        }

        protected function tearDown(): void {
                $GLOBALS['fbm_settings_errors'] = array();

                parent::tearDown();
        }

        public function test_sanitize_template_strips_disallowed_markup(): void {
                $template = '<div class="wrap" data-info="example">[text* first-name]</div><script>alert(1)</script>';

                $sanitized = RegistrationEditorPage::sanitize_template( $template );

                $this->assertStringContainsString( 'data-info="example"', $sanitized );
                $this->assertStringNotContainsString( '<script', $sanitized );
        }

        public function test_sanitize_settings_normalizes_uploads_and_messages(): void {
                $raw = array(
                        'uploads'  => array(
                                'max_size_mb'        => '3',
                                'allowed_mime_types' => 'application/pdf, image/gif , image/jpeg',
                        ),
                        'honeypot' => '0',
                        'editor'   => array(
                                'theme' => 'dark',
                        ),
                        'messages' => array(
                                'success_auto' => '<strong>Thank you!</strong>',
                        ),
                );

                $settings = RegistrationEditorPage::sanitize_settings( $raw );

                $this->assertSame( 3 * 1048576, $settings['uploads']['max_size'] );
                $this->assertSame(
                        array( 'application/pdf', 'image/gif', 'image/jpeg' ),
                        $settings['uploads']['allowed_mime_types']
                );
                $this->assertFalse( $settings['honeypot'] );
                $this->assertSame( 'dark', $settings['editor']['theme'] );
                $this->assertSame(
                        '<strong>Thank you!</strong>',
                        $settings['messages']['success_auto']
                );
        }

        public function test_sanitize_template_adds_warnings_for_missing_fields(): void {
                $template = '<div>[text optional-field]</div>';

                RegistrationEditorPage::sanitize_template( $template );

                $this->assertNotEmpty( $GLOBALS['fbm_settings_errors'] );
        }

}
