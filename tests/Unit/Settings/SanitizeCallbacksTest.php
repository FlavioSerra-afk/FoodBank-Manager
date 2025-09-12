<?php
declare(strict_types=1);

namespace Tests\Unit\Settings;

use FBM\Core\Options;
use FoodBankManager\Mail\TemplateRepo;
use FoodBankManager\Admin\PermissionsPage;

final class SanitizeCallbacksTest extends \BaseTestCase {
    public function testTemplateRepoSanitizeLimits(): void {
        $tpl = TemplateRepo::sanitize_all([
            't1' => [
                'subject' => str_repeat('a', 300),
                'body'    => str_repeat('b', 70000),
                'to'      => ['bad', 'ok@example.com'],
            ],
        ]);
        $data = $tpl['t1'];
        $this->assertSame(255, mb_strlen($data['subject']));
        $this->assertLessThanOrEqual(65536, mb_strlen($data['body']));
        $this->assertSame(['ok@example.com'], $data['to']);
    }

    public function testOptionsSanitizeAllRejectsOversizedTheme(): void {
        $GLOBALS['fbm_settings_errors'] = [];
        $big = ['theme' => ['admin' => ['style' => str_repeat('x', 70000)]]];
        Options::sanitize_all($big);
        $this->assertNotEmpty($GLOBALS['fbm_settings_errors']);
    }

    public function testPermissionsSanitizeDefaultsIgnoresInvalid(): void {
        $out = PermissionsPage::sanitize_defaults([
            'badrole'       => ['foo'],
            'administrator' => ['fb_manage_diagnostics', 'unknown'],
        ]);
        $this->assertSame(['administrator' => ['fb_manage_diagnostics']], $out);
    }
}

