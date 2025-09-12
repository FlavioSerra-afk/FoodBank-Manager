<?php
declare(strict_types=1);

namespace Tests\Unit\Cron;

use FoodBankManager\Core\Cron;
use FoodBankManager\Admin\DiagnosticsReport;

final class TelemetryReportTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        wp_clear_scheduled_hook('fbm_retention_hourly');
    }

    public function testIdempotentSchedule(): void {
        Cron::maybe_schedule_retention();
        $cron1 = $GLOBALS['fbm_cron'];
        Cron::maybe_schedule_retention();
        $this->assertSame($cron1, $GLOBALS['fbm_cron']);
    }

    public function testReportShowsLastNext(): void {
        Cron::maybe_schedule_retention();
        $GLOBALS['fbm_options']['cron'] = $GLOBALS['fbm_cron'];
        Cron::run_retention();
        $GLOBALS['fbm_options']['cron'] = $GLOBALS['fbm_cron'];
        $data = DiagnosticsReport::data();
        $row = null;
        foreach ($data['cron'] as $r) {
            if ($r['hook'] === Cron::RETENTION_HOOK) {
                $row = $r;
                break;
            }
        }
        $this->assertNotNull($row);
        $this->assertGreaterThan(0, $row['last_run']);
        $this->assertGreaterThan(0, $row['next_run']);
    }
}
