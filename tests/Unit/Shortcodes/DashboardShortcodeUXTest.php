<?php
declare(strict_types=1);

namespace FBM\Tests\Unit\Shortcodes;

use BaseTestCase;
use FoodBankManager\Core\Assets;
use FBM\Shortcodes\Shortcodes;
use FBM\Tests\Support\WPDBStub;
use Tests\Support\Rbac;

final class DashboardShortcodeUXTest extends BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        Rbac::grantManager();
        if (!defined('FBM_PATH')) {
            define('FBM_PATH', dirname(__DIR__, 3) . '/');
        }
        if (!defined('FBM_URL')) {
            define('FBM_URL', '');
        }
        // Ensure shortcode registered.
        require_once FBM_PATH . 'includes/Shortcodes/Shortcodes.php';
        Shortcodes::register();
        // Stub DB.
        $GLOBALS['wpdb'] = new WPDBStub();
    }

    private function seedTransients(array $totals, array $series = array()): void {
        $hash = md5('7d||all|0');
        $base = 'fbm_dash_1_7d_' . $hash . '_';
        $GLOBALS['fbm_transients'][$base . 'series'] = $series;
        $GLOBALS['fbm_transients'][$base . 'totals'] = $totals;
        $GLOBALS['fbm_transients'][$base . 'prev']   = array();
    }

    public function testLoadedStateRendersTokens(): void {
        $this->seedTransients(array('present' => 5), array(1,2));
        $html = \FBM\Shortcodes\DashboardShortcode::render();
        $this->assertStringContainsString('fbm-dashboard', $html);
        $this->assertStringContainsString('data-testid="fbm-summary"', $html);
        $this->assertStringContainsString('data-testid="fbm-compare-toggle"', $html);
        $this->assertStringContainsString('fbm-sparkline', $html);
    }

    public function testEmptyStateMessage(): void {
        $this->seedTransients(array(), array());
        $html = \FBM\Shortcodes\DashboardShortcode::render();
        $this->assertStringContainsString('No data for selected filters', $html);
    }

    public function testAssetsOnlyWhenShortcodePresent(): void {
        $assets = new Assets();
        $GLOBALS['fbm_is_singular'] = true;
        $GLOBALS['fbm_post_content'] = 'none';
        $assets->enqueue_front();
        $this->assertArrayNotHasKey('fbm-frontend-dashboard', $GLOBALS['fbm_styles']);

        $GLOBALS['fbm_styles'] = array();
        $GLOBALS['fbm_post_content'] = '[fbm_dashboard]';
        $assets->enqueue_front();
        $this->assertArrayHasKey('fbm-frontend-dashboard', $GLOBALS['fbm_styles']);
    }
}
