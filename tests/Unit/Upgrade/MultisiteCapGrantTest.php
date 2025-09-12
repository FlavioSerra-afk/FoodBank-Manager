<?php
declare(strict_types=1);

namespace Tests\Unit\Upgrade;

use FoodBankManager\Core\Plugin;

final class MultisiteCapGrantTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        $GLOBALS['fbm_is_multisite'] = true;
        $GLOBALS['fbm_sites'] = array((object) ['blog_id' => 1], (object) ['blog_id' => 2]);
        $role = \get_role('administrator');
        $role?->remove_cap('fbm_manage_jobs');
        \update_site_option('fbm_caps_migrated_2025_09', 0);
        \update_option('fbm_version', '1.10.0');
    }

    public function testMigrationRunsOnceAcrossSites(): void {
        $ref = new \ReflectionClass(Plugin::class);
        $m   = $ref->getMethod('maybe_upgrade');
        $m->setAccessible(true);

        $m->invoke(null);
        $this->assertTrue(\get_role('administrator')->has_cap('fbm_manage_jobs'));
        $this->assertSame(array(1, 2), $GLOBALS['fbm_switched_to']);
        $GLOBALS['fbm_switched_to'] = array();
        $m->invoke(null);
        $this->assertSame(array(), $GLOBALS['fbm_switched_to']);
    }
}

