<?php
/**
 * Install migration tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace {
        if ( ! function_exists( 'dbDelta' ) ) {
                /**
                 * Capture dbDelta calls during testing.
                 *
                 * @param string $sql Migration SQL.
                 */
                function dbDelta( string $sql ): void {
                        if ( ! isset( $GLOBALS['fbm_dbdelta_sql'] ) || ! is_array( $GLOBALS['fbm_dbdelta_sql'] ) ) {
                                $GLOBALS['fbm_dbdelta_sql'] = array();
                        }

                        $GLOBALS['fbm_dbdelta_sql'][] = $sql;
                }
        }
}

namespace FBM\Tests\Core;

use FoodBankManager\Core\Install;
use PHPUnit\Framework\TestCase;

/**
 * @covers \FoodBankManager\Core\Install
 */
final class InstallTest extends TestCase {
        /**
         * Previously configured global wpdb instance.
         *
         * @var \wpdb|null
         */
        private $previous_wpdb;

        protected function setUp(): void {
                parent::setUp();

                $this->previous_wpdb        = $GLOBALS['wpdb'] ?? null;
                $GLOBALS['fbm_dbdelta_sql'] = array();
                $GLOBALS['fbm_options']     = array();
        }

        protected function tearDown(): void {
                if ( $this->previous_wpdb instanceof \wpdb ) {
                        $GLOBALS['wpdb'] = $this->previous_wpdb;
                } else {
                        unset( $GLOBALS['wpdb'] );
                }

                unset( $GLOBALS['fbm_dbdelta_sql'], $GLOBALS['fbm_options'] );

                parent::tearDown();
        }

        public function test_ensure_tables_creates_current_schema(): void {
                global $wpdb;

                $wpdb = new \wpdb();

                Install::ensure_tables();

                $this->assertCount( 4, $GLOBALS['fbm_dbdelta_sql'] );

                $sql = implode( '\n', $GLOBALS['fbm_dbdelta_sql'] );

                $this->assertStringContainsString( 'CREATE TABLE `wp_fbm_members`', $sql );
                $this->assertStringContainsString( 'CREATE TABLE `wp_fbm_attendance`', $sql );
                $this->assertStringContainsString( 'CREATE TABLE `wp_fbm_attendance_overrides`', $sql );
                $this->assertStringContainsString( 'CREATE TABLE `wp_fbm_tokens`', $sql );

                $this->assertSame( '2024093002', get_option( 'fbm_db_version' ) );
        }

        public function test_ensure_tables_drops_legacy_tables(): void {
                global $wpdb;

                $wpdb = new \wpdb();

                Install::ensure_tables();

                $expected_drops = array(
                        'DROP TABLE IF EXISTS `wp_fb_events`',
                        'DROP TABLE IF EXISTS `wp_fb_tickets`',
                        'DROP TABLE IF EXISTS `wp_fb_checkins`',
                );

                foreach ( $expected_drops as $statement ) {
                        $this->assertContains( $statement, $wpdb->queries );
                }
        }
}
