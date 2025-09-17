<?php
/**
 * Integration coverage for uninstall cleanup.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests;

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

                $this->pluginRoot = dirname( __DIR__ );

                $GLOBALS['fbm_deleted_options'] = array();
                $GLOBALS['fbm_dropped_tables']  = array();
                $GLOBALS['fbm_filters']         = array();
                $GLOBALS['fbm_options']         = $this->optionFixture();

                if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
                        define( 'WP_UNINSTALL_PLUGIN', true );
                }

                $abspath = $this->prepare_wordpress_stubs();

                if ( ! defined( 'ABSPATH' ) ) {
                        define( 'ABSPATH', $abspath );
                }

                $GLOBALS['wpdb'] = new \wpdb();
        }

        public function test_guard_disabled_preserves_tables_and_secrets(): void {
                require $this->pluginRoot . '/uninstall.php';

                $this->assertSame( array(), $GLOBALS['fbm_dropped_tables'] );
                $this->assertContains( 'fbm_db_migration_summary', $GLOBALS['fbm_deleted_options'] );
                $this->assertContains( 'fbm_schedule_window_overrides', $GLOBALS['fbm_deleted_options'] );
                $this->assertNotContains( 'fbm_token_signing_key', $GLOBALS['fbm_deleted_options'] );

                $this->assertArrayHasKey( 'fbm_token_signing_key', $GLOBALS['fbm_options'] );
                $this->assertArrayHasKey( 'fbm_token_storage_key', $GLOBALS['fbm_options'] );
                $this->assertArrayNotHasKey( 'fbm_theme', $GLOBALS['fbm_options'] );
        }

        public function test_guard_enabled_purges_tables_and_secrets(): void {
                if ( ! defined( 'FBM_ALLOW_DESTRUCTIVE_UNINSTALL' ) ) {
                        define( 'FBM_ALLOW_DESTRUCTIVE_UNINSTALL', true );
                }

                require $this->pluginRoot . '/uninstall.php';

                $expected_tables = array(
                        'wp_fbm_attendance_overrides',
                        'wp_fbm_attendance',
                        'wp_fbm_tokens',
                        'wp_fbm_members',
                );

                $this->assertEqualsCanonicalizing( $expected_tables, $GLOBALS['fbm_dropped_tables'] );
                $this->assertContains( 'fbm_token_signing_key', $GLOBALS['fbm_deleted_options'] );
                $this->assertContains( 'fbm_token_storage_key', $GLOBALS['fbm_deleted_options'] );
                $this->assertSame( array(), $GLOBALS['fbm_options'] );
        }

        /**
         * Provide a representative option payload for uninstall checks.
         *
         * @return array<string, mixed>
         */
        private function optionFixture(): array {
                return array(
                        'fbm_db_version'             => '2.2.24',
                        'fbm_theme'                  => array( 'style' => 'basic' ),
                        'fbm_settings'               => array( 'window' => 'thursday' ),
                        'fbm_db_migration_summary'   => array( 'attendance_migrated' => 100 ),
                        'fbm_schedule_window'        => array( 'start' => '11:00', 'end' => '14:30' ),
                        'fbm_schedule_window_overrides' => array( 'holiday' => 'closed' ),
                        'fbm_token_signing_key'      => 'sign-secret',
                        'fbm_token_storage_key'      => 'store-secret',
                );
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
