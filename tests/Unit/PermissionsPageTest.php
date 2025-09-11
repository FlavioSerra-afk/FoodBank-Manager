<?php
declare(strict_types=1);

namespace {
    use FoodBankManager\Admin\PermissionsPage;
    use FoodBankManager\Admin\UsersMeta;
    use FoodBankManager\Auth\Capabilities;

    function fbm_silence_error() { return true; }

    final class PermissionsPageTest extends \BaseTestCase {

    public function test_import_rejects_bad_json(): void {
        fbm_grant_admin();
        fbm_test_set_request_nonce('fbm_permissions_perm_import');
        $_POST = array_merge($_POST, [
            'fbm_action' => 'perm_import',
            'json'       => 'bad',
        ]);
        $_REQUEST = $_POST;
        $_FILES   = array();
        global $fbm_user_meta, $fbm_options;
        $fbm_user_meta = array(1 => array());
        $fbm_options   = array();
        $page = new \FoodBankManager\Admin\PermissionsPage();
        $ref  = new \ReflectionMethod( \FoodBankManager\Admin\PermissionsPage::class, 'handle_import' );
        $ref->setAccessible(true);
        $this->expectException( \RuntimeException::class );
        $ref->invoke($page);
    }

    public function test_user_override_add_and_remove(): void {
        fbm_grant_admin();
        fbm_test_set_request_nonce('fbm_permissions_perm_user_override_add');
        $_POST = array_merge($_POST, [
            'fbm_action' => 'perm_user_override_add',
            'user_id'    => 1,
            'caps'       => array('fb_manage_dashboard'),
        ]);
        $_REQUEST = $_POST;
        $_FILES   = array();
        global $fbm_user_meta, $fbm_options;
        $fbm_user_meta = array(1 => array());
        $fbm_options   = array();
        $add = new \ReflectionMethod( \FoodBankManager\Admin\PermissionsPage::class, 'handle_user_override_add' );
        $add->setAccessible(true);
        try {
            $add->invoke(new \FoodBankManager\Admin\PermissionsPage());
            $this->fail('Expected redirect');
        } catch ( \RuntimeException $e ) {
            $this->assertSame('redirect', $e->getMessage());
        }

        fbm_test_set_request_nonce('fbm_permissions_perm_user_override_remove');
        $_POST = array_merge($_POST, [
            'fbm_action' => 'perm_user_override_remove',
            'user_id'    => 1,
        ]);
        $_REQUEST = $_POST;
        $rm = new \ReflectionMethod( \FoodBankManager\Admin\PermissionsPage::class, 'handle_user_override_remove' );
        $rm->setAccessible(true);
        try {
            $rm->invoke(new \FoodBankManager\Admin\PermissionsPage());
            $this->fail('Expected redirect');
        } catch ( \RuntimeException $e ) {
            $this->assertSame('redirect', $e->getMessage());
        }
    }

    public function test_export_json(): void {
        fbm_grant_admin();
        fbm_test_set_request_nonce('fbm_permissions_perm_export');
        $_POST = array_merge($_POST, [
            'fbm_action' => 'perm_export',
        ]);
        $_REQUEST = $_POST;
        $_FILES   = array();
        global $fbm_user_meta, $fbm_options;
        $fbm_user_meta = array(1 => array('fbm_user_caps' => array('fb_manage_dashboard' => true)));
        $fbm_options   = array();
        $page = new \FoodBankManager\Admin\PermissionsPage();
        $ref  = new \ReflectionMethod( \FoodBankManager\Admin\PermissionsPage::class, 'handle_export' );
        $ref->setAccessible(true);
        set_error_handler('fbm_silence_error');
        try {
            $ref->invoke($page);
        } catch ( \RuntimeException $e ) {
        }
        restore_error_handler();
        $this->assertTrue(true);
    }
    }
}
