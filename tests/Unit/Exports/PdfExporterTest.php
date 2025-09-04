<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Exports\PdfExporter;

final class PdfExporterTest extends TestCase {
    public function testFallbackHtml(): void {
        $res = PdfExporter::render_entry( array( 'id' => 1 ), array() );
        $this->assertSame( 'text/html', $res['content_type'] );
        $this->assertStringContainsString( 'PDF engine not installed', $res['body'] );
        $this->assertStringContainsString( 'entry-1-' . gmdate( 'Ymd' ), $res['filename'] );
        $this->assertStringEndsWith( '.html', $res['filename'] );
    }

    public function testMpdfPdf(): void {
        if ( ! class_exists( '\\Mpdf\\Mpdf' ) ) {
            eval('namespace Mpdf; class Mpdf { public function WriteHTML(string $h): void {} public function Output(string $d="", string $n="S"){ return "PDF"; } }');
        }
        $res = PdfExporter::render_entry( array( 'id' => 2 ), array() );
        $this->assertSame( 'application/pdf', $res['content_type'] );
        $this->assertSame( 'PDF', $res['body'] );
        $this->assertStringContainsString( 'entry-2-' . gmdate( 'Ymd' ), $res['filename'] );
        $this->assertStringEndsWith( '.pdf', $res['filename'] );
    }
}
