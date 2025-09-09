<?php
declare(strict_types=1);

namespace {
    use PHPUnit\Framework\TestCase;
    use FBM\Exports\XlsxWriter;

    final class XlsxWriterTest extends TestCase {
        private function requireZip(): void {
            if (!class_exists('\\ZipArchive')) {
                $this->markTestSkipped('ZipArchive not available');
            }
        }

        public function testBuildXlsx(): void {
            $this->requireZip();
            $columns = array('Name', 'Email');
            $rows    = array(array('Alice', 'a@example.com'));
            $res = XlsxWriter::build($columns, $rows);
            $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', implode(',', $res['headers']));
            $this->assertStringStartsWith('PK', $res['body']);
            $tmp = tempnam(sys_get_temp_dir(), 'fbm');
            file_put_contents($tmp, $res['body']);
            $zip = new \ZipArchive();
            $zip->open($tmp);
            $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
            $this->assertStringContainsString('<t>Name</t>', (string) $sheet);
            $this->assertStringContainsString('<t>Alice</t>', (string) $sheet);
            $zip->close();
            unlink($tmp);
        }
    }
}
