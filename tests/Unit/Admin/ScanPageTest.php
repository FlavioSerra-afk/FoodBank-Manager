<?php
declare(strict_types=1);

namespace Tests\Unit\Admin;

use FBM\Rest\ScanController;
use FBM\Attendance\TicketService;
use FBM\Attendance\EventsRepo;
use Tests\Support\Rbac;
use WP_REST_Request;

final class ScanPageTest extends \BaseTestCase {
    private object $factory;
    private $nowCb;

    protected function setUp(): void {
        parent::setUp();
        $this->factory = new class {
            public $post;
            public $user;
            public function __construct() {
                $this->post = new class {
                    public function create(array $args = array()): int {
                        return wp_insert_post($args);
                    }
                };
                $this->user = new class {
                    public function create(array $args = array()): int {
                        $id                 = count($GLOBALS['fbm_users']) + 1;
                        $GLOBALS['fbm_users'][$id] = $args + array('ID' => $id);
                        return $id;
                    }
                };
            }
        };
        $this->factory->post->create(array('post_title' => 'Sample'));
        $user_id                    = $this->factory->user->create(array('user_login' => 'scanner'));
        update_user_meta($user_id, 'role', 'administrator');
        $GLOBALS['fbm_current_user'] = $user_id;
        Rbac::grantForPage('fbm_scan');
        $this->nowCb = static fn($v) => 1700000000;
        add_filter('fbm_now', $this->nowCb);
        EventsRepo::create(array(
            'title'     => 'Event',
            'starts_at' => '2024-01-01 00:00:00',
            'ends_at'   => '2024-01-01 01:00:00',
            'status'    => 'active',
        ));
    }

    protected function tearDown(): void {
        remove_filter('fbm_now', $this->nowCb);
        wp_clear_scheduled_hook('fbm_retention_hourly');
        remove_all_actions('init');
        remove_all_actions('admin_init');
        parent::tearDown();
    }

    public function testEndpointCheckedIn(): void {
        $controller = new ScanController();
        $token = TicketService::fromPayload(1, 'jane@example.com', 1700000060, 'abcd');
        $req = new WP_REST_Request('POST', '/fbm/v1/scan');
        $req->set_header('x-wp-nonce', wp_create_nonce('wp_rest'));
        $req->set_param('token', $token['token']);
        $res  = $controller->verify($req);
        $data = $res->get_data();
        $this->assertTrue($data['checked_in']);
        $this->assertSame('checked-in', $data['status']);
    }
}
