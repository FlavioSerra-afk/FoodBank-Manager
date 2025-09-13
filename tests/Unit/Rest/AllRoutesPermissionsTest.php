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

use FoodBankManager\Rest\Api;
use FBM\Rest\ScanController;

final class AllRoutesPermissionsTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        $GLOBALS['__fbm_rest'] = array();
    }

    public function testAllRoutesHavePermissionCallbacks(): void {
        Api::register_routes();
        $scan = new ScanController();
        $scan->register();
        foreach ($GLOBALS['__fbm_rest'] as $path => $route) {
            $this->assertArrayHasKey('permission_callback', $route, $path . ' missing permission_callback');
            $this->assertNotEmpty($route['permission_callback']);
            $this->assertArrayHasKey('args', $route, $path . ' missing args');
            foreach ($route['args'] as $arg) {
                $this->assertTrue(
                    isset($arg['sanitize_callback']) || isset($arg['validate_callback']),
                    $path . ' arg missing sanitize/validate'
                );
            }
        }
    }
}

}
