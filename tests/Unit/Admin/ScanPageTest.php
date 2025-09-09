<?php
declare(strict_types=1);

namespace Tests\Unit\Admin;

use BaseTestCase;
use FBM\Admin\ScanPage;
use FBM\Attendance\TicketService;
use FBM\Attendance\EventsRepo;
use FBM\Attendance\CheckinsRepo;
use Tests\Support\EventsDbStub;
use Tests\Support\Rbac;

if (!defined('FBM_KEK_BASE64')) {
    define('FBM_KEK_BASE64', base64_encode(str_repeat('k', 32)));
}

final class ScanPageTest extends BaseTestCase {
    private EventsDbStub $db;

    protected function setUp(): void {
        parent::setUp();
        $this->db = new EventsDbStub();
        $GLOBALS['wpdb'] = $this->db;
        $ref = new \ReflectionClass(CheckinsRepo::class);
        $p = $ref->getProperty('store');
        $p->setAccessible(true);
        $p->setValue(array());
        $p2 = $ref->getProperty('next_id');
        $p2->setAccessible(true);
        $p2->setValue(1);
        $GLOBALS['fbm_filters']['fbm_now'][] = static fn($v) => 1700000000;
        if (!defined('FBM_PATH')) {
            define('FBM_PATH', dirname(__DIR__, 3) . '/');
        }
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

    public function testRenderDenied(): void {
        Rbac::revokeAll();
        ob_start();
        ScanPage::route();
        $html = (string) ob_get_clean();
        $this->assertStringContainsString('fbm-admin', $html);
        $this->assertStringContainsString('permission', $html);
    }

    public function testRenderAndSubmit(): void {
        Rbac::grantForPage('fbm_scan');
        ob_start();
        ScanPage::route();
        $html = (string) ob_get_clean();
        $this->assertStringContainsString('data-testid="fbm-scan-status"', $html);
        $token = TicketService::createToken(1, 'jane@example.com', 1700000060);
        fbm_seed_nonce('fbm_scan_verify');
        fbm_test_trust_nonces(true);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = array(
            'token'     => $token['token'],
            'fbm_nonce' => wp_create_nonce('fbm_scan_verify'),
        );
        ob_start();
        ScanPage::route();
        $html = (string) ob_get_clean();
        $this->assertStringContainsString('checked_in', $html);
        $this->assertStringContainsString('j***@example.com', $html);
    }
}
