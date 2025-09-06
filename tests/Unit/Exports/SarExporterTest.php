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
    if ( ! function_exists( 'wp_delete_file' ) ) {
        function wp_delete_file( $file ) { return unlink($file); }
    }
    if ( ! function_exists( 'esc_html' ) ) {
        function esc_html( $t ) { return (string) $t; }
    }
}

namespace {
    use PHPUnit\Framework\TestCase;
    use FBM\Exports\SarExporter;

    /**
     * @runInSeparateProcess
     */
    final class SarExporterTest extends TestCase {
        private function requireZip(): void {
            if ( ! class_exists( \ZipArchive::class ) ) {
                $this->markTestSkipped( 'ZipArchive not available' );
            }
        }
        public function testMaskedExport(): void {
            $this->requireZip();
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
            $this->assertStringContainsString('a***@example.com', $app);
            $manifest = $z->getFromName('manifest.csv');
            $this->assertStringContainsString('file', $manifest);
            $z->close();
        }

        public function testUnmaskedExport(): void {
            $this->requireZip();
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

        public function testFilenameSanitized(): void {
            $this->requireZip();
            $subject = array(
                'applications' => array(
                    array(
                        'id' => 3,
                        'files' => array(
                            array('stored_path' => __FILE__, 'original_name' => "../e\x00vil.txt"),
                        ),
                    ),
                ),
            );
            $zip = SarExporter::build_zip($subject, true);
            $z = new \ZipArchive();
            $z->open($zip);
            $names = array();
            for ($i = 0; $i < $z->numFiles; $i++) {
                $names[] = $z->getNameIndex($i);
            }
            $z->close();
            foreach ($names as $name) {
                $this->assertStringNotContainsString('..', $name);
                $this->assertStringNotContainsString("\x00", $name);
            }
            $this->assertTrue((bool)preg_grep('/\.txt$/', $names));
        }

        public function testHtmlFallback(): void {
            $subject = array(
                'applications' => array(
                    array(
                        'id' => 4,
                        'files' => array(
                            array('original_name' => '../file.txt'),
                        ),
                    ),
                ),
            );
            $html = SarExporter::render_html($subject, true);
            $this->assertStringNotContainsString('..', $html);
            $this->assertMatchesRegularExpression('/[a-f0-9]{8}\.txt/', $html);
        }
    }
}
