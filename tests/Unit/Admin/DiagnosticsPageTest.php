<?php
declare(strict_types=1);

namespace {
    use PHPUnit\Framework\TestCase;
    use FoodBankManager\Admin\DiagnosticsPage;

    if ( ! defined( 'DAY_IN_SECONDS' ) ) {
        define( 'DAY_IN_SECONDS', 86400 );
    }

    if ( ! function_exists( 'wp_unslash' ) ) {
        function wp_unslash( $value ) {
            return is_array( $value ) ? array_map( 'wp_unslash', $value ) : stripslashes( (string) $value );
        }
    }
    // capability handled via $GLOBALS['fbm_user_caps']
    if ( ! function_exists( 'check_admin_referer' ) ) {
        function check_admin_referer( string $action, string $name = '_fbm_nonce' ): void {
            if ( empty( $_POST[ $name ] ) ) {
                throw new \RuntimeException( 'missing nonce' );
            }
        }
    }
    if ( ! function_exists( 'wp_die' ) ) {
        function wp_die( $message = '' ) {
            throw new \RuntimeException( (string) $message );
        }
    }
    if ( ! function_exists( 'menu_page_url' ) ) {
        function menu_page_url( string $slug, bool $echo = true ): string {
            return 'admin.php?page=' . $slug;
        }
    }
    if ( ! function_exists( 'add_query_arg' ) ) {
        function add_query_arg( array $args, string $url ): string {
            return $url . '?' . http_build_query( $args );
        }
    }
    if ( ! function_exists( 'add_settings_error' ) ) {
        function add_settings_error( $setting, $code, $message, $type = 'error' ) {}
    }
    if ( ! function_exists( 'wp_safe_redirect' ) ) {
        function wp_safe_redirect( string $url, int $status = 302 ): void {
            DiagnosticsPageTest::$redirect = $url;
            throw new \RuntimeException( 'redirect' );
        }
    }
    if ( ! function_exists( 'wp_mail' ) ) {
        function wp_mail( $to, $subject, $message ): bool {
            return DiagnosticsPageTest::$mail_result;
        }
    }
    if ( ! function_exists( 'esc_html__' ) ) {
        function esc_html__( string $text, string $domain = 'default' ): string {
            return $text;
        }
    }
    if ( ! function_exists( 'esc_html_e' ) ) {
        function esc_html_e( string $text, string $domain = 'default' ): void {
            echo $text;
        }
    }
    if ( ! function_exists( 'esc_attr' ) ) {
        function esc_attr( $text ) { return (string) $text; }
    }
    if ( ! function_exists( 'esc_html' ) ) {
        function esc_html( $text ) { return (string) $text; }
    }
    if ( ! function_exists( 'esc_url_raw' ) ) {
        function esc_url_raw( $url ) { return (string) $url; }
    }
    if ( ! function_exists( 'get_bloginfo' ) ) {
        function get_bloginfo( $show ) { return '6.5.0'; }
    }
    if ( ! function_exists( 'wp_nonce_field' ) ) {
        function wp_nonce_field( $action, $name ) {}
    }
    if ( ! function_exists( 'add_filter' ) ) {
        function add_filter( $tag, $func ) {}
    }
    if ( ! function_exists( 'remove_filter' ) ) {
        function remove_filter( $tag, $func ) {}
    }
    if ( ! function_exists( 'get_option' ) ) {
        function get_option( string $key, $default = false ) {
            global $fbm_test_options;
            return $fbm_test_options[ $key ] ?? $default;
        }
    }
    if ( ! function_exists( 'wp_next_scheduled' ) ) {
        function wp_next_scheduled( string $hook ) {
            return DiagnosticsPageTest::$cron_next[ $hook ] ?? false;
        }
    }
    if ( ! function_exists( 'wp_get_schedule' ) ) {
        function wp_get_schedule( string $hook ) {
            return 'daily';
        }
    }
    if ( ! function_exists( 'wp_get_schedules' ) ) {
        function wp_get_schedules() {
            return array( 'daily' => array( 'interval' => DAY_IN_SECONDS ) );
        }
    }
    if ( ! function_exists( 'sanitize_key' ) ) {
        function sanitize_key( $key ) { return preg_replace( '/[^a-z0-9_]/', '', strtolower( (string) $key ) ); }
    }
    if ( ! function_exists( '__' ) ) {
        function __( string $t, string $d = 'default' ): string { return $t; }
    }
}

namespace FoodBankManager\Auth {
    class Roles {
        public static bool $installed = false;
        public static bool $ensured  = false;
        public static function install(): void { self::$installed = true; }
        public static function ensure_admin_caps(): void { self::$ensured = true; }
    }
}

