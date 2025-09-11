<?php
declare(strict_types=1);

use FBM\Privacy\Eraser;
use PHPUnit\Framework\TestCase;

if ( ! class_exists( 'PrivacyEraserDBStub' ) ) {
    class PrivacyEraserDBStub {
        public string $prefix = 'wp_';
        /** @var array<string,list<array<string,mixed>>> */
        public array $data = array();
        /** @var array<int,mixed> */
        public array $last_args = array();
        /** @var list<string> */
        public array $queries = array();
        public function prepare( string $sql, ...$args ): string { $this->last_args = $args; return $sql; }
        public function get_col( $sql ) {
            $email  = $this->last_args[0] ?? '';
            $limit  = (int) ( $this->last_args[1] ?? 50 );
            $offset = (int) ( $this->last_args[2] ?? 0 );
            foreach ( $this->data as $table => $rows ) {
                if ( strpos( $sql, $table ) !== false ) {
                    $rows = array_values( array_filter( $rows, fn( $r ) => $r['email'] === $email ) );
                    $slice = array_slice( $rows, $offset, $limit );
                    return array_column( $slice, 'id' );
                }
            }
            return array();
        }
        public function query( $sql ) { $this->queries[] = $sql; return true; }
    }
}

final class EraserTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        global $wpdb;
        $wpdb       = new PrivacyEraserDBStub();
        $wpdb->data = array(
            'wp_fb_submissions' => array(
                array( 'id' => 1, 'email' => 'user@example.com' ),
                array( 'id' => 2, 'email' => 'user@example.com' ),
                array( 'id' => 3, 'email' => 'other@example.com' ),
            ),
        );
    }

    public function testDryRunRetains(): void {
        global $wpdb;
        $res = Eraser::run( 'user@example.com', 1, true );
        $this->assertTrue( $res['items_retained'] );
        $this->assertFalse( $res['items_removed'] );
        $this->assertEmpty( $wpdb->queries );
    }

    public function testRealRunDeletes(): void {
        global $wpdb;
        $res = Eraser::run( 'user@example.com', 1, false );
        $this->assertTrue( $res['items_removed'] );
        $this->assertFalse( $res['items_retained'] );
        $this->assertNotEmpty( $wpdb->queries );
    }
}
