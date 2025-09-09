<?php
declare(strict_types=1);

namespace Tests\Unit\Admin;

use FBM\Admin\FormBuilderPage;
use Tests\Support\Rbac;
use BaseTestCase;

final class FormBuilderPageTest extends BaseTestCase {
    public function testRenderDenied(): void {
        Rbac::revokeAll();
        ob_start();
        FormBuilderPage::route();
        $html = (string) ob_get_clean();
        $this->assertStringContainsString('You do not have permission', $html);
    }

    public function testCreatePreviewAndDelete(): void {
        Rbac::grantAdmin();
        fbm_test_set_request_nonce('fbm_form_save', '_fbm_nonce');
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'title' => 'Builder Test',
            'schema_json' => json_encode([['type' => 'text', 'label' => 'Name']]),
        ];
        try {
            FormBuilderPage::route();
        } catch (\Tests\Support\Exceptions\FbmDieException $e) {
        }
        $url = $GLOBALS['__last_redirect'];
        parse_str(parse_url($url, PHP_URL_QUERY), $q);
        $id = (int) ($q['form_id'] ?? 0);

        $_GET = ['form_id' => $id];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        ob_start();
        FormBuilderPage::route();
        $html = (string) ob_get_clean();
        $this->assertStringContainsString('fbm-preview', $html);
        $this->assertStringContainsString('Name', $html);

        fbm_test_set_request_nonce('fbm_form_delete', '_fbm_nonce');
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['form_id' => $id, 'delete_form' => '1'];
        try {
            FormBuilderPage::route();
        } catch (\Tests\Support\Exceptions\FbmDieException $e) {
        }
        $url = $GLOBALS['__last_redirect'];
        parse_str(parse_url($url, PHP_URL_QUERY), $q);
        $this->assertSame('form_deleted', $q['notice'] ?? '');
    }
}
