<?php
declare(strict_types=1);

namespace Tests\Unit\Admin;

use BaseTestCase;
use FBM\Admin\DashboardPage;
use FBM\Attendance\CheckinsRepo;
use FBM\Attendance\EventsRepo;
use Tests\Support\EventsDbStub;
use Tests\Support\Rbac;

function fbm_dashboard_now($v) { // @phpstan-ignore-line
    return 1700000000;
}

final class DashboardPageTest extends BaseTestCase {
    private $oldDb;

    protected function setUp(): void {
        parent::setUp();
        if (!defined('FBM_PATH')) {
            define('FBM_PATH', dirname(__DIR__, 3) . '/');
        }
        if (!class_exists('FoodBankManager\\Database\\ApplicationsRepo', false)) {
            require_once __DIR__ . '/../../Support/ApplicationsRepoStub.php';
        }
        $ref = new \ReflectionClass(CheckinsRepo::class);
        $p = $ref->getProperty('store');
        $p->setAccessible(true);
        $p->setValue(null, array());
        $p2 = $ref->getProperty('next_id');
        $p2->setAccessible(true);
        $p2->setValue(null, 1);
        $this->oldDb = $GLOBALS['wpdb'] ?? null;
        $db = new EventsDbStub();
        $db->prefix = 'wp_';
        $GLOBALS['wpdb'] = $db;
        add_filter('fbm_now', __NAMESPACE__ . '\\fbm_dashboard_now');
        EventsRepo::create(array(
            'title' => 'Event',
            'starts_at' => '2024-01-01 00:00:00',
            'ends_at' => '2024-01-01 01:00:00',
            'status' => 'active',
        ));
    }

    protected function tearDown(): void {
        $GLOBALS['wpdb'] = $this->oldDb;
        parent::tearDown();
    }

    public function testRender(): void {
        Rbac::grantManager();
        ob_start();
        DashboardPage::route();
        $html = (string) ob_get_clean();
        $this->assertStringContainsString('fbm-admin', $html);
        $this->assertStringContainsString('fbm-dashboard-tile-registrations', $html);
        $this->assertStringContainsString('fbm-dashboard-tile-today', $html);
        $this->assertStringContainsString('fbm-dashboard-tile-week', $html);
        $this->assertStringContainsString('fbm-dashboard-tile-month', $html);
        $this->assertStringContainsString('fbm-dashboard-tile-recent', $html);
        $this->assertStringContainsString('data-testid="fbm-dashboard-sparkline"', $html);
        $this->assertStringContainsString('data-testid="fbm-dashboard-shortcuts"', $html);
        $this->assertStringContainsString('fbm-card--glass', $html);
        $this->assertStringContainsString('fbm-button--glass', $html);
        $this->assertStringNotContainsString('<script', $html);
    }
}
