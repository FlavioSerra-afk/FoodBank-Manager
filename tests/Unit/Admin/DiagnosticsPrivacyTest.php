<?php
declare(strict_types=1);

use FoodBankManager\Admin\DiagnosticsPrivacy;

if ( ! class_exists( 'DiagPrivacyDBStub' ) ) {
    class DiagPrivacyDBStub {
        public string $prefix = 'wp_';
        /** @var array<string,list<array<string,mixed>>> */
        public array $data = array();
        /** @var array<int,mixed> */
        public array $last_args = array();
        /** @var list<string> */
        public array $queries = array();
        public function prepare( string $sql, ...$args ): string { $this->last_args = $args; return $sql; }
        public function get_results( $sql, $type = 'ARRAY_A' ): array {
            $email  = $this->last_args[0] ?? '';
            foreach ( $this->data as $table => $rows ) {
                if ( strpos( $sql, $table ) !== false ) {
                    return array_values( array_filter( $rows, fn( $r ) => $r['email'] === $email ) );
                }
            }
            return array();
        }
        public function get_col( $sql ) {
            $email  = $this->last_args[0] ?? '';
            foreach ( $this->data as $table => $rows ) {
                if ( strpos( $sql, $table ) !== false ) {
                    $rows = array_values( array_filter( $rows, fn( $r ) => $r['email'] === $email ) );
                    return array_column( $rows, 'id' );
                }
            }
            return array();
        }
        public function query( $sql ) { $this->queries[] = $sql; return true; }
    }
}

final class DiagnosticsPrivacyTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        global $wpdb;
        $wpdb       = new DiagPrivacyDBStub();
        $wpdb->data = array(
            'wp_fb_submissions' => array(
                array( 'id' => 1, 'email' => 'user@example.com', 'field' => 'a' ),
            ),
        );
    }

    public function testPanelRenders(): void {
        if ( ! defined( 'ABSPATH' ) ) {
            define( 'ABSPATH', __DIR__ );
        }
        if ( ! defined( 'FBM_PATH' ) ) {
            define( 'FBM_PATH', dirname( __DIR__, 2 ) . '/' );
        }
        ob_start();
        DiagnosticsPrivacy::render_panel();
        $html = (string) ob_get_clean();
        $this->assertStringContainsString( 'Privacy', $html );
    }

    public function testPreviewActionGeneratesSummary(): void {
        fbm_seed_nonce( 'unit-seed' );
        fbm_grant_manager();
        fbm_test_set_request_nonce( 'fbm_privacy_preview' );
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $nonce = $_POST['_wpnonce'];
        $_POST  = array(
            'fbm_privacy_action' => 'fbm_privacy_preview',
            'email'              => 'user@example.com',
            '_wpnonce'           => $nonce,
        );
        $_REQUEST = $_POST;
        DiagnosticsPrivacy::handle_actions();
        $summary = DiagnosticsPrivacy::preview_summary();
        $this->assertFalse( $summary['done'] );
        $this->assertSame( 'fbm_submissions', $summary['data'][0]['group_id'] );
    }
    public function testDryRunErasureSetsFlags(): void {
        fbm_seed_nonce( 'unit-seed' );
        fbm_grant_manager();
        fbm_test_set_request_nonce( 'fbm_privacy_erase_dry' );
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $nonce = $_POST['_wpnonce'];
        $_POST  = array(
            'fbm_privacy_action' => 'fbm_privacy_erase_dry',
            'email'              => 'user@example.com',
            '_wpnonce'           => $nonce,
        );
        $_REQUEST = $_POST;
        DiagnosticsPrivacy::handle_actions();
        $summary = DiagnosticsPrivacy::erasure_summary();
        $this->assertTrue( $summary['items_retained'] );
        $this->assertFalse( $summary['items_removed'] );
    }
}
