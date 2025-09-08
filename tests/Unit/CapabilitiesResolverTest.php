<?php declare(strict_types=1);

use FBM\Auth\Capabilities;
use PHPUnit\Framework\TestCase;

final class CapabilitiesResolverTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        fbm_test_reset_globals();
        do_action('fbm_test_reset_caps');
    }

    public function testMergesRoleAndOverrides(): void {
        add_role('editor', 'Editor', ['fb_manage_dashboard' => true]);
        $user_id = 10;
        $GLOBALS['fbm_users'][] = ['ID' => $user_id, 'roles' => ['editor']];
        update_user_meta($user_id, 'fbm_user_caps', [
            'fb_manage_forms'    => true,
            'fb_manage_dashboard' => false,
        ]);

        $caps = Capabilities::effective_caps_for_user($user_id);
        $this->assertArrayHasKey('fb_manage_forms', $caps);
        $this->assertTrue($caps['fb_manage_forms']);
        $this->assertArrayNotHasKey('fb_manage_dashboard', $caps);
    }

    public function testUnknownCapsIgnored(): void {
        $uid = 11;
        $GLOBALS['fbm_users'][] = ['ID' => $uid, 'roles' => []];
        update_user_meta($uid, 'fbm_user_caps', ['unknown_cap' => true]);

        $caps = Capabilities::effective_caps_for_user($uid);
        $this->assertArrayNotHasKey('unknown_cap', $caps);
    }
}

