<?php
declare(strict_types=1);

use FBM\Shortcodes\FormShortcode;
use FoodBankManager\Forms\PresetsRepo;
use FoodBankManager\Core\Options;

if (!defined('FBM_URL')) {
    define('FBM_URL', '');
}

final class PublicFormsThemeTest extends \BaseTestCase {
    public function test_form_css_and_fallbacks(): void {
        Options::update('theme', array('front' => array('enabled' => true)));
        PresetsRepo::upsert(array('meta' => array('slug' => 't', 'name' => 'T'), 'fields' => array(array('id'=>'f1','label'=>'F1','type'=>'text'))));
        $GLOBALS['fbm_styles'] = array();
        FormShortcode::render(array('preset' => 't'));
        $this->assertArrayHasKey('fbm-public', $GLOBALS['fbm_styles']);
        $count = count($GLOBALS['fbm_styles']);
        FormShortcode::render(array('preset' => 't'));
        $this->assertSame($count, count($GLOBALS['fbm_styles']));
        $css = (string) file_get_contents(__DIR__ . '/../../assets/css/public.css');
        $this->assertStringContainsString('.fbm-form', $css);
        $this->assertStringContainsString('input:focus-visible', $css);
        $this->assertStringContainsString('prefers-reduced-transparency', $css);
    }
}
