<?php
declare(strict_types=1);

namespace Tests\Unit\Admin;

use FBM\Admin\JobsTable;
use FBM\Core\Jobs\JobsRepo;
use Tests\Support\JobsDbStub;
use Tests\Support\Rbac;
use function fbm_grant_caps;
use function fbm_seed_nonce;
use function fbm_nonce;

final class JobsCapEnforcementTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        fbm_seed_nonce('unit');
        $GLOBALS['wpdb'] = new JobsDbStub();
        JobsRepo::create('foo', 'csv', [], false);
        JobsRepo::create('bar', 'csv', [], false);
    }

    public function testReadOnlyWithoutCap(): void {
        Rbac::revokeAll();
        $t = new JobsTable();
        $ref = new \ReflectionClass(JobsTable::class);
        $m   = $ref->getMethod('get_bulk_actions');
        $m->setAccessible(true);
        $this->assertSame(array(), $m->invoke($t));
        $cb = $ref->getMethod('column_cb');
        $cb->setAccessible(true);
        $this->assertSame('', $cb->invoke($t, ['id' => 1]));
    }

    public function testBulkActionNeedsNonceAndCap(): void {
        fbm_grant_caps(['fbm_manage_jobs']);
        $t = new JobsTable();
        $t->prepare_items();
        $_REQUEST = array(
            'action'   => 'retry',
            '_wpnonce' => fbm_nonce('bulk-jobs'),
        );
        $_POST['job'] = array(1);
        $t->process_bulk_action();
        $table = $GLOBALS['wpdb']->prefix . 'fbm_jobs';
        $this->assertSame('pending', $GLOBALS['wpdb']->tables[$table][1]['status']);
    }
}

