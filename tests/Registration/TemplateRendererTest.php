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
}
