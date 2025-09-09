<?php
declare(strict_types=1);

namespace FoodBankManagerTest {

use \BaseTestCase;

final class DashboardShortcodeTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        if (!defined('FBM_PATH')) {
            define('FBM_PATH', dirname(__DIR__, 2) . '/');
        }
    }

    public function testSanitizePeriod(): void {
        require_once FBM_PATH . 'includes/Shortcodes/Dashboard.php';
        $this->assertSame('7d', \FoodBankManager\Shortcodes\Dashboard::sanitize_period('bad'));
    }

    public function testSanitizeTypeAndEvent(): void {
        require_once FBM_PATH . 'includes/Shortcodes/Dashboard.php';
        $this->assertSame('all', \FoodBankManager\Shortcodes\Dashboard::sanitize_type('bogus'));
        $this->assertSame('delivery', \FoodBankManager\Shortcodes\Dashboard::sanitize_type('delivery'));
        $this->assertNull(\FoodBankManager\Shortcodes\Dashboard::sanitize_event(''));
        $this->assertSame('abc', \FoodBankManager\Shortcodes\Dashboard::sanitize_event('abc'));
    }

    public function testUnauthorizedGated(): void {
        require_once FBM_PATH . 'includes/Shortcodes/Dashboard.php';
        \fbm_grant_caps(array());
        $html = \FoodBankManager\Shortcodes\Dashboard::render();
        $expected = '<div class="fbm-no-permission">You do not have permission to view the dashboard.</div>';
        $this->assertSame($expected, $html);
    }

    public function testSafeHtmlOutput(): void {
        \fbm_grant_caps(['fb_manage_dashboard']);
        class_exists('FoodBankManager\\UI\\Theme');
        $hash = md5('today||all|0');
        $base = 'fbm_dash_1_today_' . $hash . '_';
        $GLOBALS['fbm_transients'][$base . 'series'] = array(1, 2, 3);
        $GLOBALS['fbm_transients'][$base . 'totals'] = array(
            'present' => 3,
            'households' => 2,
            'no_shows' => 1,
            'in_person' => 1,
            'delivery' => 2,
            'voided' => 0,
        );
        $GLOBALS['fbm_transients'][$base . 'prev'] = array(
            'present' => 2,
            'households' => 1,
            'no_shows' => 0,
            'in_person' => 1,
            'delivery' => 1,
            'voided' => 0,
        );
        require_once FBM_PATH . 'includes/Shortcodes/Dashboard.php';
        $html = \FoodBankManager\Shortcodes\Dashboard::render(array('period' => 'today', 'compare' => '1', 'sparkline' => '1'));
        $this->assertStringContainsString('<svg', $html);
        $this->assertStringNotContainsString('<script', $html);
    }
}

}

