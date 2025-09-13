<?php
declare(strict_types=1);

namespace FBM\Tests\Unit\Shortcodes;

use FBM\Shortcodes\Shortcodes;
use Tests\Support\Rbac;

final class DashboardCompareTest extends \BaseTestCase {
    private $nowFilter;

    protected function setUp(): void {
        parent::setUp();
        Rbac::grantManager();
        if ( ! defined( 'FBM_PATH' ) ) {
            define( 'FBM_PATH', dirname( __DIR__, 3 ) . '/' );
        }
        if ( ! defined( 'FBM_URL' ) ) {
            define( 'FBM_URL', '' );
        }
        require_once FBM_PATH . 'includes/Shortcodes/Shortcodes.php';
        Shortcodes::register();
        $this->nowFilter = static fn() => 1735689600;
        add_filter( 'fbm_now', $this->nowFilter );
    }

    protected function tearDown(): void {
        remove_filter( 'fbm_now', $this->nowFilter );
        parent::tearDown();
    }

    private function seedTransients(): void {
        $hash = md5( '7d||all|0' );
        $base = 'fbm_dash_1_7d_' . $hash . '_';
        $GLOBALS['fbm_transients'][ $base . 'series' ] = array();
        $GLOBALS['fbm_transients'][ $base . 'totals' ] = array( 'present' => 10 );
        $GLOBALS['fbm_transients'][ $base . 'prev' ]   = array( 'present' => 15 );
    }

    public function testCompareShowsDelta(): void {
        $this->seedTransients();
        $html = \FBM\Shortcodes\DashboardShortcode::render( array( 'compare' => '1' ) );
        $this->assertStringContainsString( 'fbm-scope', $html );
        $this->assertStringContainsString( 'fbm-public', $html );
        $this->assertStringContainsString( 'data-testid="fbm-delta">+5', $html );
    }

    public function testDeltaHiddenWhenCompareOff(): void {
        $this->seedTransients();
        $html = \FBM\Shortcodes\DashboardShortcode::render( array( 'compare' => '0' ) );
        $this->assertStringContainsString( 'data-testid="fbm-delta">+0', $html );
    }
}
