<?php
declare(strict_types=1);

namespace Tests\Unit\Rest;

use FBM\Rest\ThrottleController;
use WP_REST_Request;

final class ThrottleEndpointsTest extends \BaseTestCase {
    public function testGetRequiresCap(): void {
        \fbm_grant_caps( array() );
        $controller = new ThrottleController();
        $res = $controller->get( new WP_REST_Request( 'GET', '/fbm/v1/throttle' ) );
        $this->assertSame( 403, $res->get_status() );
    }

    public function testPostValidation(): void {
        \fbm_grant_caps( array( 'fb_manage_diagnostics' ) );
        $controller = new ThrottleController();
        $req = new WP_REST_Request( 'POST', '/fbm/v1/throttle' );
        $req->set_param( 'role_multipliers', array( 'invalid' => 'x' ) );
        $res = $controller->update( $req );
        $this->assertSame( 422, $res->get_status() );
    }

    public function testPostSuccess(): void {
        \fbm_grant_caps( array( 'fb_manage_diagnostics' ) );
        $GLOBALS['fbm_roles']['editor'] = (object) array( 'caps' => array() );
        $controller = new ThrottleController();
        $req = new WP_REST_Request( 'POST', '/fbm/v1/throttle' );
        $req->set_param( 'window_seconds', 40 );
        $req->set_param( 'base_limit', 8 );
        $req->set_param( 'role_multipliers', array( 'editor' => 2 ) );
        $res = $controller->update( $req );
        $this->assertSame( 200, $res->get_status() );
        $data = $res->get_data();
        $this->assertSame( 40, $data['settings']['window_seconds'] );
        $this->assertSame( 8, $data['settings']['base_limit'] );
        $this->assertSame( 16, $data['limits']['editor'] );
    }
}

