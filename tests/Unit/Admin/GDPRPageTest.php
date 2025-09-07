<?php
declare(strict_types=1);

namespace {
    use PHPUnit\Framework\TestCase;

    if ( ! function_exists( 'wp_unslash' ) ) {
        function wp_unslash( $value ) { return is_array( $value ) ? array_map( 'wp_unslash', $value ) : stripslashes( (string) $value ); }
    }
    if ( ! function_exists( 'sanitize_email' ) ) {
        function sanitize_email( $email ) { return filter_var( $email, FILTER_SANITIZE_EMAIL ); }
    }
    if ( ! function_exists( 'wp_die' ) ) {
        function wp_die( $msg = '' ) { throw new \RuntimeException( (string) $msg ); }
    }
    if ( ! function_exists( 'wp_nonce_field' ) ) {
        function wp_nonce_field( $action, $name ) {}
    }
    if ( ! function_exists( 'esc_html__' ) ) {
        function esc_html__( string $t, string $d = 'default' ): string { return $t; }
    }
    if ( ! function_exists( 'esc_html_e' ) ) {
        function esc_html_e( string $t, string $d = 'default' ): void { echo $t; }
    }
    if ( ! function_exists( 'esc_attr' ) ) {
        function esc_attr( $t ) { return (string) $t; }
    }
    if ( ! function_exists( 'esc_html' ) ) {
        function esc_html( $t ) { return (string) $t; }
    }
    if ( ! function_exists( 'esc_url_raw' ) ) {
        function esc_url_raw( $t ) { return (string) $t; }
    }
    if ( ! function_exists( 'sanitize_file_name' ) ) {
        function sanitize_file_name( $f ) { return preg_replace( '/[^A-Za-z0-9\.\-_]/', '', (string) $f ); }
    }
    if ( ! function_exists( 'add_query_arg' ) ) {
        function add_query_arg( array $args, string $url ): string { return $url . '?' . http_build_query( $args ); }
    }
    if ( ! function_exists( 'menu_page_url' ) ) {
        function menu_page_url( string $slug, bool $echo = true ): string { return 'admin.php?page=' . $slug; }
    }
}

namespace FBM\Exports {
    class SarExporter {
        public static array $last = array();
        public static function stream( array $subject, bool $masked, string $name ): void {
            self::$last = array( 'masked' => $masked, 'subject' => $subject, 'name' => $name );
            throw new \RuntimeException('stream');
        }
    }
}

namespace {
    use PHPUnit\Framework\TestCase;
    use FBM\Admin\GDPRPage;

    /**
     * @runTestsInSeparateProcesses
     */
    final class GDPRPageTest extends TestCase {
        protected function setUp(): void {
            fbm_test_reset_globals();
            fbm_grant_for_page('fbm_diagnostics');
            fbm_test_trust_nonces(true);
            $_GET = $_POST = $_SERVER = $_REQUEST = array();
            if (!class_exists('FoodBankManager\\Database\\ApplicationsRepo', false)) {
                require_once __DIR__ . '/../../Support/ApplicationsRepoStub.php';
            }
            if (!class_exists('FoodBankManager\\Attendance\\AttendanceRepo', false)) {
                require_once __DIR__ . '/../../Support/AttendanceRepoStub.php';
            }
            if (!class_exists('FBM\\Mail\\LogRepo', false)) {
                require_once __DIR__ . '/../../Support/LogRepoStub.php';
            }
        }

        public function testSearchPreview(): void {
            if ( ! defined( 'ABSPATH' ) ) { define( 'ABSPATH', __DIR__ ); }
            if ( ! defined( 'FBM_PATH' ) ) { define( 'FBM_PATH', dirname( __DIR__, 3 ) . '/' ); }
            $_GET['email'] = 'user@example.com';
            ob_start();
            include FBM_PATH . 'templates/admin/gdpr.php';
            $html = ob_get_clean();
            $this->assertStringContainsString( 'Applications: 1', $html );
        }

        public function testExportRequiresNonce(): void {
            fbm_test_trust_nonces(false);
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST['fbm_action'] = 'export';
            $_REQUEST            = $_POST;
            $this->expectException( \RuntimeException::class );
            GDPRPage::route();
        }

        public function testMaskedByDefault(): void {
            fbm_test_set_request_nonce('fbm_gdpr_export', '_fbm_nonce');
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST['fbm_action'] = 'export';
            $_POST['app_id'] = '5';
            $_REQUEST = $_POST;
            try {
                GDPRPage::route();
            } catch ( \RuntimeException $e ) {
                $this->assertSame( 'stream', $e->getMessage() );
            }
            $this->assertTrue( \FBM\Exports\SarExporter::$last['masked'] );
        }

        public function testUnmaskedWithCapability(): void {
            fbm_test_reset_globals();
            fbm_grant_caps(['fb_manage_diagnostics','fb_view_sensitive']);
            fbm_test_trust_nonces(true);
            fbm_test_set_request_nonce('fbm_gdpr_export', '_fbm_nonce');
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST['fbm_action'] = 'export';
            $_POST['app_id'] = '5';
            $_REQUEST = $_POST;
            try {
                GDPRPage::route();
            } catch ( \RuntimeException $e ) {
                $this->assertSame( 'stream', $e->getMessage() );
            }
            $this->assertFalse( \FBM\Exports\SarExporter::$last['masked'] );
        }
    }
}
