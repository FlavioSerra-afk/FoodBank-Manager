<?php
declare(strict_types=1);

namespace {
    if (!function_exists('register_rest_route')) {
        function register_rest_route($ns, $route, $args) {
            $GLOBALS['__fbm_rest'][$ns . $route] = $args;
        }
    }
}

namespace Tests\Unit\Rest {

use FBM\Rest\JobsController;
use Tests\Support\Rbac;
use WP_REST_Request;

final class ArgsValidationTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        $GLOBALS['__fbm_rest'] = array();
        Rbac::revokeAll();
    }

    private function dispatch(array $route, array $params): \WP_REST_Response {
        $perm = $route['permission_callback'];
        if (!$perm()) {
            return new \WP_REST_Response(array(), 403);
        }
        foreach ($params as $name => $val) {
            if (isset($route['args'][$name]['sanitize_callback'])) {
                $val = call_user_func($route['args'][$name]['sanitize_callback'], $val);
            }
            if (isset($route['args'][$name]['validate_callback']) && !call_user_func($route['args'][$name]['validate_callback'], $val, null, null)) {
                return new \WP_REST_Response(array(), 400);
            }
        }
        $req = new WP_REST_Request('GET', '/fbm/v1/jobs');
        foreach ($params as $k => $v) {
            $req->set_param($k, $v);
        }
        return call_user_func($route['callback'], $req);
    }

    public function testInvalidArgsFail(): void {
        $c = new JobsController();
        $c->register_routes();
        $route = $GLOBALS['__fbm_rest']['fbm/v1/jobs'];
        Rbac::grantAdmin();
        $res = $this->dispatch($route, array('limit' => 0));
        $this->assertSame(400, $res->get_status());
        $res2 = $this->dispatch($route, array('limit' => 5));
        $this->assertSame(200, $res2->get_status());
    }
}

}
