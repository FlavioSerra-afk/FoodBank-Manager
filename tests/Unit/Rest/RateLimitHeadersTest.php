<?php
declare(strict_types=1);

namespace Tests\Unit\Rest;

use FBM\Rest\ScanController;
use FBM\Attendance\EventsRepo;
use Tests\Support\EventsDbStub;
use Tests\Support\Rbac;
use WP_REST_Request;

if (!defined('FBM_KEK_BASE64')) {
    define('FBM_KEK_BASE64', base64_encode(str_repeat('k', 32)));
}

final class RateLimitHeadersTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        $db = new EventsDbStub();
        $GLOBALS['wpdb'] = $db;
        $GLOBALS['fbm_transients'] = array();
        EventsRepo::create([
            'title' => 'Event',
            'starts_at' => '2024-01-01 00:00:00',
            'ends_at' => '2024-01-01 01:00:00',
            'status' => 'active',
        ]);
        Rbac::grantForPage('fbm_scan');
        $_SERVER['REMOTE_ADDR'] = '1.1.1.1';
        fbm_test_trust_nonces(true);
    }

    private function req(): WP_REST_Request {
        $r = new WP_REST_Request('POST', '/fbm/v1/scan');
        $r->set_header('x-wp-nonce', wp_create_nonce('wp_rest'));
        $r->set_param('token', 'badtoken');
        return $r;
    }

    public function testHeadersAndThrottle(): void {
        $c = new ScanController();
        $c->verify($this->req());
        $headers = $GLOBALS['__fbm_sent_headers'];
        $this->assertContains('X-FBM-RateLimit-Limit: 5', $headers);
        $this->assertContains('X-FBM-RateLimit-Remaining: 4', $headers);
        for ($i=0;$i<4;$i++) {
            $c->verify($this->req());
        }
        $res = $c->verify($this->req());
        $this->assertSame(429, $res->get_status());
        $headers = $GLOBALS['__fbm_sent_headers'];
        $this->assertContains('X-FBM-RateLimit-Remaining: 0', $headers);
    }
}
