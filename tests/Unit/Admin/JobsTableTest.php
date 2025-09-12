<?php
declare(strict_types=1);

namespace Tests\Unit\Admin;

use FBM\Admin\JobsTable;
use Tests\Support\JobsDbStub;
use Tests\Support\Exceptions\FbmDieException;
use function fbm_grant_caps;
use function fbm_seed_nonce;
use function fbm_nonce;

final class JobsTableTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        fbm_seed_nonce( 'unit' );
        $db = new JobsDbStub();
        $GLOBALS['wpdb'] = $db;
        $table = $db->prefix . 'fbm_jobs';
        for ( $i = 1; $i <= 30; $i++ ) {
            $db->tables[ $table ][ $i ] = array(
                'id'         => $i,
                'type'       => 't' . $i,
                'format'     => 'csv',
                'status'     => $i % 2 ? 'failed' : 'pending',
                'attempts'   => 0,
                'file_path'  => '',
                'created_at' => $i,
                'updated_at' => $i,
                'last_error' => '',
                'filters'    => '[]',
                'masked'     => 0,
            );
        }
    }

    public function testColumns(): void {
        $t = new JobsTable();
        $cols = $t->get_columns();
        $this->assertArrayHasKey( 'id', $cols );
        $this->assertArrayHasKey( 'status', $cols );
    }

    public function testPreparePagingSorting(): void {
        $_GET = array( 'paged' => 2, 'orderby' => 'id', 'order' => 'asc' );
        $t = new JobsTable();
        $t->prepare_items();
        $this->assertCount( 10, $t->items );
        $this->assertSame( 21, $t->items[0]['id'] );
    }

    public function testBulkRetryCancel(): void {
        fbm_grant_caps( array( 'fbm_manage_jobs' ) );
        $t = new JobsTable();
        $t->prepare_items();

        $_REQUEST = array(
            'action'   => 'retry',
            '_wpnonce' => fbm_nonce( 'bulk-jobs' ),
        );
        $_POST['job'] = array( 1, 3 );
        $t->process_bulk_action();
        $table = $GLOBALS['wpdb']->prefix . 'fbm_jobs';
        $this->assertSame( 'pending', $GLOBALS['wpdb']->tables[ $table ][1]['status'] );

        $_REQUEST = array(
            'action'   => 'cancel',
            '_wpnonce' => fbm_nonce( 'bulk-jobs' ),
        );
        $_POST['job'] = array( 2 );
        $t->process_bulk_action();
        $this->assertSame( 'cancelled', $GLOBALS['wpdb']->tables[ $table ][2]['status'] );
    }

    public function testBulkDeniedWithoutCap(): void {
        $_REQUEST = array(
            'action'   => 'retry',
            '_wpnonce' => fbm_nonce( 'bulk-jobs' ),
        );
        $_POST['job'] = array( 1 );
        $t = new JobsTable();
        $this->expectException( FbmDieException::class );
        $t->process_bulk_action();
    }
}
