<?php
declare(strict_types=1);

use FBM\Privacy\Exporter;
use PHPUnit\Framework\TestCase;

if ( ! class_exists( 'PrivacyDBStub' ) ) {
    class PrivacyDBStub {
        public string $prefix = 'wp_';
        /** @var array<string,list<array<string,mixed>>> */
        public array $data = array();
        /** @var array<int,mixed> */
        public array $last_args = array();
        public function prepare( string $sql, ...$args ): string { $this->last_args = $args; return $sql; }
        public function get_results( $sql, $type = 'ARRAY_A' ): array {
            $email  = $this->last_args[0] ?? '';
            $limit  = (int) ( $this->last_args[1] ?? 50 );
            $offset = (int) ( $this->last_args[2] ?? 0 );
            foreach ( $this->data as $table => $rows ) {
                if ( strpos( $sql, $table ) !== false ) {
                    $rows = array_values( array_filter( $rows, fn( $r ) => $r['email'] === $email ) );
                    return array_slice( $rows, $offset, $limit );
                }
            }
            return array();
        }
    }
}

final class ExporterTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        global $wpdb;
        $wpdb       = new PrivacyDBStub();
        $wpdb->data = array(
            'wp_fb_submissions' => array(),
            'wp_fb_attendance'  => array(
                array( 'id' => 1, 'email' => 'user@example.com', 'note' => 'hi' ),
            ),
        );
        for ( $i = 1; $i <= 51; $i++ ) {
            $wpdb->data['wp_fb_submissions'][] = array( 'id' => $i, 'email' => 'user@example.com', 'field' => 'v' . $i );
        }
        $wpdb->data['wp_fb_submissions'][] = array( 'id' => 999, 'email' => 'other@example.com', 'field' => 'x' );
    }

    public function testPaginationAndFiltering(): void {
        $page1 = Exporter::export( 'user@example.com', 1 );
        $this->assertFalse( $page1['done'] );
        $this->assertCount( 51, $page1['data'] );
        $page2 = Exporter::export( 'user@example.com', 2 );
        $this->assertTrue( $page2['done'] );
        $this->assertCount( 1, $page2['data'] );
    }
}
