<?php
declare(strict_types=1);

namespace {
    use PHPUnit\Framework\TestCase;
    use FBM\Exports\PdfReceipt;

    final class ReceiptPdfTest extends TestCase {
        public function testMaskingAndEscaping(): void {
            $entry = array(
                'id' => 1,
                'name' => 'Alice "A"',
                'email' => 'alice@example.com',
                'phone' => '0123456789',
                'address' => '1 Some St',
            );
            $res = PdfReceipt::build($entry, array('return_html' => true));
            $this->assertStringContainsString('a***@example.com', (string) $res['html']);
            $this->assertStringContainsString('******6789', (string) $res['html']);
            $this->assertStringContainsString('* **** **', (string) $res['html']);
            $GLOBALS['fbm_user_caps']['fb_view_sensitive'] = true;
            $res2 = PdfReceipt::build($entry, array('unmask' => true, 'return_html' => true));
            $this->assertStringContainsString('alice@example.com', (string) $res2['html']);
            $this->assertStringContainsString('0123456789', (string) $res2['html']);
            $entry2 = array('id' => 2, 'name' => 'Bob "Quote"', 'email' => 'bob"quote"@example.com');
            $res3 = PdfReceipt::build($entry2, array('unmask' => true, 'return_html' => true));
            $this->assertStringContainsString('Bob &quot;Quote&quot;', (string) $res3['html']);
            $this->assertStringContainsString('bob&quot;quote&quot;@example.com', (string) $res3['html']);
        }
    }
}
