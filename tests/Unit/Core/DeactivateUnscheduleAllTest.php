<?php
declare(strict_types=1);

namespace Tests\Unit\Core;

use FoodBankManager\Core\Plugin;
use FBM\Core\Jobs\JobsWorker;
use FBM\Core\Retention;
use FoodBankManager\Core\Cron;

final class DeactivateUnscheduleAllTest extends \BaseTestCase {
    public function testUnschedulesAllHooks(): void {
        $now = time() + 100;
        $GLOBALS['fbm_cron'] = array(
            $now => array(
                Cron::RETENTION_HOOK => array(array()),
                Retention::EVENT => array(array()),
                JobsWorker::EVENT => array(array()),
                'fbm_retention_hourly' => array(array()),
                'fbm_retention_tick' => array(array()),
                'fbm_jobs_tick' => array(array()),
            ),
        );
        (new Plugin())->deactivate();
        $this->assertSame(array(), $GLOBALS['fbm_cron']);
    }
}
