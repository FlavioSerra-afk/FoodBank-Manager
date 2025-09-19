<?php
/**
 * Readme header validation.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Release;

use FoodBankManager\Core\Plugin;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
final class ReadmeTest extends TestCase {
        public function test_header_contains_required_fields(): void {
                $headers = $this->readme_headers();

                $this->assertArrayHasKey( 'Requires at least', $headers );
                $this->assertNotSame( '', $headers['Requires at least'] );

                $this->assertArrayHasKey( 'Requires PHP', $headers );
                $this->assertNotSame( '', $headers['Requires PHP'] );

                $this->assertArrayHasKey( 'Tested up to', $headers );
                $this->assertNotSame( '', $headers['Tested up to'] );

                $this->assertArrayHasKey( 'Stable tag', $headers );
                $this->assertNotSame( '', $headers['Stable tag'] );
        }

        public function test_stable_tag_matches_plugin_version(): void {
                $headers = $this->readme_headers();

                $this->assertSame( Plugin::VERSION, $headers['Stable tag'] );
        }

        /**
         * Parse the readme header into an associative array.
         *
         * @return array<string,string>
         */
        private function readme_headers(): array {
                $path = FBM_PATH . 'readme.txt';
                $raw  = file_get_contents( $path );

                $this->assertIsString( $raw, 'Unable to read readme.txt' );

                $headers = array();

                foreach ( preg_split( '/\r?\n/', $raw ) as $line ) {
                        $line = trim( $line );
                        if ( '' === $line ) {
                                break;
                        }

                        if ( str_starts_with( $line, '===' ) ) {
                                continue;
                        }

                        if ( ! str_contains( $line, ':' ) ) {
                                continue;
                        }

                        list( $key, $value ) = array_map( 'trim', explode( ':', $line, 2 ) );
                        $headers[ $key ]     = $value;
                }

                return $headers;
        }
}
