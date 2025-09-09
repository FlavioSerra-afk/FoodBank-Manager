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
            $tmp = tempnam(sys_get_temp_dir(), 'fbm');
            file_put_contents($tmp, $res['body']);
            $zip = new \ZipArchive();
            $zip->open($tmp);
            $this->assertNotFalse($zip->locateName('entry-1.html'));
            $this->assertNotFalse($zip->locateName('entry-2.html'));
            $c = $zip->getFromName('entry-1.html');
            $this->assertStringContainsString('a***@example.com', (string) $c);
            $zip->close();
            unlink($tmp);
        }

        public function testUnmasked(): void {
            $this->requireZip();
            $entries = array(
                array('id'=>1,'email'=>'a@example.com','postcode'=>'A1 1AA'),
            );
            $res = BulkPdfZip::build($entries, array('masked'=>false));
            $tmp = tempnam(sys_get_temp_dir(), 'fbm');
            file_put_contents($tmp, $res['body']);
            $zip = new \ZipArchive();
            $zip->open($tmp);
            $c = $zip->getFromName('entry-1.html');
            $this->assertStringContainsString('a@example.com', (string) $c);
            $zip->close();
            unlink($tmp);
        }
    }
}
