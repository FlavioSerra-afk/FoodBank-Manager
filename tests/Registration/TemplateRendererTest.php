<?php
/**
 * Template renderer tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Registration;

use FoodBankManager\Registration\Editor\TemplateRenderer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \FoodBankManager\Registration\Editor\TemplateRenderer
 */
final class TemplateRendererTest extends TestCase {
        public function test_sanitize_template_removes_disallowed_tags(): void {
                $template = '<div data-role="wrapper"><span>Field</span></div><script>alert(1)</script>';

                $sanitized = TemplateRenderer::sanitize_template( $template );

                $this->assertStringContainsString( '<div data-role="wrapper">', $sanitized );
                $this->assertStringNotContainsString( '<script', $sanitized );
        }

        public function test_sanitize_template_preserves_allowed_attributes(): void {
                $template = '<section class="wrap" aria-label="Info" data-panel="true"><h2>Heading</h2><ul><li>Item</li></ul><br /></section>';

                $sanitized = TemplateRenderer::sanitize_template( $template );

                $this->assertStringContainsString( 'aria-label="Info"', $sanitized );
                $this->assertStringContainsString( 'data-panel="true"', $sanitized );
                $this->assertStringContainsString( '<ul><li>Item</li></ul>', $sanitized );
                $this->assertStringContainsString( '<br />', $sanitized );
        }

        public function test_sanitize_template_strips_unsafe_attributes(): void {
                $template = '<div onclick="alert(1)" style="color:red"><p>Test</p></div>';

                $sanitized = TemplateRenderer::sanitize_template( $template );

                $this->assertStringNotContainsString( 'onclick', $sanitized );
                $this->assertStringNotContainsString( 'style=', $sanitized );
        }
}
