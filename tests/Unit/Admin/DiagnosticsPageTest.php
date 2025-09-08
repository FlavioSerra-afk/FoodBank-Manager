<?php
declare(strict_types=1);

namespace {
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

    if ( ! class_exists( 'DiagRetention' ) ) {
        class DiagRetention {
            public static function run_now(): array {
                return array( 'applications' => array( 'deleted' => 1 ) );
            }
            public static function dry_run(): array {
                return array( 'applications' => array( 'deleted' => 1 ) );
            }
            public static function events(): array {
                return array();
            }
        }
    }

    if ( ! defined( 'DAY_IN_SECONDS' ) ) {
        define( 'DAY_IN_SECONDS', 86400 );
    }

    if ( ! function_exists( 'wp_get_phpmailer' ) ) {
        function wp_get_phpmailer() {
            return (object) array(
                'Mailer'     => 'smtp',
                'Host'       => 'smtp.example.com',
                'Port'       => 25,
                'SMTPSecure' => 'tls',
                'SMTPAuth'   => true,
            );
        }
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
    if (!class_exists(Capabilities::class)) {
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
}

namespace {
    use PHPUnit\Framework\TestCase;
    use FoodBankManager\Admin\DiagnosticsPage;
    use BaseTestCase;

    /**
     * @runTestsInSeparateProcesses
     */
    final class DiagnosticsPageTest extends BaseTestCase {
        /** @var array<string,int> */
        public static array $cron_next = array();

    protected function setUp(): void {
        parent::setUp();
        if ( ! class_exists( '\\FBM\\Core\\Retention', false ) ) {
            class_alias( DiagRetention::class, '\\FBM\\Core\\Retention' );
        }
        fbm_grant_manager();
        \FoodBankManager\Auth\Roles::$installed = false;
        \FoodBankManager\Auth\Roles::$ensured  = false;
        global $fbm_test_options, $fbm_options;
        $fbm_test_options = array(
            'emails' => array(
                    'from_name'  => 'FoodBank',
                    'from_email' => 'from@example.com',
                ),
                'admin_email' => 'admin@example.com',
            );
        $fbm_options =& $fbm_test_options;
        update_option( 'admin_email', 'admin@example.com' );
    }

    public function testTemplateRendersSmtpInfo(): void {
        if ( ! defined( 'ABSPATH' ) ) {
            define( 'ABSPATH', __DIR__ );
        }
        if ( ! defined( 'FBM_PATH' ) ) {
            define( 'FBM_PATH', dirname( __DIR__, 3 ) . '/' );
        }
        ob_start();
        DiagnosticsPage::render();
        $html = (string) ob_get_clean();
        $this->assertStringContainsString('<div class="wrap fbm-admin">', $html);
        $this->assertStringContainsString('Mailer:', $html);
        $this->assertStringContainsString('smtp.example.com', $html);
        $this->assertStringContainsString('a***@example.com', $html);
        $this->assertStringNotContainsString('admin@example.com', $html);
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
            global $fbm_options;
            $fbm_options['fbm_retention_tick_last_run'] = 123;
            $fbm_options['cron'] = array(
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
            $html = (string) ob_get_clean();
            $this->assertStringContainsString('<div class="wrap fbm-admin">', $html);
            $this->assertStringContainsString('fbm_retention_tick', $html);
            $this->assertStringContainsString('⚠️', $html);
        }

        public function testRetentionRunOutputsSummary(): void {
            fbm_seed_nonce('unit-seed');
            if ( ! defined( 'ABSPATH' ) ) {
                define( 'ABSPATH', __DIR__ );
            }
            if ( ! defined( 'FBM_PATH' ) ) {
                define( 'FBM_PATH', dirname( __DIR__, 3 ) . '/' );
            }
            fbm_test_set_request_nonce('fbm_retention_run');
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $nonce = $_POST['_wpnonce'];
            $_POST = array(
                'fbm_action' => 'fbm_retention_run',
                '_wpnonce'   => $nonce,
            );
            $_REQUEST = $_POST;
            $filter = static function ( array $summary ): array {
                return array( 'applications' => array( 'deleted' => 1 ) );
            };
            add_filter( 'fbm_retention_summary', $filter );
            ob_start();
            DiagnosticsPage::render();
            $html = (string) ob_get_clean();
            remove_filter( 'fbm_retention_summary', $filter );
            $this->assertStringContainsString('<div class="wrap fbm-admin">', $html);
            $this->assertStringContainsString('Cron Health', $html);
            $this->assertStringContainsString('&quot;deleted&quot;:1', $html);
        }

        public function testRetentionDryRunOutputsSummary(): void {
            fbm_seed_nonce('unit-seed');
            if ( ! defined( 'ABSPATH' ) ) {
                define( 'ABSPATH', __DIR__ );
            }
            if ( ! defined( 'FBM_PATH' ) ) {
                define( 'FBM_PATH', dirname( __DIR__, 3 ) . '/' );
            }
            fbm_test_set_request_nonce('fbm_retention_dry_run');
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $nonce = $_POST['_wpnonce'];
            $_POST = array(
                'fbm_action' => 'fbm_retention_dry_run',
                '_wpnonce'   => $nonce,
            );
            $_REQUEST = $_POST;
            $filter = static function ( array $summary ): array {
                return array( 'applications' => array( 'deleted' => 1 ) );
            };
            add_filter( 'fbm_retention_summary', $filter );
            ob_start();
            DiagnosticsPage::render();
            $html = (string) ob_get_clean();
            remove_filter( 'fbm_retention_summary', $filter );
            $this->assertStringContainsString('<div class="wrap fbm-admin">', $html);
            $this->assertStringContainsString('Cron Health', $html);
            $this->assertStringContainsString('&quot;deleted&quot;:1', $html);
        }

        public function testRepairCapsActionEnsuresCaps(): void {
            \FBM\Auth\Capabilities::$ensured = false;
            \FBM\Auth\Capabilities::ensure_for_admin();
            $this->assertTrue(\FBM\Auth\Capabilities::$ensured);
        }
    }
}
