<?php
declare(strict_types=1);

namespace Tests\Unit\Admin;

use FBM\Admin\DashboardPage;
use FBM\Attendance\CheckinsRepo;
use FBM\Attendance\EventsRepo;
use Tests\Support\JobsDbStub;
use Tests\Support\Rbac;

function fbm_dashboard_now($v) { // @phpstan-ignore-line
    return 1700000000;
}

final class DashboardPageTest extends \BaseTestCase {
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
        $db = new JobsDbStub();
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
        remove_filter('fbm_now', __NAMESPACE__ . '\\fbm_dashboard_now');
        $GLOBALS['wpdb'] = $this->oldDb;
        parent::tearDown();
    }

    public function testRender(): void {
        Rbac::grantManager();
        ob_start();
        DashboardPage::route();
        $html = (string) ob_get_clean();
        $this->assertStringContainsString('fbm-admin', $html);
        $this->assertStringContainsString('fbm-grid', $html);
        $this->assertSame(9, substr_count($html, 'fbm-card--glass fbm-tile'));
        $labels = array(
            'Total applications',
            'New today',
            'Check-ins today',
            'Check-ins this week',
            'Check-ins this month',
            'Active events',
            'Tickets issued',
            'Tickets revoked',
            'Mail failures (last 7d)',
        );
        foreach ($labels as $label) {
            $this->assertStringContainsString($label, $html);
        }
        $this->assertStringContainsString('fbm-card--glass', $html);
        $this->assertStringContainsString('fbm-button--glass', $html);
        $this->assertStringContainsString('fbm-dashboard-sparkline', $html);
        $this->assertStringNotContainsString('<script', $html);
    }
}
