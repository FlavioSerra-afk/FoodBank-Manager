<?php
declare(strict_types=1);

namespace Tests\Unit\Rest;

use BaseTestCase;
use FBM\Rest\ScanController;
use FBM\Attendance\TicketService;
use FBM\Attendance\EventsRepo;
use FBM\Attendance\CheckinsRepo;
use Tests\Support\EventsDbStub;
require_once __DIR__ . '/../../Support/EventsDbStub.php';
use Tests\Support\Rbac;
use WP_REST_Request;

if (!defined('FBM_KEK_BASE64')) {
    define('FBM_KEK_BASE64', base64_encode(str_repeat('k', 32)));
}

function fbm_scan_now($v) { // @phpstan-ignore-line
    return 1700000000;
}

final class ScanControllerTest extends BaseTestCase {
    private EventsDbStub $db;

    protected function setUp(): void {
        parent::setUp();
        $this->db = new EventsDbStub();
        $GLOBALS['wpdb'] = $this->db;
        fbm_test_trust_nonces(true);
        $ref = new \ReflectionClass(CheckinsRepo::class);
        $p = $ref->getProperty('store');
        $p->setAccessible(true);
        $p->setValue(array());
        $p2 = $ref->getProperty('next_id');
        $p2->setAccessible(true);
        $p2->setValue(1);
        add_filter('fbm_now', __NAMESPACE__ . '\\fbm_scan_now');
        EventsRepo::create(array(
            'title'     => 'Event',
            'starts_at' => '2024-01-01 00:00:00',
            'ends_at'   => '2024-01-01 01:00:00',
            'status'    => 'active',
        ));
        Rbac::grantForPage('fbm_scan');
    }

    private function req(string $token): WP_REST_Request {
        $r = new WP_REST_Request('POST', '/fbm/v1/scan');
        $r->set_header('x-wp-nonce', wp_create_nonce('wp_rest'));
        $r->set_param('token', $token);
        return $r;
    }

    public function testSuccessAndReplay(): void {
        $controller = new ScanController();
        $token = TicketService::fromPayload(1, 'jane@example.com', 1700000060, 'abcd');
        $res = $controller->verify($this->req($token['token']));
        $data = $res->get_data();
        $this->assertTrue($data['ok']);
        $this->assertSame('checked_in', $data['status']);
        $this->assertSame('j***@example.com', $data['recipient_masked']);
        $this->assertSame(1, CheckinsRepo::list_for_event(1)['total']);
        $res2 = $controller->verify($this->req($token['token']));
        $this->assertSame('replay', $res2->get_data()['status']);
    }

    public function testExpired(): void {
        $controller = new ScanController();
        $token = TicketService::fromPayload(1, 'jane@example.com', 1699999990, 'abcd');
        $res = $controller->verify($this->req($token['token']));
        $this->assertSame('expired', $res->get_data()['status']);
        $this->assertSame(0, CheckinsRepo::list_for_event(1)['total']);
    }

    public function testInvalid(): void {
        $controller = new ScanController();
        $res = $controller->verify($this->req('badtoken'));
        $this->assertSame('invalid', $res->get_data()['status']);
    }

    public function testDenied(): void {
        Rbac::revokeAll();
        $controller = new ScanController();
        $token = TicketService::fromPayload(1, 'jane@example.com', 1700000060, 'abcd');
        $res = $controller->verify($this->req($token['token']));
        $this->assertSame('denied', $res->get_data()['status']);
    }
}
