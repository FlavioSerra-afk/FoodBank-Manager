<?php
declare(strict_types=1);

namespace Tests\Unit\Permissions;

use FBM\Auth\Capabilities;

final class RoleMappingTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        add_role('trole', 'Test');
        $GLOBALS['fbm_users'][] = array(
            'ID' => 1,
            'user_login' => 'u',
            'roles' => array('trole'),
        );
    }

    public function testAddRemoveCapAffectsUser(): void {
        $caps = Capabilities::effective_caps_for_user(1);
        $this->assertArrayNotHasKey('fb_manage_dashboard', $caps);
        $role = get_role('trole');
        $role->add_cap('fb_manage_dashboard');
        $caps = Capabilities::effective_caps_for_user(1);
        $this->assertArrayHasKey('fb_manage_dashboard', $caps);
        $role->remove_cap('fb_manage_dashboard');
        $caps = Capabilities::effective_caps_for_user(1);
        $this->assertArrayNotHasKey('fb_manage_dashboard', $caps);
    }
}
