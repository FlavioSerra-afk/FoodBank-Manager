<?php
declare(strict_types=1);

namespace Tests\Unit\Attendance;

use FBM\Attendance\EventsRepo;
use FBM\Attendance\ManualCheckinService;
use FBM\Attendance\CheckinsRepo;
use Tests\Support\EventsDbStub;

final class ManualCheckinServiceTest extends \BaseTestCase {
    private EventsDbStub $db;

    protected function setUp(): void {
        parent::setUp();
        $this->db = new EventsDbStub();
        $GLOBALS['wpdb'] = $this->db;
        $ref = new \ReflectionClass(CheckinsRepo::class);
        $p = $ref->getProperty('store');
        $p->setAccessible(true);
        $p->setValue(null, array());
        $p2 = $ref->getProperty('next_id');
        $p2->setAccessible(true);
        $p2->setValue(null, 1);
        $GLOBALS['fbm_filters']['fbm_now'][] = static fn($v) => 1700000000;
        EventsRepo::create(array(
            'title'     => 'Event',
            'starts_at' => '2024-01-01 00:00:00',
            'ends_at'   => '2024-01-01 01:00:00',
            'status'    => 'active',
        ));
    }

    protected function tearDown(): void {
        $GLOBALS['fbm_filters']['fbm_now'] = array();
        parent::tearDown();
    }

    public function testSuccess(): void {
        $id = ManualCheckinService::check_in(1, '<b>user@example.com</b>', 'valid note', 9);
        $this->assertGreaterThan(0, $id);
        $rows = CheckinsRepo::list_for_event(1);
        $this->assertSame(1, $rows['total']);
        $row = $rows['rows'][0];
        $this->assertSame('manual', $row['method']);
        $this->assertSame(9, $row['by']);
        $this->assertSame('user@example.com', $row['recipient']);
    }

    public function testValidationFailures(): void {
        $this->expectException(\DomainException::class);
        ManualCheckinService::check_in(1, '', 'no', 9);
    }

    public function testInactiveEvent(): void {
        EventsRepo::update(1, array('status' => 'cancelled'));
        $this->expectException(\DomainException::class);
        ManualCheckinService::check_in(1, 'user@example.com', 'valid note', 9);
    }
}
