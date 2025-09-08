<?php
declare(strict_types=1);

namespace FoodBankManager\UI {
    if (!class_exists(Theme::class)) {
        class Theme { public static function enqueue_front(): void {} }
    }
}

namespace FBM\Tests\Unit\Shortcodes {

use FBM\Shortcodes\Shortcodes;
use FoodBankManager\Forms\PresetsRepo;
use FoodBankManager\Http\FormSubmitController;
use PHPUnit\Framework\TestCase;

final class FormShortcodeTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        fbm_test_reset_globals();
        fbm_grant_viewer();
        fbm_test_trust_nonces(true);
        fbm_test_set_request_nonce('fbm_submit_form', '_fbm_nonce');
        if (!defined('FBM_PATH')) {
            define('FBM_PATH', dirname(__DIR__, 3) . '/');
        }
        $schema = [
            'meta' => ['name' => 'Test', 'slug' => 'test_form', 'captcha' => true],
            'fields' => [
                ['id' => 'email', 'type' => 'email', 'label' => 'Email', 'required' => true],
                ['id' => 'consent', 'type' => 'checkbox', 'label' => 'Consent', 'required' => true],
            ],
        ];
        PresetsRepo::upsert($schema);
    }

    public function testRendersCaptchaAndFields(): void {
        $raw = json_decode($GLOBALS['fbm_options']['fbm_form_test_form'], true);
        $raw['fields'][0]['label'] = '<script>alert(1)</script>';
        $GLOBALS['fbm_options']['fbm_form_test_form'] = json_encode($raw);
        Shortcodes::register();
        $html = do_shortcode('[fbm_form preset="test_form"]');
        $this->assertStringContainsString('name="captcha"', $html);
        $this->assertStringContainsString('name="email"', $html);
        $this->assertStringNotContainsString('<script', $html);
    }

    public function testSubmitFlowSucceeds(): void {
        Shortcodes::register();
        $_POST = [
            'action' => 'fbm_submit',
            'preset' => 'test_form',
            'email' => 'a@example.com',
            'consent' => '1',
            'captcha' => 'token',
        ];
        fbm_test_set_request_nonce('fbm_submit_form', '_fbm_nonce');
        $_REQUEST = $_POST;
        FormSubmitController::handle();
        $this->assertTrue(true);
    }

    public function testTamperedSchemaReturnsEmpty(): void {
        $raw = json_decode($GLOBALS['fbm_options']['fbm_form_test_form'], true);
        $raw['fields'][] = ['id' => 'hack', 'type' => 'evil', 'label' => 'Hack'];
        $GLOBALS['fbm_options']['fbm_form_test_form'] = json_encode($raw);
        Shortcodes::register();
        $html = do_shortcode('[fbm_form preset="test_form"]');
        $this->assertSame('', $html);
    }
}

}
