<?php
declare(strict_types=1);

namespace Tests\Unit\Security;

final class ThrottleSettingsSanitizeTest extends \BaseTestCase {
    public function testSanitizeClampsAndRejects(): void {
        $GLOBALS['fbm_roles']['administrator'] = (object) array( 'caps' => array() );
        $GLOBALS['fbm_roles']['editor']        = (object) array( 'caps' => array() );
        $in = array(
            'window_seconds'   => 1,
            'base_limit'       => 500,
            'role_multipliers' => array(
                'administrator' => '0',
                'editor'        => '2.5',
                'invalid'       => '5',
            ),
        );
        $out = \fbm_throttle_sanitize( $in );
        $this->assertSame( 5, $out['window_seconds'] );
        $this->assertSame( 120, $out['base_limit'] );
        $this->assertSame( 0.0, $out['role_multipliers']['administrator'] );
        $this->assertSame( 2.5, $out['role_multipliers']['editor'] );
        $this->assertArrayNotHasKey( 'invalid', $out['role_multipliers'] );
    }
}

