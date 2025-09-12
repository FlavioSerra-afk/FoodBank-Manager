<?php
declare(strict_types=1);

namespace Tests\Unit\Cron;

use FoodBankManager\Core\Cron;
use FoodBankManager\Admin\DiagnosticsPage;
use PHPUnit\Framework\TestCase;
use function update_option;
use function delete_option;
use function get_option;
use function wp_clear_scheduled_hook;

final class TelemetryTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        global $fbm_cron;
        $fbm_cron = [];
        delete_option('cron');
        wp_clear_scheduled_hook(Cron::RETENTION_HOOK);
        delete_option(Cron::RETENTION_HOOK . '_last_run');
        delete_option(Cron::RETENTION_HOOK . '_next_run');
    }

    public function testScheduleAndReport(): void {
        Cron::maybe_schedule_retention();
        update_option('cron', $GLOBALS['fbm_cron']);
        $next = get_option(Cron::RETENTION_HOOK . '_next_run');
        Cron::maybe_schedule_retention();
        $this->assertSame($next, get_option(Cron::RETENTION_HOOK . '_next_run'));
        Cron::run_retention();
        update_option('cron', $GLOBALS['fbm_cron']);
        $last = get_option(Cron::RETENTION_HOOK . '_last_run');
        $rows = DiagnosticsPage::cron_status();
        $this->assertSame($last, $rows[0]['last_run']);
        $this->assertArrayHasKey('next_run', $rows[0]);
    }
}
