<?php
/**
 * Registration editor template smoke tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Registration\Editor;

use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
final class RegistrationEditorTemplateTest extends TestCase {
        public function test_preview_modal_includes_debug_trace_toggle(): void {
                $template = file_get_contents(dirname(__DIR__, 3) . '/templates/admin/registration-editor.php');
                $this->assertNotFalse($template, 'Template should be readable.');
                $markup = (string) $template;
                $this->assertStringContainsString('data-fbm-preview-debug-toggle', $markup);
                $this->assertStringContainsString('data-fbm-preview-trace-toggle', $markup);
                $this->assertStringContainsString('data-fbm-import-diff', $markup);
                $this->assertStringContainsString('data-fbm-import-summary', $markup);
        }
}
