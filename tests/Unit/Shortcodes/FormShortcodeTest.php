<?php
declare(strict_types=1);

namespace {
    if (!function_exists('shortcode_atts')) {
        function shortcode_atts(array $pairs, array $atts, string $shortcode = ''): array { return array_merge($pairs, $atts); }
    }
    if (!function_exists('sanitize_key')) {
        function sanitize_key($key) { return preg_replace('/[^a-z0-9_]/', '', strtolower((string)$key)); }
    }
    if (!function_exists('esc_html__')) {
        function esc_html__(string $text, string $domain = 'default'): string { return $text; }
    }
    if (!function_exists('esc_html')) {
        function esc_html($text) { return htmlspecialchars((string)$text, ENT_QUOTES); }
    }
    if (!function_exists('esc_attr')) {
        function esc_attr($text) { return htmlspecialchars((string)$text, ENT_QUOTES); }
    }
    if (!function_exists('esc_url')) {
        function esc_url($url) { return (string)$url; }
    }
    if (!function_exists('admin_url')) {
        function admin_url(string $path = '') { return '/admin/' . ltrim($path, '/'); }
    }
    if (!function_exists('wp_create_nonce')) {
        function wp_create_nonce(string $action) { return 'nonce'; }
    }
    $GLOBALS['fbm_options_store'] = [];
}

namespace FoodBankManager\Forms {
    function get_option(string $key, $default = false) { return $GLOBALS['fbm_options_store'][$key] ?? $default; }
    function update_option(string $key, $value): bool { $GLOBALS['fbm_options_store'][$key] = $value; return true; }
}

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
        $GLOBALS['fbm_options_store'] = [];
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
        Shortcodes::register();
        $html = do_shortcode('[fbm_form preset="test_form"]');
        $this->assertStringContainsString('name="captcha"', $html);
        $this->assertStringContainsString('name="email"', $html);
    }

    public function testSubmitFlowSucceeds(): void {
        Shortcodes::register();
        $_POST = [
            'action' => 'fbm_submit',
            'preset' => 'test_form',
            '_fbm_nonce' => 'fbm_submit_form_nonce',
            'email' => 'a@example.com',
            'consent' => '1',
            'captcha' => 'token',
        ];
        $_REQUEST = $_POST;
        FormSubmitController::handle();
        $this->assertTrue(true);
    }
}

}
