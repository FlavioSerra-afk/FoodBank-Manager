<?php
declare(strict_types=1);

use FoodBankManager\Admin\DiagnosticsPage;
use FoodBankManager\Http\DiagnosticsController;
use Tests\Support\Exceptions\FbmDieException;

if (!function_exists('wp_get_phpmailer')) {
    function wp_get_phpmailer() {
        return (object) array(
            'Mailer' => 'smtp',
            'Host' => 'smtp.example.com',
            'Port' => 25,
            'SMTPSecure' => 'tls',
            'SMTPAuth' => true,
        );
    }
}

final class DiagnosticsMailFailuresTest extends \BaseTestCase {
    public function test_list_and_resend(): void {
        fbm_grant_manager();
        if (!defined('ABSPATH')) {
            define('ABSPATH', __DIR__);
        }
        if (!defined('FBM_PATH')) {
            define('FBM_PATH', dirname(__DIR__, 3) . '/');
        }
        $GLOBALS['wpdb'] = (object) array('prefix' => 'wp_');
        \FBM\Mail\LogRepo::$failures = [
            1 => ['id'=>1,'to'=>'a@example.com','subject'=>'Sub1','provider_msg'=>'boom','timestamp'=>'2023-01-01'],
            2 => ['id'=>2,'to'=>'b@example.com','subject'=>'Sub2','provider_msg'=>'nope','timestamp'=>'2023-01-02'],
        ];
        \FBM\Mail\LogRepo::$records = [
            1 => ['id'=>1,'to_email'=>'a@example.com','subject'=>'Sub1','headers'=>''],
        ];
        ob_start();
        DiagnosticsPage::render();
        $html = (string) ob_get_clean();
        $this->assertStringContainsString('Mail Failures (Last 20)', $html);
        $this->assertStringContainsString('boom', $html);

        fbm_seed_nonce('unit-seed');
        $nonce = wp_create_nonce('fbm_mail_resend_1');
        $_GET = array('id'=>1, '_fbm_nonce'=>$nonce);
        $_REQUEST = $_GET;
        try {
            DiagnosticsController::mail_resend();
        } catch (FbmDieException $e) {
        }
        $this->assertStringContainsString('notice=resent', (string) ($GLOBALS['__last_redirect'] ?? ''));
        $this->assertNotEmpty(\FBM\Mail\LogRepo::$appended);
    }

    protected function tearDown(): void {
        wp_clear_scheduled_hook('fbm_retention_hourly');
        remove_all_actions('init');
        remove_all_actions('admin_init');
        parent::tearDown();
    }
}
