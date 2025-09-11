<?php
declare(strict_types=1);

namespace {
    use PHPUnit\Framework\TestCase;
    use FBM\Exports\BulkPdfZip;

    final class BulkPdfZipTest extends TestCase {
        private function requireZip(): void {
            if (!class_exists('\\ZipArchive')) {
                $this->markTestSkipped('ZipArchive not available');
            }
        }

        public function testBuildZipMasked(): void {
            $this->requireZip();
            $entries = array(
                array('id'=>1,'email'=>'a@example.com','postcode'=>'A1 1AA'),
                array('id'=>2,'email'=>'b@example.com','postcode'=>'B2 2BB'),
            );
            $res = BulkPdfZip::build($entries);
            $this->assertContains('Content-Type: application/zip', $res['headers']);
            $this->assertStringStartsWith('PK', $res['body']);
            $tmp = sys_get_temp_dir() . '/fbm_ut_' . uniqid('', true) . '.zip';
            file_put_contents($tmp, $res['body']);
            $zip = new \ZipArchive();
            $this->assertTrue($zip->open($tmp));
            $this->assertNotFalse($zip->locateName('receipts/entry-1.pdf'));
            $this->assertNotFalse($zip->locateName('receipts/entry-2.pdf'));
            $this->assertTrue($zip->close());
            unlink($tmp);
        }

        public function testUnmasked(): void {
            $this->requireZip();
            $entries = array(
                array('id'=>1,'email'=>'a@example.com','postcode'=>'A1 1AA'),
            );
            $res = BulkPdfZip::build($entries, array('masked'=>false));
            $this->assertStringStartsWith('PK', $res['body']);
            $tmp = sys_get_temp_dir() . '/fbm_ut_' . uniqid('', true) . '.zip';
            file_put_contents($tmp, $res['body']);
            $zip = new \ZipArchive();
            $this->assertTrue($zip->open($tmp));
            $this->assertNotFalse($zip->locateName('receipts/entry-1.pdf'));
            $this->assertTrue($zip->close());
            unlink($tmp);
        }
    }
}
