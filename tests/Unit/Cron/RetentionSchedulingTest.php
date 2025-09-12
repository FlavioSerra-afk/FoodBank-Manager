<?php
declare(strict_types=1);

namespace Tests\Unit\Cron;

use FoodBankManager\Core\Cron;

use function wp_clear_scheduled_hook;

final class RetentionSchedulingTest extends \BaseTestCase {
    public function testSchedulesOnce(): void {
        wp_clear_scheduled_hook(Cron::RETENTION_HOOK);
        Cron::maybe_schedule_retention();
        Cron::maybe_schedule_retention();
        $count = 0;
        $cron  = $GLOBALS['fbm_cron'] ?? [];
        foreach ($cron as $events) {
            if (isset($events[Cron::RETENTION_HOOK])) {
                $count += count($events[Cron::RETENTION_HOOK]);
            }
        }
        $this->assertSame(1, $count);
    }
}

