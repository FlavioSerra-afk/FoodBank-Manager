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
    // capability handled via $GLOBALS['fbm_user_caps']
    if ( ! function_exists( 'check_admin_referer' ) ) {
        function check_admin_referer( string $action, string $name = '_fbm_nonce' ): void {
            if ( empty( $_POST[ $name ] ) ) {
                throw new \RuntimeException( 'nonce' );
            }
        }
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
    if ( ! function_exists( 'wp_safe_redirect' ) ) {
        function wp_safe_redirect( string $url, int $status = 302 ): void { GDPRPageTest::$redirect = $url; throw new \RuntimeException('redirect'); }
    }
    if ( ! function_exists( 'readfile' ) ) {
        function readfile( $filename ) { return ''; }
    }
}

namespace FBM\Exports {
    class SarExporter {
        public static array $last = array();
        public static function build_zip( array $subject, bool $masked ): string {
            self::$last = array( 'masked' => $masked, 'subject' => $subject );
            $tmp = tempnam(sys_get_temp_dir(), 'sar');
            $z = new \ZipArchive();
            $z->open($tmp, \ZipArchive::CREATE);
            $z->close();
            return $tmp;
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
        public static string $redirect = '';

        protected function setUp(): void {
            fbm_reset_globals();
            $GLOBALS['fbm_user_caps'] = ['fb_manage_diagnostics' => true];
            self::$redirect      = '';
            $_GET = $_POST = $_SERVER = array();
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
            if ( ! defined( 'FBM_PATH' ) ) { define( 'FBM_PATH', dirname( __DIR__, 2 ) . '/' ); }
            $_GET['email'] = 'user@example.com';
            ob_start();
            include FBM_PATH . 'templates/admin/gdpr.php';
            $html = ob_get_clean();
            $this->assertStringContainsString( 'Applications: 1', $html );
        }

        public function testExportRequiresNonce(): void {
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST['fbm_action'] = 'export';
            $this->expectException( \RuntimeException::class );
            GDPRPage::route();
        }

        public function testMaskedByDefault(): void {
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST['fbm_action'] = 'export';
            $_POST['_fbm_nonce'] = 'n';
            $_POST['app_id'] = '5';
            $_REQUEST = $_POST;
            try {
                GDPRPage::route();
            } catch ( \RuntimeException $e ) {
                $this->assertSame( 'redirect', $e->getMessage() );
            }
            $this->assertTrue( \FBM\Exports\SarExporter::$last['masked'] );
        }

        public function testUnmaskedWithCapability(): void {
            $GLOBALS['fbm_user_caps']['fb_view_sensitive'] = true;
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST['fbm_action'] = 'export';
            $_POST['_fbm_nonce'] = 'n';
            $_POST['app_id'] = '5';
            $_REQUEST = $_POST;
            try {
                GDPRPage::route();
            } catch ( \RuntimeException $e ) {
                $this->assertSame( 'redirect', $e->getMessage() );
            }
            $this->assertFalse( \FBM\Exports\SarExporter::$last['masked'] );
        }
    }
}
