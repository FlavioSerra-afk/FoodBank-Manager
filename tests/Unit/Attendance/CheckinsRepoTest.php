<?php
declare(strict_types=1);

namespace Tests\Unit\Attendance;

use BaseTestCase;
use FBM\Attendance\CheckinsRepo;

final class CheckinsRepoTest extends BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        $ref = new \ReflectionClass(CheckinsRepo::class);
        $p = $ref->getProperty('store');
        $p->setAccessible(true);
        $p->setValue(array());
        $p2 = $ref->getProperty('next_id');
        $p2->setAccessible(true);
        $p2->setValue(1);
        CheckinsRepo::record(array(
            'event_id'    => 1,
            'recipient'   => 'a@example.com',
            'token_hash'  => 'h1',
            'method'      => 'qr',
            'note'        => null,
            'by'          => 1,
            'verified_at' => '2024-01-01 00:00:00',
            'created_at'  => '2024-01-01 00:00:00',
        ));
        CheckinsRepo::record(array(
            'event_id'    => 1,
            'recipient'   => 'b@example.com',
            'token_hash'  => null,
            'method'      => 'manual',
            'note'        => 'note',
            'by'          => 2,
            'verified_at' => '2024-01-02 00:00:00',
            'created_at'  => '2024-01-02 00:00:00',
        ));
    }

    public function testListFiltersAndOrder(): void {
        $list = CheckinsRepo::list_for_event(1, array('method' => 'manual'));
        $this->assertCount(1, $list['rows']);
        $this->assertSame('manual', $list['rows'][0]['method']);
        $list2 = CheckinsRepo::list_for_event(1, array(), array('order_by' => 'recipient', 'order' => 'ASC', 'limit' => 1));
        $this->assertSame(2, $list2['total']);
        $this->assertCount(1, $list2['rows']);
        $this->assertSame('a@example.com', $list2['rows'][0]['recipient']);
    }

    public function testReplayGuard(): void {
        $this->expectException(\RuntimeException::class);
        CheckinsRepo::record(array(
            'event_id'    => 1,
            'recipient'   => 'c@example.com',
            'token_hash'  => 'h1',
            'method'      => 'qr',
            'note'        => null,
            'by'          => 3,
            'verified_at' => '2024-01-03 00:00:00',
            'created_at'  => '2024-01-03 00:00:00',
        ));
    }
}
