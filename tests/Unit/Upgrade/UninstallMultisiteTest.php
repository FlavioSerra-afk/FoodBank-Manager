<?php
declare(strict_types=1);

namespace Tests\Unit\Upgrade;

final class UninstallMultisiteTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        \define('WP_UNINSTALL_PLUGIN', true);
        if (!defined('ABSPATH')) {
            \define('ABSPATH', __DIR__);
        }
        $GLOBALS['fbm_is_multisite'] = true;
        $GLOBALS['fbm_network_active_plugins'] = array('foodbank-manager/foodbank-manager.php');
        $GLOBALS['fbm_sites'] = array((object) ['blog_id' => 1], (object) ['blog_id' => 2]);
        $GLOBALS['fbm_switched_to'] = array();
        foreach ([1, 2] as $blog) {
            switch_to_blog($blog);
            update_option('fbm_options', array('test' => 1));
            update_option('fbm_caps_migrated_2025_09', 1);
            update_option('fbm_telemetry_daily', 1);
            set_transient('fbm_temp', 'x');
        }
        restore_current_blog();
        $GLOBALS['fbm_switched_to'] = array();
        $GLOBALS['fbm_site_options']['fbm_caps_migrated_2025_09'] = 1;
        $GLOBALS['fbm_site_options']['fbm_telemetry_daily'] = 1;
        $GLOBALS['fbm_site_options']['_site_transient_fbm_temp'] = 1;
    }

    public function testRemovesDataPerSite(): void {
        include dirname(__DIR__, 3) . '/uninstall.php';
        foreach ([1, 2] as $blog) {
            $this->assertArrayNotHasKey('fbm_caps_migrated_2025_09', $GLOBALS['fbm_options'][$blog] ?? array());
            $this->assertArrayNotHasKey('fbm_telemetry_daily', $GLOBALS['fbm_options'][$blog] ?? array());
            $this->assertArrayNotHasKey('fbm_temp', $GLOBALS['fbm_transients'][$blog] ?? array());
        }
        $this->assertEmpty($GLOBALS['fbm_site_options']);
        $this->assertSame([1,2], $GLOBALS['fbm_switched_to']);
        $GLOBALS['fbm_switched_to'] = array();
        include dirname(__DIR__, 3) . '/uninstall.php';
        $this->assertSame([1,2], $GLOBALS['fbm_switched_to']);
    }
}
