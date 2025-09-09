<?php
declare(strict_types=1);

namespace Tests\Unit\Attendance;

use BaseTestCase;
use FBM\Attendance\ReportsService;
use FBM\Attendance\CheckinsRepo;
use FBM\Attendance\EventsRepo;
use Tests\Support\EventsDbStub;
use Tests\Support\Rbac;

final class ReportsServiceTest extends BaseTestCase {
    private $oldDb;

    protected function setUp(): void {
        parent::setUp();
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
        $GLOBALS['fbm_filters']['fbm_now'][] = fn($v)=>1700000000; // 2023-11-14 approx
        EventsRepo::create(array(
            'title' => 'Event 1',
            'starts_at' => '2023-11-14 00:00:00',
            'ends_at' => '2023-11-14 01:00:00',
            'status' => 'active',
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
        CheckinsRepo::record(array(
            'event_id'=>1,
            'recipient'=>'b@example.com',
            'token_hash'=>null,
            'method'=>'manual',
            'note'=>'note',
            'by'=>2,
            'verified_at'=>'2023-11-13 09:00:00',
            'created_at'=>'2023-11-13 09:00:00',
        ));
        CheckinsRepo::record(array(
            'event_id'=>1,
            'recipient'=>'a@example.com',
            'token_hash'=>null,
            'method'=>'qr',
            'note'=>null,
            'by'=>1,
            'verified_at'=>'2023-11-13 10:00:00',
            'created_at'=>'2023-11-13 10:00:00',
        ));
    }

    protected function tearDown(): void {
        $GLOBALS['fbm_filters']['fbm_now'] = array();
        $GLOBALS['wpdb'] = $this->oldDb;
        parent::tearDown();
    }

    public function testDailyCounts(): void {
        $out = ReportsService::daily_counts(7);
        $this->assertCount(7, $out['days']);
        $this->assertSame('2023-11-08', $out['days'][0]['date']);
        $this->assertSame(1, $out['days'][6]['total']);
        $this->assertSame(1, $out['days'][6]['unique']);
    }

    public function testPeriodSummary(): void {
        $sum = ReportsService::period_summary();
        $this->assertSame(1, $sum['today']);
        $this->assertSame(3, $sum['week']);
        $this->assertSame(3, $sum['month']);
        $this->assertSame(1, $sum['unique_today']);
        $this->assertSame(2, $sum['unique_week']);
    }

    public function testExportRowsMasking(): void {
        $rows = ReportsService::export_rows(array(), true);
        $this->assertSame('a***@example.com', $rows[0]['recipient_masked']);
        $rows2 = ReportsService::export_rows(array(), false);
        $this->assertSame('a@example.com', $rows2[0]['recipient_masked']);
    }
}
