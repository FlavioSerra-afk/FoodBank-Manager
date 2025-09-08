<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FBM\Auth\Capabilities;

class CapabilitiesTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        fbm_test_reset_globals();
        do_action('fbm_test_reset_caps');
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
