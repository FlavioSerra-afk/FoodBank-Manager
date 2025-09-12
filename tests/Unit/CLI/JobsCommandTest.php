<?php
declare(strict_types=1);

namespace Tests\Unit\CLI;

require_once __DIR__ . '/../../Support/FakeIO.php';
require_once __DIR__ . '/../../Support/JobsDbStub.php';

use FoodBankManager\CLI\Commands;
use Tests\Support\FakeIO;
use Tests\Support\JobsDbStub;
use FBM\Core\Jobs\JobsRepo;

final class JobsCommandTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        global $wpdb;
        $wpdb = new JobsDbStub();
    }

    public function testListOutputsJobs(): void {
        JobsRepo::create('foo', 'csv', [], false);
        $io  = new FakeIO();
        $cmd = new Commands($io);
        $cmd->jobs_list([], []);
        $this->assertStringContainsString('ID', $io->lines[0] ?? '');
        $this->assertNotEmpty($io->lines[1] ?? '');
    }

    public function testRetrySuccessAndFailure(): void {
        $id = JobsRepo::create('foo', 'csv', [], false);
        \fbm_grant_caps(['fbm_manage_jobs']);
        $io  = new FakeIO();
        $cmd = new Commands($io);
        $cmd->jobs_retry([$id], []);
        $this->assertSame('Job retried', $io->success[0] ?? '');

        \fbm_grant_caps([]);
        $io2  = new FakeIO();
        $cmd2 = new Commands($io2);
        $cmd2->jobs_retry([$id], []);
        $this->assertNotEmpty($io2->errors);
    }
}

