<?php
declare(strict_types=1);

namespace Tests\Unit\Security;

use FBM\Security\RateLimiter;

final class RateLimiterRoleTest extends \BaseTestCase {
    public function testAdminBypasses(): void {
        $GLOBALS['fbm_roles']['administrator'] = (object) array( 'caps' => array() );
        update_option( 'fbm_throttle', array( 'window_seconds' => 30, 'base_limit' => 6, 'role_multipliers' => array( 'administrator' => 0 ) ) );
        $GLOBALS['fbm_current_user_roles'] = array( 'administrator' );
        $_SERVER['REMOTE_ADDR'] = '1.1.1.1';
        $res = RateLimiter::scan();
        $this->assertTrue( $res['allowed'] );
        $this->assertSame( 'RateLimit-Limit: 0', $GLOBALS['__fbm_sent_headers'][0] ?? '' );
    }

    public function testEditorMultiplier(): void {
        $GLOBALS['fbm_roles']['administrator'] = (object) array( 'caps' => array() );
        $GLOBALS['fbm_roles']['editor']        = (object) array( 'caps' => array() );
        update_option( 'fbm_throttle', array( 'window_seconds' => 30, 'base_limit' => 6, 'role_multipliers' => array( 'administrator' => 0, 'editor' => 2.0 ) ) );
        $GLOBALS['fbm_current_user_roles'] = array( 'editor' );
        $_SERVER['REMOTE_ADDR'] = '2.2.2.2';
        for ( $i = 0; $i < 12; $i++ ) {
            $this->assertTrue( RateLimiter::scan()['allowed'] );
        }
        $res = RateLimiter::scan();
        $this->assertFalse( $res['allowed'] );
        $this->assertGreaterThanOrEqual( 0, $res['retry_after'] );
        $this->assertSame( 'RateLimit-Limit: 12', $GLOBALS['__fbm_sent_headers'][0] ?? '' );
    }
}

