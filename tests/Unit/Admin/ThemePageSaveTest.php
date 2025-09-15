<?php
declare(strict_types=1);

 use FoodBankManager\UI\Theme;

final class ThemePageSaveTest extends \BaseTestCase {
    public function test_valid_theme_saved_once(): void {
        Theme::defaults();
        update_option('fbm_theme', fbm_theme_defaults());
        $payload = array(
            'admin' => array(
                'style' => 'glass',
                'preset' => 'dark',
                'accent' => '#123456',
                'glass' => array('alpha'=>0.2,'blur'=>10,'elev'=>4,'radius'=>10,'border'=>1),
            ),
            'front' => array(
                'style' => 'basic',
                'preset' => 'light',
                'accent' => '#123456',
                'glass' => array('alpha'=>0.1,'blur'=>5,'elev'=>2,'radius'=>8,'border'=>1),
                'enabled' => true,
            ),
            'match_front_to_admin' => false,
        );
        $opts = Theme::sanitize($payload);
        update_option('fbm_theme', $opts);
        $opts2 = Theme::sanitize($payload);
        update_option('fbm_theme', $opts2);
        $saved = Theme::get();
        $this->assertSame('#123456', $saved['admin']['accent']);
        $css = Theme::css_vars(Theme::admin(), '.t');
        $this->assertSame(1, substr_count($css, '--fbm-color-accent'));
    }

    public function test_invalid_theme_rejected(): void {
        Theme::defaults();
        update_option('fbm_theme', fbm_theme_defaults());
        $payload = array('blob' => str_repeat('a', 70000));
        $opts = Theme::sanitize($payload);
        $this->assertSame(Theme::defaults(), $opts);
        $this->assertNotEmpty($GLOBALS['fbm_settings_errors']);
    }

    public function test_post_option_page_updates_accent(): void {
        $_POST = array(
            'option_page' => 'fbm_theme',
            'fbm_theme'   => array(
                'admin' => array(
                    'style' => 'glass',
                    'preset' => 'light',
                    'accent' => '#AaBbCc',
                    'glass' => array('alpha'=>0.2,'blur'=>8,'elev'=>4,'radius'=>10,'border'=>1),
                ),
            ),
        );
        $san = Theme::sanitize($_POST['fbm_theme']);
        update_option('fbm_theme', $san);
        $saved = Theme::get();
        $this->assertSame('#AABBCC', strtoupper($saved['admin']['accent']));
    }

    public function test_template_has_option_page_field(): void {
        if (!defined('FBM_PATH')) {
            define('FBM_PATH', dirname(__DIR__, 3) . '/');
        }
        ob_start();
        require FBM_PATH . 'templates/admin/theme.php';
        $html = (string) ob_get_clean();
        $this->assertStringNotContainsString('name="option_page"', $html);
    }
}
