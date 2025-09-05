<?php
declare(strict_types=1);

namespace {
    if ( ! function_exists( 'sanitize_file_name' ) ) {
        function sanitize_file_name( string $filename ): string {
            return preg_replace( '/[^A-Za-z0-9\.\-_]/', '', $filename );
        }
    }
    if ( ! function_exists( 'wp_json_encode' ) ) {
        function wp_json_encode( $data ) { return json_encode( $data ); }
    }
}

namespace {
    use PHPUnit\Framework\TestCase;
    use FBM\Exports\SarExporter;

    /**
     * @runInSeparateProcess
     */
    final class SarExporterTest extends TestCase {
        protected function setUp(): void {
            parent::setUp();
            if (!class_exists(\ZipArchive::class)) {
                $this->markTestSkipped('ZipArchive not available');
            }
        }
        public function testMaskedExport(): void {
            $tmp = tempnam(sys_get_temp_dir(), 'fbm');
            file_put_contents($tmp, 'file');
            $subject = array(
                'applications' => array(
                    array(
                        'id' => 1,
                        'email' => 'alice@example.com',
                        'postcode' => 'E1 3AA',
                        'files' => array(
                            array('stored_path' => $tmp, 'original_name' => 'id.txt'),
                        ),
                    ),
                ),
            );
            $zip = SarExporter::build_zip($subject, true);
            $z = new \ZipArchive();
            if ($z->open($zip) !== true) {
                $this->markTestSkipped('ZipArchive not available');
            }
            $readme = $z->getFromName('README.txt');
            $this->assertStringContainsString('masked', $readme);
            $app = $z->getFromName('application-1.json');
            $this->assertStringContainsString('j***@example.com', $app);
            $manifest = $z->getFromName('manifest.csv');
            $this->assertStringContainsString('file', $manifest);
            $z->close();
        }

        public function testUnmaskedExport(): void {
            $tmp = tempnam(sys_get_temp_dir(), 'fbm');
            file_put_contents($tmp, 'file');
            $subject = array(
                'applications' => array(
                    array(
                        'id' => 2,
                        'email' => 'bob@example.com',
                        'files' => array(
                            array('stored_path' => $tmp, 'original_name' => 'note.txt'),
                        ),
                    ),
                ),
            );
            $zip = SarExporter::build_zip($subject, false);
            $z = new \ZipArchive();
            if ($z->open($zip) !== true) {
                $this->markTestSkipped('ZipArchive not available');
            }
            $app = $z->getFromName('application-2.json');
            $this->assertStringContainsString('bob@example.com', $app);
            $z->close();
        }
    }
}
