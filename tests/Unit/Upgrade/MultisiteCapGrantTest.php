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
        $this->markTestSkipped('Upgrade routine not implemented in minimal plugin.');
    }
}

