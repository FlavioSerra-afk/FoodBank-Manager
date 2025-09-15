<?php
declare(strict_types=1);

use FoodBankManager\Admin\ThemePage;
use FoodBankManager\UI\Theme;
use FoodBankManager\Core\Options;

final class ThemeImportExportTest extends \BaseTestCase {
    public function test_import_mirrors_when_match_on(): void {
        if (!defined('FBM_PATH')) {
            define('FBM_PATH', dirname(__DIR__, 3) . '/');
        }
        Options::update('theme', Theme::defaults());
        Options::update('theme', array_replace_recursive(Theme::defaults(), array('match_front_to_admin' => true)));
        update_option('fbm_theme', fbm_theme_defaults());
        $data = array(
            'version' => 1,
            'style'   => 'basic',
            'preset'  => 'dark',
            'accent'  => '#000000',
            'glass'   => array('alpha' => 0.5, 'blur' => 10, 'elev' => 4, 'radius' => 10, 'border' => 2),
        );
        ThemePage::import_json('admin', $data);
        $theme = Theme::get();
        $this->assertSame($theme['admin']['style'], $theme['front']['style']);
        $this->assertSame($theme['admin']['preset'], $theme['front']['preset']);
        $this->assertSame($theme['admin']['accent'], $theme['front']['accent']);
    }

    public function test_export_has_version(): void {
        $out = ThemePage::export_json('admin');
        $this->assertSame(1, $out['version']);
    }
}
