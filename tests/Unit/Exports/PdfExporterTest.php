<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Exports\PdfExporter;

final class PdfExporterTest extends TestCase {
    public function testRenderEntryHandlesEngines(): void {
        $res = PdfExporter::render_entry(['id' => 1], []);
        if (!class_exists('\\Mpdf\\Mpdf') && !class_exists('\\TCPDF')) {
            $this->assertSame('text/html', $res['content_type']);
            $this->assertStringContainsString('PDF engine not installed', $res['body']);
            $this->assertStringEndsWith('.html', $res['filename']);
        } else {
            $this->assertSame('application/pdf', $res['content_type']);
            $this->assertStringEndsWith('.pdf', $res['filename']);
        }
    }
}
