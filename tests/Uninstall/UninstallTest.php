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

		$this->pluginRoot = dirname( __DIR__, 2 );

			$GLOBALS['fbm_deleted_options']   = array();
			$GLOBALS['fbm_dropped_tables']    = array();
			$GLOBALS['fbm_filters']           = array();
			$GLOBALS['fbm_options']           = $this->optionFixture();
			$GLOBALS['fbm_transients']        = array(
				'fbm_cache_entry' => array(
					'value'   => 'cached',
					'expires' => 0,
				),
				'fbm_site_entry'  => array(
					'value'   => 'remote',
					'expires' => 0,
				),
				'other_transient' => array(
					'value'   => 'keep',
					'expires' => 0,
				),
			);
			$GLOBALS['fbm_cron']              = array(
				time() => array(
					'fbm_cleanup_cache' => array(
						md5( 'fbm_cleanup_cache' ) => array(
							'schedule' => false,
							'args'     => array(),
							'interval' => 0,
						),
					),
					'other_hook'        => array(
						md5( 'other_hook' ) => array(
							'schedule' => false,
							'args'     => array(),
							'interval' => 0,
						),
					),
				),
			);
			$GLOBALS['fbm_unscheduled_hooks'] = array();

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
			$this->assertSame( array( 'fbm_allow_destructive_uninstall' ), $GLOBALS['fbm_deleted_options'] );
			$this->assertArrayHasKey( 'fbm_token_signing_key', $GLOBALS['fbm_options'] );
			$this->assertArrayHasKey( 'fbm_token_storage_key', $GLOBALS['fbm_options'] );
			$this->assertArrayHasKey( '_transient_fbm_cache_key', $GLOBALS['fbm_options'] );
			$this->assertArrayHasKey( '_site_transient_fbm_remote', $GLOBALS['fbm_options'] );
			$this->assertArrayHasKey( 'other_plugin_option', $GLOBALS['fbm_options'] );
			$this->assertArrayNotHasKey( 'fbm_allow_destructive_uninstall', $GLOBALS['fbm_options'] );
			$this->assertSame( array(), $GLOBALS['fbm_unscheduled_hooks'] );
			$this->assertArrayHasKey( 'fbm_cache_entry', $GLOBALS['fbm_transients'] );
	}

	public function test_opt_in_option_drops_tables_options_and_hooks(): void {
			$GLOBALS['fbm_options']['fbm_allow_destructive_uninstall'] = true;

			require $this->pluginRoot . '/uninstall.php';

			$expected_tables = array(
				'wp_fbm_attendance_overrides',
				'wp_fbm_attendance',
				'wp_fbm_tokens',
				'wp_fbm_members',
			);

			$this->assertEqualsCanonicalizing( $expected_tables, $GLOBALS['fbm_dropped_tables'] );

			$expected_deleted = array(
				'fbm_db_version',
				'fbm_theme',
				'fbm_settings',
				'fbm_db_migration_summary',
				'fbm_schedule_window',
				'fbm_schedule_window_overrides',
				'fbm_token_signing_key',
				'fbm_token_storage_key',
				'fbm_mail_failures',
				'fbm_members_action_audit',
				'fbm_allow_destructive_uninstall',
				'_transient_fbm_cache_key',
				'_site_transient_fbm_remote',
			);

			$this->assertEqualsCanonicalizing( $expected_deleted, array_unique( $GLOBALS['fbm_deleted_options'] ) );
			$this->assertSame( array( 'other_plugin_option' => 'preserve' ), $GLOBALS['fbm_options'] );
			$this->assertSame(
				array(
					'other_transient' => array(
						'value'   => 'keep',
						'expires' => 0,
					),
				),
				$GLOBALS['fbm_transients']
			);
			$this->assertSame( array( 'fbm_cleanup_cache' ), $GLOBALS['fbm_unscheduled_hooks'] );
	}

		/**
		 * Provide a representative option payload for uninstall checks.
		 *
		 * @return array<string, mixed>
		 */
	private function optionFixture(): array {
			return array(
				'fbm_db_version'                  => '2.2.25',
				'fbm_theme'                       => array( 'style' => 'basic' ),
				'fbm_settings'                    => array( 'window' => 'thursday' ),
				'fbm_db_migration_summary'        => array( 'attendance_migrated' => 100 ),
				'fbm_schedule_window'             => array(
					'start' => '11:00',
					'end'   => '14:30',
				),
				'fbm_schedule_window_overrides'   => array( 'holiday' => 'closed' ),
				'fbm_token_signing_key'           => 'sign-secret',
				'fbm_token_storage_key'           => 'store-secret',
				'_transient_fbm_cache_key'        => 'cached',
				'_site_transient_fbm_remote'      => 'cached-remote',
				'fbm_allow_destructive_uninstall' => false,
				'other_plugin_option'             => 'preserve',
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
