<?php
declare(strict_types=1);

use FBM\Auth\Capabilities;

class CapabilitiesTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        Capabilities::$ensured = false;
    }

    public function testEnsureForAdminAddsAllCapsIdempotently(): void {
        $role       = get_role('administrator');
        $role->caps = [];

        Capabilities::ensure_for_admin();
        foreach (Capabilities::all() as $cap) {
            self::assertArrayHasKey($cap, $role->caps);
        }

        $first = $role->caps;
        Capabilities::ensure_for_admin();
        self::assertSame($first, $role->caps);
    }
}
