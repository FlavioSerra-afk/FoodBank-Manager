<?php
/**
 * Uninstall script tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace {
        if ( ! function_exists( 'delete_option' ) ) {
                function delete_option( string $option ): bool {
                        $GLOBALS['fbm_deleted_options'][] = $option;

                        return true;
                }
        }

        if ( ! function_exists( 'apply_filters' ) ) {
                function apply_filters( string $hook_name, $value, mixed ...$args ) {
                        $filters = $GLOBALS['fbm_filter_overrides'][ $hook_name ] ?? array();

                        foreach ( $filters as $callback ) {
                                $value = $callback( $value, ...$args );
                        }

                        return $value;
                }
        }

        if ( ! function_exists( 'add_filter' ) ) {
                function add_filter( string $hook_name, callable $callback ): void {
                        $GLOBALS['fbm_filter_overrides'][ $hook_name ][] = $callback;
                }
        }
}

namespace FBM\Tests\Unit {

use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class UninstallTest extends TestCase {
        /**
         * Absolute path to the plugin root.
         *
         * @var string
         */
        private string $pluginRoot;

        protected function setUp(): void {
                parent::setUp();

                $this->pluginRoot = dirname( __DIR__, 2 );

                $GLOBALS['fbm_deleted_options']  = array();
                $GLOBALS['fbm_dropped_tables']   = array();
                $GLOBALS['fbm_filter_overrides'] = array();
        }

        public function test_uninstall_skips_destructive_cleanup_without_flag(): void {
                if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
                        define( 'WP_UNINSTALL_PLUGIN', true );
                }

                $abspath = $this->prepare_wordpress_stubs();

                if ( ! defined( 'ABSPATH' ) ) {
                        define( 'ABSPATH', $abspath );
                }

                $GLOBALS['wpdb'] = new \wpdb();

                require $this->pluginRoot . '/uninstall.php';

                $this->assertSame( array(), $GLOBALS['fbm_dropped_tables'] );
                $this->assertContains( 'fbm_db_version', $GLOBALS['fbm_deleted_options'] );
        }

        public function test_uninstall_drops_tables_when_constant_enabled(): void {
                if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
                        define( 'WP_UNINSTALL_PLUGIN', true );
                }

                if ( ! defined( 'FBM_ALLOW_DESTRUCTIVE_UNINSTALL' ) ) {
                        define( 'FBM_ALLOW_DESTRUCTIVE_UNINSTALL', true );
                }

                $abspath = $this->prepare_wordpress_stubs();

                if ( ! defined( 'ABSPATH' ) ) {
                        define( 'ABSPATH', $abspath );
                }

                $GLOBALS['wpdb'] = new \wpdb();

                require $this->pluginRoot . '/uninstall.php';

                $this->assertSame( array( 'wp_fbm_attendance' ), $GLOBALS['fbm_dropped_tables'] );
                $this->assertContains( 'fbm_db_version', $GLOBALS['fbm_deleted_options'] );
        }

        public function test_uninstall_drops_tables_when_filter_opt_in(): void {
                if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
                        define( 'WP_UNINSTALL_PLUGIN', true );
                }

                add_filter(
                        'fbm_allow_destructive_uninstall',
                        static function ( bool $allowed ): bool {
                                unset( $allowed );

                                return true;
                        }
                );

                $abspath = $this->prepare_wordpress_stubs();

                if ( ! defined( 'ABSPATH' ) ) {
                        define( 'ABSPATH', $abspath );
                }

                $GLOBALS['wpdb'] = new \wpdb();

                require $this->pluginRoot . '/uninstall.php';

                $this->assertSame( array( 'wp_fbm_attendance' ), $GLOBALS['fbm_dropped_tables'] );
                $this->assertContains( 'fbm_db_version', $GLOBALS['fbm_deleted_options'] );
        }

        private function prepare_wordpress_stubs(): string {
                $root        = sys_get_temp_dir() . '/fbm-wp-' . uniqid( '', true ) . '/';
                $upgrade_dir = $root . 'wp-admin/includes';

                if ( ! is_dir( $upgrade_dir ) && ! mkdir( $upgrade_dir, 0777, true ) ) {
                        $this->fail( 'Failed to create WordPress stub directories.' );
                }

                $stub = <<<'PHP'
<?php

declare(strict_types=1);

function maybe_drop_table( string $table ): bool {
        $GLOBALS['fbm_dropped_tables'][] = $table;

        return true;
}
PHP;

                if ( false === file_put_contents( $upgrade_dir . '/upgrade.php', $stub ) ) {
                        $this->fail( 'Failed to write WordPress upgrade stub.' );
                }

                return $root;
        }
}
}
