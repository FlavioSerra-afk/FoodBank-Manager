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
        public static bool $mail_result = true;
        /** @var array<string,int> */
        public static array $cron_next = array();

        protected function setUp(): void {
            fbm_test_reset_globals();
            fbm_grant_manager();
            fbm_test_trust_nonces(true);
            fbm_test_set_request_nonce();
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
            $this->assertStringContainsString( 'notice=sent', (string) $GLOBALS['__last_redirect'] );
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
            $this->assertStringContainsString( 'notice=error', (string) $GLOBALS['__last_redirect'] );
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
            $now            = time();
            self::$cron_next = array(
                'fbm_retention_tick'   => $now - 400,
                'fbm_cron_cleanup'     => $now + 100,
                'fbm_cron_email_retry' => $now + 100,
            );
            global $fbm_test_options;
            $fbm_test_options['fbm_retention_tick_last_run'] = 123;
            $fbm_test_options['cron'] = array(
                $now - 400 => array( 'fbm_retention_tick' => array() ),
               $now + 100 => array(
                    'fbm_cron_cleanup'     => array(),
                    'fbm_cron_email_retry' => array(),
                ),
            );
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
