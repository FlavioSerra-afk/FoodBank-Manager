<?php
declare(strict_types=1);

namespace Tests\Unit\Permissions;

use FBM\Auth\Capabilities;
use FoodBankManager\Admin\UsersMeta;

final class UserOverridesTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        $GLOBALS['fbm_users'][] = array(
            'ID' => 2,
            'user_login' => 'u2',
            'roles' => array(),
        );
    }

    public function testGrantRevoke(): void {
        UsersMeta::set_user_caps(2, array('fb_manage_dashboard'));
        $caps = Capabilities::effective_caps_for_user(2);
        $this->assertArrayHasKey('fb_manage_dashboard', $caps);
        UsersMeta::set_user_caps(2, array());
        $caps = Capabilities::effective_caps_for_user(2);
        $this->assertArrayNotHasKey('fb_manage_dashboard', $caps);
    }
}
