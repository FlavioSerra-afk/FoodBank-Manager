<?php
declare(strict_types=1);

namespace FBM\Tests\Unit\Shortcodes;

use FBM\Shortcodes\Shortcodes;
use Tests\Support\Rbac;

final class DashboardPersistenceTest extends \BaseTestCase {
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
    }

    private function seedTransients( int $user_id ): void {
        $hash = md5( '7d||all|0' );
        $base = 'fbm_dash_' . $user_id . '_7d_' . $hash . '_';
        $GLOBALS['fbm_transients'][ $base . 'series' ] = array();
        $GLOBALS['fbm_transients'][ $base . 'totals' ] = array( 'present' => 0 );
        $GLOBALS['fbm_transients'][ $base . 'prev' ]   = array( 'present' => 0 );
    }

    public function testFilterPersistencePerUser(): void {
        $this->seedTransients( 1 );
        $_GET['preset'] = 'weekly';
        $_GET['compare'] = '1';
        $_GET['tags'] = array( 'x' );
        $html1 = \FBM\Shortcodes\DashboardShortcode::render();
        $this->assertStringContainsString( 'data-testid="fbm-filter-preset">weekly', $html1 );
        $this->assertStringContainsString( 'data-testid="fbm-filter-tags">x', $html1 );
        $this->assertStringContainsString( 'Compare: On', $html1 );

        $_GET = array();
        $html2 = \FBM\Shortcodes\DashboardShortcode::render();
        $this->assertStringContainsString( 'data-testid="fbm-filter-preset">weekly', $html2 );
        $this->assertStringContainsString( 'data-testid="fbm-filter-tags">x', $html2 );
        $this->assertStringContainsString( 'Compare: On', $html2 );

        $GLOBALS['fbm_current_user'] = 2;
        $this->seedTransients( 2 );
        $html3 = \FBM\Shortcodes\DashboardShortcode::render();
        $this->assertStringContainsString( 'data-testid="fbm-filter-preset"></span>', $html3 );
        $this->assertStringContainsString( 'Compare: Off', $html3 );
    }
}
