<?php
declare(strict_types=1);

namespace {
    use PHPUnit\Framework\TestCase;
    use FBM\Exports\PdfReceipt;

    final class ReceiptPdfTest extends TestCase {
        protected function setUp(): void {
            parent::setUp();
        }

        public function testMaskingByDefault(): void {
            $entry = array('id'=>1,'email'=>'alice@example.com','postcode'=>'AB1 2CD');
            $res   = PdfReceipt::build($entry);
            $this->assertContains('Content-Type: application/pdf', $res['headers']);
            $res2 = PdfReceipt::build($entry, array('masked'=>false));
            $this->assertNotSame($res['body'], $res2['body']);
        }
    }
}
