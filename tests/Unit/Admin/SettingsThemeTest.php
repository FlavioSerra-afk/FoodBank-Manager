<?php

declare(strict_types=1);

use FoodBankManager\Admin\SettingsPage;
use FoodBankManager\Core\Options;
use Tests\Support\Exceptions\FbmDieException;

final class SettingsThemeTest extends BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        fbm_grant_admin();
    }

    public function test_saves_and_renders_theme_options(): void {
        fbm_test_set_request_nonce('fbm_theme_save');
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['_fbm_nonce'] = 'fbm_theme_save';
        $_POST['fbm_action'] = 'theme_save';
        $_POST['theme'] = array(
            'preset' => 'high_contrast<script>',
            'rtl'    => 'force_on<script>',
        );
        $this->expectException(FbmDieException::class);
        SettingsPage::route();

        $opts = Options::all();
        $this->assertSame('high_contrast', $opts['theme']['preset']);
        $this->assertSame('force_on', $opts['theme']['rtl']);

        fbm_test_reset_globals();
        fbm_grant_admin();
        $_GET['tab'] = 'appearance';
        ob_start();
        SettingsPage::route();
        $html = ob_get_clean();
        $this->assertStringContainsString('class="wrap fbm-admin"', $html);
        $this->assertStringContainsString('value="high_contrast" selected', $html);
        $this->assertStringContainsString('value="force_on" selected', $html);
    }

    public function test_invalid_rtl_falls_back_to_auto(): void {
        fbm_test_set_request_nonce('fbm_theme_save');
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['_fbm_nonce'] = 'fbm_theme_save';
        $_POST['fbm_action'] = 'theme_save';
        $_POST['theme'] = array(
            'preset' => 'dark',
            'rtl'    => 'bad',
        );
        $this->expectException(FbmDieException::class);
        SettingsPage::route();
        $opts = Options::all();
        $this->assertSame('dark', $opts['theme']['preset']);
        $this->assertSame('auto', $opts['theme']['rtl']);
    }
}
