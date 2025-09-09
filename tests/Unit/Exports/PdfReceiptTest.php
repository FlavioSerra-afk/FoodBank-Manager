<?php
declare(strict_types=1);

namespace {
    use PHPUnit\Framework\TestCase;
    use FBM\Exports\PdfReceipt;

    final class PdfReceiptTest extends TestCase {
        protected function setUp(): void {
            parent::setUp();
        }

        public function testHtmlFallbackAndMasking(): void {
            if (class_exists('\\Mpdf\\Mpdf') || class_exists('\\TCPDF')) {
                $this->markTestSkipped('PDF engine present');
            }
            $entry = array('id'=>1,'email'=>'alice@example.com','postcode'=>'AB1 2CD');
            $res = PdfReceipt::build($entry);
            $this->assertContains('Content-Type: text/html; charset=utf-8', $res['headers']);
            $this->assertStringContainsString('<h1>Entry</h1>', $res['body']);
            $this->assertStringContainsString('a***@example.com', $res['body']);
            $res2 = PdfReceipt::build($entry, array('masked'=>false));
            $this->assertStringContainsString('alice@example.com', $res2['body']);
        }

        public function testPdfEnginePath(): void {
            if (!class_exists('\\Mpdf\\Mpdf') && !class_exists('\\TCPDF')) {
                eval('namespace Mpdf; class Mpdf { public function WriteHTML($h){} public function Output($d="", $m="S"){return "PDF";} }');
            }
            $entry = array('id'=>2);
            $res = PdfReceipt::build($entry, array('masked'=>false));
            $this->assertContains('Content-Type: application/pdf', $res['headers']);
            $this->assertNotSame('', $res['body']);
        }
    }
}
