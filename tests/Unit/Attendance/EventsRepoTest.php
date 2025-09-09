<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Support/EventsDbStub.php';

use PHPUnit\Framework\TestCase;
use FBM\Attendance\EventsRepo;
use Tests\Support\EventsDbStub;

final class EventsRepoTest extends TestCase {
    private EventsDbStub $db;

    protected function setUp(): void {
        parent::setUp();
        $this->db = new EventsDbStub();
        $GLOBALS['wpdb'] = $this->db;
        fbm_seed_nonce('unit');
        $GLOBALS['fbm_filters']['fbm_now'][] = fn($v) => 1700000000;
    }

    protected function tearDown(): void {
        $GLOBALS['fbm_filters']['fbm_now'] = [];
        parent::tearDown();
    }

    public function testCrudFlow(): void {
        $id = EventsRepo::create([
            'title' => '<b>Test</b>',
            'starts_at' => '2024-01-01 10:00:00',
            'ends_at' => '2024-01-01 09:00:00',
            'location' => '<script>loc</script>',
            'capacity' => -5,
            'notes' => '<b>note</b>',
            'status' => 'invalid',
        ]);
        $this->assertSame(1, $id);
        $fetched = EventsRepo::get(1);
        $this->assertSame('Test', $fetched['title']);
        $this->assertSame('2024-01-01 10:00:00', $fetched['ends_at']);
        $this->assertSame(0, $fetched['capacity']);
        $this->assertSame('active', $fetched['status']);

        EventsRepo::update(1, ['status' => 'cancelled']);
        $updated = EventsRepo::get(1);
        $this->assertSame('cancelled', $updated['status']);

        EventsRepo::delete(1);
        $this->assertNull(EventsRepo::get(1));
    }

    public function testListFiltersAndOrdering(): void {
        $this->db->insert('wp_fb_events', [
            'title' => 'Foo',
            'starts_at' => '2024-01-01 00:00:00',
            'ends_at' => '2024-01-01 01:00:00',
            'location' => '',
            'capacity' => 1,
            'notes' => '',
            'status' => 'active',
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-01-01 00:00:00',
        ]);
        $this->db->insert('wp_fb_events', [
            'title' => 'Bar',
            'starts_at' => '2024-02-01 00:00:00',
            'ends_at' => '2024-02-01 01:00:00',
            'location' => '',
            'capacity' => 1,
            'notes' => '',
            'status' => 'cancelled',
            'created_at' => '2024-02-01 00:00:00',
            'updated_at' => '2024-02-01 00:00:00',
        ]);

        EventsRepo::list(
            ['status' => 'active', 'q' => 'Foo'],
            ['order_by' => 'title', 'order' => 'DESC', 'limit' => 500, 'offset' => -5]
        );
        $sql = $this->db->prepared[0] ?? '';
        $args = $this->db->args_history[0] ?? [];
        $this->assertStringContainsString('status = %s', $sql);
        $this->assertStringContainsString('title LIKE %s', $sql);
        $this->assertStringContainsString('ORDER BY title DESC', $sql);
        $this->assertSame(200, $args[count($args)-2]);
        $this->assertSame(0, $args[count($args)-1]);
    }
}
