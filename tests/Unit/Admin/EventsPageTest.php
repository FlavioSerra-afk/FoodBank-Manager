<?php
declare(strict_types=1);

namespace Tests\Unit\Admin;

require_once __DIR__ . '/../../Support/EventsDbStub.php';

use BaseTestCase;
use FBM\Admin\EventsPage;
use Tests\Support\Rbac;
use Tests\Support\EventsDbStub;

final class EventsPageTest extends BaseTestCase {
    private EventsDbStub $db;

    protected function setUp(): void {
        parent::setUp();
        if (!defined('FBM_PATH')) {
            define('FBM_PATH', dirname(__DIR__, 3) . '/');
        }
        $this->db = new EventsDbStub();
        $GLOBALS['wpdb'] = $this->db;
        $GLOBALS['fbm_filters']['fbm_now'][] = fn($v) => 1700000000;
    }

    protected function tearDown(): void {
        $GLOBALS['fbm_filters']['fbm_now'] = [];
        parent::tearDown();
    }

    public function testRenderDenied(): void {
        Rbac::revokeAll();
        ob_start();
        EventsPage::route();
        $html = (string) ob_get_clean();
        $this->assertStringContainsString('You do not have permission', $html);
    }

    public function testListRenderEscapesOutput(): void {
        Rbac::grantForPage('fbm_events');
        $this->db->insert('wp_fb_events', [
            'title' => '<script>alert(1)</script>',
            'starts_at' => '2024-01-01 00:00:00',
            'ends_at' => '2024-01-01 01:00:00',
            'location' => '<b>loc</b>',
            'capacity' => 1,
            'notes' => '',
            'status' => 'active',
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-01-01 00:00:00',
        ]);
        ob_start();
        EventsPage::route();
        $html = (string) ob_get_clean();
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('Events', $html);
    }

    public function testAddEditDeleteFlow(): void {
        Rbac::grantForPage('fbm_events');
        fbm_seed_nonce('unit-seed');
        fbm_test_trust_nonces(true);
        // Create
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'fbm_action' => 'save',
            'title' => 'One',
            'starts_at' => '2024-01-01 00:00:00',
            'ends_at' => '2024-01-01 01:00:00',
            'status' => 'active',
            'fbm_nonce' => wp_create_nonce('fbm_events_save'),
        ];
        try {
            EventsPage::route();
        } catch (\Tests\Support\Exceptions\FbmDieException $e) {}
        $this->assertNotNull( \FBM\Attendance\EventsRepo::get(1) );
        // Update
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'fbm_action' => 'save',
            'id' => 1,
            'title' => 'Two',
            'starts_at' => '2024-01-02 00:00:00',
            'ends_at' => '2024-01-02 01:00:00',
            'status' => 'cancelled',
            'fbm_nonce' => wp_create_nonce('fbm_events_save'),
        ];
        try {
            EventsPage::route();
        } catch (\Tests\Support\Exceptions\FbmDieException $e) {}
        $event = \FBM\Attendance\EventsRepo::get(1);
        $this->assertNotNull($event);
        $this->assertSame('Two', $event['title']);
        $this->assertSame('cancelled', $event['status']);
        // Delete
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'fbm_action' => 'delete',
            'id' => 1,
            'fbm_nonce' => wp_create_nonce('fbm_events_delete_1'),
        ];
        try {
            EventsPage::route();
        } catch (\Tests\Support\Exceptions\FbmDieException $e) {}
        $this->assertNull( \FBM\Attendance\EventsRepo::get(1) );
    }
}
