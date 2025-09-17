<?php
/**
 * Packaging script integration tests.
 *
 * @package FoodBankManager\Tests
 */

declare(strict_types=1);

namespace FBM\Tests\Packaging;

use PHPUnit\Framework\TestCase;
use ZipArchive;

/**
 * @coversNothing
 */
final class PackageScriptTest extends TestCase {
        public function test_package_script_creates_manifest_matching_zip(): void {
                $root  = dirname( __DIR__, 2 );
                $dist  = $root . '/dist';
                $build = $root . '/build';

                $this->delete_directory( $dist );
                $this->delete_directory( $build );

                $command = sprintf(
                        'cd %s && FBM_PACKAGE_USE_LOCAL_VENDOR=1 bash bin/package.sh',
                        escapeshellarg( $root )
                );

                exec( $command, $output, $exit_code );

                $this->assertSame( 0, $exit_code, 'Packaging script failed: ' . implode( PHP_EOL, $output ) );

                $zip_path      = $dist . '/foodbank-manager.zip';
                $manifest_path = $dist . '/foodbank-manager-manifest.txt';

                $this->assertFileExists( $zip_path );
                $this->assertFileExists( $manifest_path );

                $manifest_entries = file( $manifest_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
                $this->assertIsArray( $manifest_entries );

                $zip_entries = $this->read_zip_entries( $zip_path );

                sort( $manifest_entries );
                sort( $zip_entries );

                $this->assertSame( $zip_entries, $manifest_entries, 'Manifest should reflect the packaged files exactly.' );
        }

        /**
         * @param string $path Absolute path to the ZIP archive.
         *
         * @return array<int, string>
         */
        private function read_zip_entries( string $path ): array {
                $zip = new ZipArchive();

                $opened = $zip->open( $path );
                $this->assertTrue( $opened, 'Unable to open package archive.' );

                $entries = array();

                for ( $i = 0; $i < $zip->numFiles; $i++ ) {
                        $name = $zip->getNameIndex( $i );

                        if ( false === $name ) {
                                continue;
                        }

                        $entries[] = $name;
                }

                $zip->close();

                return $entries;
        }

        /**
         * @param string $path Directory to remove if present.
         */
        private function delete_directory( string $path ): void {
                if ( ! is_dir( $path ) ) {
                        return;
                }

                $items = scandir( $path );

                if ( false === $items ) {
                        return;
                }

                foreach ( $items as $item ) {
                        if ( '.' === $item || '..' === $item ) {
                                continue;
                        }

                        $target = $path . '/' . $item;

                        if ( is_dir( $target ) ) {
                                $this->delete_directory( $target );
                        } else {
                                unlink( $target );
                        }
                }

                rmdir( $path );
        }
}