namespace FBM\Auth {
    class Capabilities {
        public static bool $ensured = false;
        public static function ensure_for_admin(): void { self::$ensured = true; }
        public static function all(): array {
            return [
                'fb_manage_dashboard','fb_manage_attendance','fb_manage_database','fb_manage_forms',
                'fb_manage_emails','fb_manage_settings','fb_manage_diagnostics','fb_manage_permissions',
                'fb_manage_theme','fb_view_sensitive'
            ];
        }
    }
}

namespace {
    use PHPUnit\Framework\TestCase;
    use FoodBankManager\Admin\DiagnosticsPage;

    /**
     * @runInSeparateProcess
     */
    final class DiagnosticsPageTest extends TestCase {
        public static string $redirect = '';
        public static bool $mail_result = true;
        /** @var array<string,int> */
        public static array $cron_next = array();

        protected function setUp(): void {
            fbm_test_reset_globals();
            fbm_grant_caps(['fb_manage_diagnostics']);
            self::$redirect   = '';
            self::$mail_result = true;
            \FoodBankManager\Auth\Roles::$installed = false;
            \FoodBankManager\Auth\Roles::$ensured  = false;
            $_POST   = array();
            $_SERVER = array();
            global $fbm_test_options;
            $fbm_test_options = array(
                'emails' => array(
                    'from_name'  => 'FoodBank',
                    'from_email' => 'from@example.com',
                ),
                'admin_email' => 'admin@example.com',
            );
        }

        public function testSendTestEmailSuccess(): void {
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST['_fbm_nonce']       = 'n';
            $_POST['fbm_action']       = 'mail_test';
            try {
                DiagnosticsPage::route();
            } catch ( \RuntimeException $e ) {
                $this->assertSame( 'redirect', $e->getMessage() );
            }
            $this->assertStringContainsString( 'notice=sent', self::$redirect );
        }

        public function testSendTestEmailFailure(): void {
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST['_fbm_nonce']       = 'n';
            $_POST['fbm_action']       = 'mail_test';
            self::$mail_result         = false;
            try {
                DiagnosticsPage::route();
            } catch ( \RuntimeException $e ) {
                $this->assertSame( 'redirect', $e->getMessage() );
            }
            $this->assertStringContainsString( 'notice=error', self::$redirect );
        }

        public function testTemplateRendersCrypto(): void {
            if ( ! defined( 'ABSPATH' ) ) {
                define( 'ABSPATH', __DIR__ );
            }
            if ( ! defined( 'FBM_PATH' ) ) {
                define( 'FBM_PATH', dirname( __DIR__, 3 ) . '/' );
            }
            $notices_render_count = \FoodBankManager\Admin\Notices::getRenderCount();
            $boot_status          = 'not recorded';
            $caps_count           = '0 / 0';
            ob_start();
            \FoodBankManager\Admin\DiagnosticsPage::render();
            $html = ob_get_clean();
            $this->assertStringContainsString( 'Crypto', $html );
            $this->assertStringContainsString( 'Environment', $html );
            $this->assertStringNotContainsString( 'from@example.com', $html );
        }

        public function testCronTableShowsOverdue(): void {
            if ( ! defined( 'DAY_IN_SECONDS' ) ) {
                define( 'DAY_IN_SECONDS', 86400 );
            }
            self::$cron_next = array(
                'fbm_retention_tick' => time() - 10,
                'fbm_cron_cleanup'   => time() + 100,
                'fbm_cron_email_retry' => time() + 100,
            );
            global $fbm_test_options;
            $fbm_test_options['fbm_retention_tick_last_run'] = 123;
            if ( ! defined( 'ABSPATH' ) ) {
                define( 'ABSPATH', __DIR__ );
            }
            if ( ! defined( 'FBM_PATH' ) ) {
                define( 'FBM_PATH', dirname( __DIR__, 3 ) . '/' );
            }
            $notices_render_count = \FoodBankManager\Admin\Notices::getRenderCount();
            $boot_status          = 'not recorded';
            $caps_count           = '0 / 0';
            ob_start();
            include FBM_PATH . 'templates/admin/diagnostics.php';
            $html = ob_get_clean();
            $this->assertStringContainsString( 'fbm_retention_tick', $html );
            $this->assertStringContainsString( '⚠️', $html );
            $this->assertStringContainsString( gmdate( 'Y-m-d', 123 ), $html );
        }

        public function testRepairCapsActionEnsuresCaps(): void {
            $_POST = [
                'fbm_action' => 'fbm_repair_caps',
                '_fbm_nonce' => wp_create_nonce('fbm_repair_caps'),
            ];
            DiagnosticsPage::render();
            $this->assertTrue(\FBM\Auth\Capabilities::$ensured);
        }
    }
}
