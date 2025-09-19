<?php
/**
 * Integration coverage for the uninstall opt-in flow.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Integration;

use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @coversNothing
 */
final class UninstallOptInTest extends TestCase {
        public function test_uninstall_without_destructive_flag_preserves_data(): void {
                $wpdb = new \wpdb();
                $this->primeDatabase( $wpdb );
                $GLOBALS['wpdb']        = $wpdb;
                $GLOBALS['fbm_options'] = $this->primeOptions( false );

                if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
                        define( 'WP_UNINSTALL_PLUGIN', true );
                }

                require FBM_PATH . 'uninstall.php';

                $this->assertArrayHasKey( 'fbm_settings', $GLOBALS['fbm_options'] );
                $this->assertArrayHasKey( 'fbm_theme', $GLOBALS['fbm_options'] );
                $this->assertNotEmpty( $GLOBALS['fbm_options']['fbm_settings'] );

                $this->assertNotEmpty( $wpdb->members );
                $this->assertNotEmpty( $wpdb->tokens );
                $this->assertNotEmpty( $wpdb->attendance );
                $this->assertNotEmpty( $wpdb->attendance_overrides );
        }

        public function test_uninstall_with_destructive_flag_clears_options_and_tables(): void {
                $wpdb = new \wpdb();
                $this->primeDatabase( $wpdb );
                $GLOBALS['wpdb']        = $wpdb;
                $GLOBALS['fbm_options'] = $this->primeOptions( true );
                $GLOBALS['fbm_transients'] = array(
                        'fbm:reports:test' => array( 'foo' => 'bar' ),
                );

                if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
                        define( 'WP_UNINSTALL_PLUGIN', true );
                }

                require FBM_PATH . 'uninstall.php';

                $this->assertArrayNotHasKey( 'fbm_settings', $GLOBALS['fbm_options'] );
                $this->assertArrayNotHasKey( 'fbm_theme', $GLOBALS['fbm_options'] );
                $this->assertEmpty( $wpdb->members );
                $this->assertEmpty( $wpdb->tokens );
                $this->assertEmpty( $wpdb->attendance );
                $this->assertEmpty( $wpdb->attendance_overrides );
                $this->assertEmpty( $GLOBALS['fbm_transients'] );

                $drop_queries = array_filter(
                        $wpdb->queries,
                        static fn( string $query ): bool => str_contains( $query, 'DROP TABLE IF EXISTS' )
                );
                $this->assertNotEmpty( $drop_queries );
        }

        /**
         * Populate stub wpdb with example data.
         */
        private function primeDatabase( \wpdb $wpdb ): void {
                $wpdb->members = array(
                        1 => array(
                                'id'               => 1,
                                'member_reference' => 'FBM-UN1',
                                'email'            => 'persist@example.com',
                                'status'           => 'active',
                        ),
                );

                $wpdb->tokens = array(
                        1 => array(
                                'member_id'  => 1,
                                'token_hash' => 'hash',
                                'issued_at'  => '2023-08-01 10:00:00',
                                'version'    => 'v1',
                                'meta'       => '{}',
                        ),
                );

                $wpdb->attendance = array(
                        1 => array(
                                'id'               => 1,
                                'member_reference' => 'FBM-UN1',
                                'collected_at'     => '2023-08-10 12:00:00',
                                'collected_date'   => '2023-08-10',
                                'method'           => 'qr',
                        ),
                );

                $wpdb->attendance_overrides = array(
                        1 => array(
                                'id'               => 1,
                                'member_reference' => 'FBM-UN1',
                                'override_note'    => 'Audit entry',
                        ),
                );
        }

        /**
         * Seed options for uninstall assertions.
         *
         * @return array<string,mixed>
         */
        private function primeOptions( bool $allow_destructive ): array {
                return array(
                        'fbm_settings'                  => array( 'registration' => array( 'auto_approve' => true ) ),
                        'fbm_theme'                     => array( 'mode' => 'light' ),
                        'fbm_allow_destructive_uninstall' => $allow_destructive,
                );
        }
}
