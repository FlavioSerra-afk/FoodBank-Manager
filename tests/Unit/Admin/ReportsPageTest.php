<?php
declare(strict_types=1);

namespace Tests\Unit\Admin;

use BaseTestCase;
use FBM\Admin\ReportsPage;
use FBM\Attendance\CheckinsRepo;
use FBM\Attendance\EventsRepo;
use Tests\Support\EventsDbStub;
use Tests\Support\Rbac;

final class ReportsPageTest extends BaseTestCase {
    private $oldDb;

    protected function setUp(): void {
        parent::setUp();
        if (!defined('FBM_PATH')) {
            define('FBM_PATH', dirname(__DIR__, 3) . '/');
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
        $GLOBALS['fbm_filters']['fbm_now'][] = fn($v)=>1700000000;
        EventsRepo::create(array(
            'title' => 'Event 1',
            'starts_at'=>'2023-11-14 00:00:00',
            'ends_at'=>'2023-11-14 01:00:00',
            'status'=>'active',
        ));
        CheckinsRepo::record(array(
            'event_id'=>1,
            'recipient'=>'a@example.com',
            'token_hash'=>null,
            'method'=>'qr',
            'note'=>null,
            'by'=>1,
            'verified_at'=>'2023-11-14 09:00:00',
            'created_at'=>'2023-11-14 09:00:00',
        ));
    }

    protected function tearDown(): void {
        $GLOBALS['fbm_filters']['fbm_now'] = array();
        $GLOBALS['wpdb'] = $this->oldDb;
        parent::tearDown();
    }

    public function testDenied(): void {
        Rbac::revokeAll();
        ob_start();
        ReportsPage::route();
        $html = (string) ob_get_clean();
        $this->assertStringContainsString('You do not have permission', $html);
    }

    public function testRender(): void {
        Rbac::grantManager();
        ob_start();
        ReportsPage::route();
        $html = (string) ob_get_clean();
        $this->assertStringContainsString('data-testid="fbm-reports-summary"', $html);
        $this->assertStringContainsString('data-testid="fbm-reports-daily-table"', $html);
        $this->assertStringContainsString('_wpnonce=', $html);
        $this->assertStringNotContainsString('a@example.com', $html);
    }
}
