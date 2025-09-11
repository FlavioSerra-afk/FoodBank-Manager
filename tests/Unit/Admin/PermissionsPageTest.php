<?php
declare(strict_types=1);

namespace Tests\Unit\Admin;

use FoodBankManager\Admin\PermissionsPage;
use Tests\Support\Rbac;
use Tests\Support\Exceptions\FbmDieException;

final class PermissionsPageTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        if (!defined('FBM_PATH')) {
            define('FBM_PATH', dirname(__DIR__, 3) . '/');
        }
    }

    public function testRenderDenied(): void {
        Rbac::revokeAll();
        $this->expectException(FbmDieException::class);
        PermissionsPage::route();
    }

    public function testNonceFailure(): void {
        Rbac::grantForPage('fbm_permissions');
        $_POST = array(
            'role' => 'test',
            'cap' => 'fb_manage_dashboard',
            'grant' => '1',
            '_wpnonce' => 'bad',
        );
        $this->expectException(FbmDieException::class);
        PermissionsPage::handle_role_toggle();
    }

    public function testToggleRoleCap(): void {
        Rbac::grantForPage('fbm_permissions');
        add_role('testrole', 'Test Role');
        fbm_seed_nonce('unit-seed');
        $_POST = array(
            'role' => 'testrole',
            'cap' => 'fb_manage_dashboard',
            'grant' => '1',
            '_wpnonce' => wp_create_nonce('fbm_perms_role_toggle'),
        );
        try {
            PermissionsPage::handle_role_toggle();
        } catch (FbmDieException $e) {
            // expected due to wp_die in handler
        }
        $role = get_role('testrole');
        $this->assertTrue($role->has_cap('fb_manage_dashboard'));
    }
}
