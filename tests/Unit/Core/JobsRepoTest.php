<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Support/JobsDbStub.php';

use Tests\Support\JobsDbStub;
use FBM\Core\Jobs\JobsRepo;

final class JobsRepoTest extends \BaseTestCase {
    private JobsDbStub $db;

    protected function setUp(): void {
        parent::setUp();
        $this->db = new JobsDbStub();
        $GLOBALS['wpdb'] = $this->db;
    }

    public function testLifecycleAndListing(): void {
        $id = JobsRepo::create('attendance_export', 'csv', array('a' => 'b'), true);
        $this->assertSame(1, $id);
        $job = JobsRepo::get($id);
        $this->assertSame('attendance_export', $job['type']);
        $this->assertTrue($job['masked']);
        $this->assertSame('b', $job['filters']['a']);
        JobsRepo::mark_failed($id, 'boom');
        $this->assertSame('failed', JobsRepo::get($id)['status']);
        JobsRepo::retry($id);
        $this->assertSame('pending', JobsRepo::get($id)['status']);
        JobsRepo::mark_done($id, '/tmp/file.csv');
        $this->assertSame('done', JobsRepo::get($id)['status']);
        $this->assertSame('/tmp/file.csv', JobsRepo::get($id)['file_path']);

        // Ordering and clamp
        JobsRepo::create('attendance_export', 'csv', array(), true);
        JobsRepo::list(array('order_by' => 'created_at', 'order' => 'DESC', 'limit' => 500));
        $this->assertStringContainsString('ORDER BY created_at DESC', (string) $this->db->last_sql);
        $this->assertSame(100, $this->db->last_args[0]);
    }
}
