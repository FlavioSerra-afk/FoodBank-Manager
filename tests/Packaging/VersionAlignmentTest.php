<?php
/**
 * Version alignment checks.
 *
 * @package FoodBankManager\Tests
 */

declare(strict_types=1);

namespace FBM\Tests\Packaging;

use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
final class VersionAlignmentTest extends TestCase {
        public function test_version_markers_are_in_sync(): void {
                $root           = dirname( __DIR__, 2 );
                $plugin_file    = $root . '/foodbank-manager.php';
                $core_file      = $root . '/includes/Core/class-plugin.php';
                $readme_file    = $root . '/readme.txt';

                $plugin_version = $this->extract_plugin_version( $plugin_file );
                $core_version   = $this->extract_core_version( $core_file );
                $readme_version = $this->extract_readme_version( $readme_file );

                $this->assertNotSame( '', $plugin_version, 'Plugin file version header missing.' );
                $this->assertNotSame( '', $core_version, 'Core plugin version constant missing.' );
                $this->assertNotSame( '', $readme_version, 'Readme stable tag missing.' );

                $this->assertSame( $plugin_version, $core_version, 'Plugin header and core version differ.' );
                $this->assertSame( $plugin_version, $readme_version, 'Plugin header and readme version differ.' );
        }

        private function extract_plugin_version( string $path ): string {
                $contents = @file_get_contents( $path );

                if ( false === $contents ) {
                        return '';
                }

                if ( ! preg_match( '/^\s*\*\s*Version:\s*([^\s]+)$/m', $contents, $matches ) ) {
                        return '';
                }

                return trim( $matches[1] );
        }

        private function extract_core_version( string $path ): string {
                $contents = @file_get_contents( $path );

                if ( false === $contents ) {
                        return '';
                }

                if ( ! preg_match( "/VERSION\s*=\s*'([^']+)'/", $contents, $matches ) ) {
                        return '';
                }

                return trim( $matches[1] );
        }

        private function extract_readme_version( string $path ): string {
                $contents = @file_get_contents( $path );

                if ( false === $contents ) {
                        return '';
                }

                if ( ! preg_match( '/^Stable tag:\s*([^\s]+)$/m', $contents, $matches ) ) {
                        return '';
                }

                return trim( $matches[1] );
        }
}
