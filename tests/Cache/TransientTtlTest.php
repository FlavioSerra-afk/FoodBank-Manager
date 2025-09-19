<?php
/**
 * Cache transient behaviour smoke tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Cache;

use FoodBankManager\Core\Cache;
use PHPUnit\Framework\TestCase;

/**
 * @covers \FoodBankManager\Core\Cache
 */
final class TransientTtlTest extends TestCase {
        protected function setUp(): void {
                parent::setUp();
                unset( $GLOBALS['fbm_transients'] );
        }

        public function test_cache_set_records_expiration_timestamp(): void {
                $key     = Cache::build_key( 'reports', 'summary', array( 'demo' => true ) );
                $before  = time();
                Cache::set( $key, array( 'value' => 10 ), 120 );

                $record = $GLOBALS['fbm_transients'][ $key ] ?? null;
                $this->assertIsArray( $record );
                $this->assertArrayHasKey( 'expires', $record );
                $this->assertGreaterThan( $before, $record['expires'] );
        }

        public function test_expired_cache_entry_is_cleared_on_fetch(): void {
                $key = Cache::build_key( 'reports', 'summary', array( 'demo' => true ) );
                Cache::set( $key, array( 'value' => 10 ), 1 );

                $GLOBALS['fbm_transients'][ $key ]['expires'] = time() - 10;

                $this->assertFalse( Cache::get( $key ) );
                $this->assertArrayNotHasKey( $key, $GLOBALS['fbm_transients'] );
        }

        public function test_purge_group_removes_all_known_keys(): void {
                $first  = Cache::build_key( 'reports', 'summary', array( 'page' => 1 ) );
                $second = Cache::build_key( 'reports', 'page', array( 'page' => 2 ) );

                Cache::set( $first, array( 'value' => 1 ), 90 );
                Cache::set( $second, array( 'value' => 2 ), 90 );

                Cache::purge_group( 'reports' );

                $this->assertArrayNotHasKey( $first, $GLOBALS['fbm_transients'] ?? array() );
                $this->assertArrayNotHasKey( $second, $GLOBALS['fbm_transients'] ?? array() );
                $this->assertArrayNotHasKey( 'fbm_cache_registry:reports:0', $GLOBALS['fbm_transients'] ?? array() );
        }
}
