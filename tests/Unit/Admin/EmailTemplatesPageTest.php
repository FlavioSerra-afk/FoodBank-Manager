<?php
declare(strict_types=1);

namespace FoodBankManager\Admin {} // for autoload stub

namespace {
use FoodBankManager\Admin\EmailTemplatesPage;
use FoodBankManager\Mail\TemplateRepo;
use Tests\Support\Rbac;

final class EmailTemplatesPageTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        fbm_grant_for_page('fbm_emails');
        if (!defined('FBM_PATH')) { define('FBM_PATH', dirname(__DIR__,2).'/'); }
        if (!defined('ABSPATH')) { define('ABSPATH', __DIR__); }
        update_option('fbm_email_templates', array());
        TemplateRepo::register_setting();
    }

    public function testCapabilityRequired(): void {
        Rbac::revokeAll();
        $this->expectException( \RuntimeException::class );
        EmailTemplatesPage::route();
    }

    public function testSaveTemplate(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        fbm_seed_nonce('unit-seed');
        fbm_test_set_request_nonce('fbm_email_templates_save', '_fbm_nonce');
        $_POST = array(
            'fbm_action' => 'save',
            '_fbm_nonce' => $_POST['_fbm_nonce'],
            'slug'       => 'welcome',
            'subject'    => 'Hi',
            'body'       => '<p>Hello</p>',
            'to'         => array('test@example.com', 'bad'),
        );
        try {
            EmailTemplatesPage::route();
        } catch ( \RuntimeException $e ) {
            $this->assertSame( 'redirect', $e->getMessage() );
        }
        $all = get_option('fbm_email_templates');
        $this->assertSame( 'Hi', $all['welcome']['subject'] );
        $this->assertSame( array('test@example.com'), $all['welcome']['to'] );
    }

    public function testPreviewRequiresNonce(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = array(
            'fbm_action' => 'preview',
            'slug'       => 'welcome',
        );
        $GLOBALS['fbm_test_trust_nonces'] = false;
        $this->expectException( \RuntimeException::class );
        try {
            EmailTemplatesPage::route();
        } finally {
            $GLOBALS['fbm_test_trust_nonces'] = true;
        }
    }

    public function testPreviewRenders(): void {
        TemplateRepo::save('welcome', array('subject' => 'Hello {{first_name}}', 'body' => '<p>Hi {{first_name}}</p>'));
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = array(
            'fbm_action' => 'preview',
            '_fbm_nonce' => 'nonce',
            'slug'       => 'welcome',
            'fbm_ajax'   => '1',
        );
        ob_start();
        try {
            EmailTemplatesPage::route();
        } catch ( \RuntimeException $e ) {
        }
        $out = (string) ob_get_clean();
        $data = json_decode( $out, true );
        $this->assertStringContainsString( 'Test', $data['body'] );
    }
}
}
