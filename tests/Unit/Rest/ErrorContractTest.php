<?php
declare(strict_types=1);

namespace Tests\Unit\Rest;

use FBM\Rest\ErrorHelper;
use WP_Error;

final class ErrorContractTest extends \BaseTestCase {
    public function testInvalidArgs(): void {
        $res = ErrorHelper::from_wp_error(new WP_Error('rest_invalid_param', 'bad'));
        $this->assertSame(422, $res['status']);
        $this->assertSame('invalid_param', $res['body']['error']['code']);
    }

    public function testMissingAuth(): void {
        $res = ErrorHelper::from_wp_error(new WP_Error('rest_not_logged_in', 'no')); 
        $this->assertSame(401, $res['status']);
        $this->assertSame('unauthorized', $res['body']['error']['code']);
    }

    public function testMissingCap(): void {
        $res = ErrorHelper::from_wp_error(new WP_Error('rest_forbidden', 'no')); 
        $this->assertSame(403, $res['status']);
        $this->assertSame('forbidden', $res['body']['error']['code']);
    }

    public function testNotFound(): void {
        $res = ErrorHelper::from_wp_error(new WP_Error('rest_no_route', 'nope')); 
        $this->assertSame(404, $res['status']);
        $this->assertSame('not_found', $res['body']['error']['code']);
    }
}
