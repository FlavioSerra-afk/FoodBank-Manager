<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Exports\PdfRenderer;

final class PdfRendererTest extends TestCase {
    public function testRenderProducesPdf(): void {
        $out = PdfRenderer::render('<p>Hello</p>');
        $this->assertNotSame('', $out);
        $this->assertStringStartsWith('%PDF', $out);
        $this->assertStringContainsString('xref', $out);
    }
}
