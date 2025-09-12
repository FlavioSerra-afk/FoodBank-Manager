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
use function fbm_grant_caps;

final class JobsPermissionsTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        $GLOBALS['__fbm_rest'] = array();
        Rbac::revokeAll();
    }

    public function testPermissionAndValidation(): void {
        $c = new JobsController();
        $c->register_routes();
        $route = $GLOBALS['__fbm_rest']['fbm/v1/jobs'] ?? array();
        $this->assertArrayHasKey('permission_callback', $route);
        $perm = $route['permission_callback'];
        $this->assertFalse($perm());
        fbm_grant_caps(['fbm_manage_jobs']);
        $this->assertTrue($perm());
        $validate = $route['args']['limit']['validate_callback'];
        $this->assertFalse($validate(0));
        $this->assertTrue($validate(5));
    }
}
}

