<?php
declare(strict_types=1);

namespace Tests\Unit\Permissions;

use FoodBankManager\Admin\PermissionsPage;
use FoodBankManager\Admin\UsersMeta;
use Tests\Support\Rbac;

final class DefaultsResetTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        add_role('trole', 'Test');
        $GLOBALS['fbm_users'][] = array(
            'ID' => 3,
            'user_login' => 'u3',
            'roles' => array('trole'),
        );
        update_option('fbm_permissions_defaults', array('trole' => array('fb_manage_dashboard')));
        $role = get_role('trole');
        $role->add_cap('fb_manage_settings');
        UsersMeta::set_user_caps(3, array('fb_manage_settings'));
    }

    public function testResetRestoresDefaults(): void {
        Rbac::grantForPage('fbm_permissions');
        $ref = new \ReflectionClass(PermissionsPage::class);
        $m = $ref->getMethod('handle_reset');
        $m->setAccessible(true);
        try {
            $m->invoke(new PermissionsPage());
        } catch (\Tests\Support\Exceptions\FbmDieException $e) {
            // redirect
        }
        $role = get_role('trole');
        $this->assertTrue($role->has_cap('fb_manage_dashboard'));
        $this->assertFalse($role->has_cap('fb_manage_settings'));
        $this->assertSame(array(), UsersMeta::get_user_caps(3));
    }
}
