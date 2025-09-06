<?php
declare(strict_types=1);

namespace {
    use PHPUnit\Framework\TestCase;
    use FoodBankManager\Admin\DiagnosticsPage;

    if ( ! class_exists( 'DiagRetentionDBStub' ) ) {
        class DiagRetentionDBStub {
            public string $prefix = 'wp_';
            /** @var array<string,array<int>> */
            public array $ids = array(
                'wp_fb_applications' => array(),
                'wp_fb_attendance'   => array(),
                'wp_fb_mail_log'     => array(),
            );
            /** @var array<int,string> */
            public array $queries = array();
            public function prepare( string $sql, ...$args ): string { return $sql; }
            public function get_col( $sql, $col = 0 ) {
                foreach ( $this->ids as $table => $ids ) {
                    if ( strpos( $sql, $table ) !== false ) {
                        return $ids;
                    }
                }
                return array();
            }
            public function query( $sql ) { $this->queries[] = $sql; return true; }
            public function insert( string $table, array $data, $format = null ): bool { return true; }
        }
    }

    if ( ! defined( 'DAY_IN_SECONDS' ) ) {
        define( 'DAY_IN_SECONDS', 86400 );
    }

    if ( ! function_exists( 'wp_unslash' ) ) {
        function wp_unslash( $value ) {
            return is_array( $value ) ? array_map( 'wp_unslash', $value ) : stripslashes( (string) $value );
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
    if ( ! function_exists( 'settings_errors' ) ) {
        function settings_errors( $setting = '', $sanitize = false, $hide_on_update = false ) {}
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
    if ( ! function_exists( 'submit_button' ) ) {
        function submit_button( $text = '', $type = 'primary', $name = 'submit', $wrap = true ) {}
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
    if ( ! function_exists( 'current_time' ) ) {
        function current_time( $type, $gmt = false ) { return '2025-09-04 00:00:00'; }
    }
    if ( ! function_exists( 'get_current_user_id' ) ) {
        function get_current_user_id() { return 1; }
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
            fbm_grant_for_page('fbm_diagnostics');
            fbm_test_trust_nonces(true);
            self::$redirect   = '';
            self::$mail_result = true;
            \FoodBankManager\Auth\Roles::$installed = false;
            \FoodBankManager\Auth\Roles::$ensured  = false;
            $_POST = $_SERVER = $_REQUEST = array();
            global $fbm_test_options, $fbm_options;
            $fbm_test_options = array(
                'emails' => array(
                    'from_name'  => 'FoodBank',
                    'from_email' => 'from@example.com',
                ),
                'admin_email' => 'admin@example.com',
            );
            $fbm_options =& $fbm_test_options;
        }

        public function testSendTestEmailSuccess(): void {
            fbm_test_set_request_nonce('fbm_diag_mail_test', '_fbm_nonce');
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST['fbm_action']       = 'mail_test';
            $_REQUEST                  = $_POST;
            try {
                DiagnosticsPage::route();
            } catch ( \RuntimeException $e ) {
                $this->assertSame( 'redirect', $e->getMessage() );
            }
            $this->assertStringContainsString( 'notice=sent', self::$redirect );
        }

        public function testSendTestEmailFailure(): void {
            fbm_test_set_request_nonce('fbm_diag_mail_test', '_fbm_nonce');
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST['fbm_action']       = 'mail_test';
            self::$mail_result         = false;
            $_REQUEST                  = $_POST;
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
                'fbm_retention_tick'   => time() - 10,
                'fbm_cron_cleanup'     => time() + 100,
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
            ob_start();
            DiagnosticsPage::render();
            $html = ob_get_clean();
            $this->assertStringContainsString( 'fbm_retention_tick', $html );
            $this->assertStringContainsString( '⚠️', $html );
        }

        public function testRetentionRunOutputsSummary(): void {
            fbm_test_set_request_nonce('fbm_retention_run');
            $_POST['fbm_action'] = 'fbm_retention_run';
            $_REQUEST            = $_POST;
            if ( ! defined( 'ABSPATH' ) ) {
                define( 'ABSPATH', __DIR__ );
            }
            if ( ! defined( 'FBM_PATH' ) ) {
                define( 'FBM_PATH', dirname( __DIR__, 3 ) . '/' );
            }
            global $fbm_test_options, $fbm_options, $wpdb;
            $fbm_test_options['fbm_settings'] = array(
                'privacy' => array(
                    'retention' => array(
                        'applications' => array('days' => 1, 'policy' => 'delete'),
                        'attendance'   => array('days' => 1, 'policy' => 'anonymise'),
                        'mail'         => array('days' => 1, 'policy' => 'delete'),
                    ),
                ),
            );
            $fbm_options =& $fbm_test_options;
            $wpdb = new DiagRetentionDBStub();
            $wpdb->ids['wp_fb_applications'] = array(1);
            $wpdb->ids['wp_fb_attendance']   = array(1);
            $wpdb->ids['wp_fb_mail_log']     = array();
            ob_start();
            DiagnosticsPage::render();
            $html = ob_get_clean();
            $this->assertStringContainsString( 'applications', $html );
        }

        public function testRetentionDryRunOutputsSummary(): void {
            fbm_test_set_request_nonce('fbm_retention_dry_run');
            $_POST['fbm_action'] = 'fbm_retention_dry_run';
            $_REQUEST            = $_POST;
            if ( ! defined( 'ABSPATH' ) ) {
                define( 'ABSPATH', __DIR__ );
            }
            if ( ! defined( 'FBM_PATH' ) ) {
                define( 'FBM_PATH', dirname( __DIR__, 3 ) . '/' );
            }
            global $fbm_test_options, $fbm_options, $wpdb;
            $fbm_test_options['fbm_settings'] = array(
                'privacy' => array(
                    'retention' => array(
                        'applications' => array('days' => 1, 'policy' => 'delete'),
                        'attendance'   => array('days' => 1, 'policy' => 'anonymise'),
                        'mail'         => array('days' => 1, 'policy' => 'delete'),
                    ),
                ),
            );
            $fbm_options =& $fbm_test_options;
            $wpdb = new DiagRetentionDBStub();
            $wpdb->ids['wp_fb_applications'] = array(1);
            $wpdb->ids['wp_fb_attendance']   = array(1);
            $wpdb->ids['wp_fb_mail_log']     = array();
            ob_start();
            DiagnosticsPage::render();
            $html = ob_get_clean();
            $this->assertStringContainsString( 'applications', $html );
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
