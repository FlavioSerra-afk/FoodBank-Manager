<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FBM\Auth\Capabilities;

class CapabilitiesTest extends TestCase {
    public function testEnsureForAdminAddsAllCaps(): void {
        $role        = get_role('administrator');
        $role->caps  = [];

        Capabilities::ensure_for_admin();

        foreach (Capabilities::all() as $cap) {
            self::assertArrayHasKey($cap, $role->caps);
        }
    }
}
