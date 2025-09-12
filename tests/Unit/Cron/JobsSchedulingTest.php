<?php
declare(strict_types=1);

namespace Tests\Unit\Cron;

use FBM\Core\Jobs\JobsWorker;
use function wp_clear_scheduled_hook;

final class JobsSchedulingTest extends \BaseTestCase {
    public function testSchedulesOnce(): void {
        wp_clear_scheduled_hook( JobsWorker::EVENT );
        JobsWorker::schedule();
        JobsWorker::schedule();
        $count = 0;
        $cron  = $GLOBALS['fbm_cron'] ?? array();
        foreach ( $cron as $events ) {
            if ( isset( $events[ JobsWorker::EVENT ] ) ) {
                $count += count( $events[ JobsWorker::EVENT ] );
            }
        }
        $this->assertSame( 1, $count );
    }
}
