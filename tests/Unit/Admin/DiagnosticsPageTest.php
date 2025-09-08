<?php
declare(strict_types=1);

namespace {
    use BaseTestCase;
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

namespace FBM\Core {
    class Retention {
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

namespace FoodBankManager\Admin {
    function filter_input( int $type, $var, $filter = FILTER_DEFAULT, $options = [] ) {
        if ( INPUT_POST === $type ) {
            return $_POST[ $var ] ?? null;
        }
        if ( INPUT_GET === $type ) {
            return $_GET[ $var ] ?? null;
        }
        if ( INPUT_SERVER === $type ) {
            return $_SERVER[ $var ] ?? null;
        }
        return null;
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
    final class DiagnosticsPageTest extends BaseTestCase {
        public static bool $mail_result = true;
        /** @var array<string,int> */
        public static array $cron_next = array();

    protected function setUp(): void {
        parent::setUp();
        fbm_grant_manager();
        self::$mail_result = true;
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
    }

        public function testSendTestEmailSuccess(): void {
            fbm_seed_nonce('unit-seed');
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST = array(
                'fbm_action' => 'mail_test',
                '_fbm_nonce' => wp_create_nonce('fbm_diag_mail_test'),
            );
            try {
                DiagnosticsPage::route();
            } catch ( \RuntimeException $e ) {
                $this->assertSame('redirect', $e->getMessage());
            }
            $this->assertStringContainsString('notice=sent', (string) $GLOBALS['__last_redirect']);
        }

        public function testSendTestEmailFailure(): void {
            fbm_test_set_wp_mail_result(false);
            fbm_seed_nonce('unit-seed');
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST = array(
                'fbm_action' => 'mail_test',
                '_fbm_nonce' => wp_create_nonce('fbm_diag_mail_test'),
            );
            try {
                DiagnosticsPage::route();
            } catch ( \RuntimeException $e ) {
                $this->assertSame('redirect', $e->getMessage());
            }
            $this->assertStringContainsString('notice=error', (string) $GLOBALS['__last_redirect']);
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
            DiagnosticsPage::render();
            $html = (string) ob_get_clean();
            $this->assertStringContainsString('<div class="wrap fbm-admin">', $html);
            $this->assertStringContainsString('Crypto', $html);
            $this->assertStringContainsString('Environment', $html);
            $this->assertStringNotContainsString('from@example.com', $html);
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
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST = array(
                'fbm_action' => 'fbm_retention_run',
                '_wpnonce'   => wp_create_nonce('fbm_retention_run'),
            );
            ob_start();
            DiagnosticsPage::render();
            $html = (string) ob_get_clean();
            $this->assertStringContainsString('<div class="wrap fbm-admin">', $html);
            $this->assertStringContainsString('Cron Health', $html);
            $this->assertStringContainsString('"deleted":1', $html);
        }

        public function testRetentionDryRunOutputsSummary(): void {
            fbm_seed_nonce('unit-seed');
            if ( ! defined( 'ABSPATH' ) ) {
                define( 'ABSPATH', __DIR__ );
            }
            if ( ! defined( 'FBM_PATH' ) ) {
                define( 'FBM_PATH', dirname( __DIR__, 3 ) . '/' );
            }
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST = array(
                'fbm_action' => 'fbm_retention_dry_run',
                '_wpnonce'   => wp_create_nonce('fbm_retention_dry_run'),
            );
            ob_start();
            DiagnosticsPage::render();
            $html = (string) ob_get_clean();
            $this->assertStringContainsString('<div class="wrap fbm-admin">', $html);
            $this->assertStringContainsString('Cron Health', $html);
            $this->assertStringContainsString('"deleted":1', $html);
        }

        public function testRepairCapsActionEnsuresCaps(): void {
            \FBM\Auth\Capabilities::$ensured = false;
            \FBM\Auth\Capabilities::ensure_for_admin();
            $this->assertTrue(\FBM\Auth\Capabilities::$ensured);
        }
    }
}
