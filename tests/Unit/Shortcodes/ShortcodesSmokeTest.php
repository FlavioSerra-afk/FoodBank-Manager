<?php
declare(strict_types=1);

namespace FBM\Tests\Unit\Shortcodes;

use FBM\Shortcodes\FormShortcode;
use FoodBankManager\Forms\PresetsRepo;

final class ShortcodesSmokeTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        if ( ! defined( 'FBM_PATH' ) ) {
            define( 'FBM_PATH', dirname( __DIR__, 3 ) . '/' );
        }
        $GLOBALS['fbm_options'] = array( 'fbm_options' => array( 'theme' => array( 'front' => array( 'enabled' => true ) ) ) );
        $schema = array(
            'meta'   => array( 'name' => 'Smoke', 'slug' => 'smoke_form', 'captcha' => false ),
            'fields' => array(
                array( 'id' => 'field1', 'type' => 'text', 'label' => 'Field', 'required' => false ),
            ),
        );
        PresetsRepo::upsert( $schema );
    }

    public function test_wrapper_enqueues_css(): void {
        $GLOBALS['fbm_styles'] = array();
        $html                  = FormShortcode::render( array( 'preset' => 'smoke_form' ) );
        $this->assertStringContainsString( 'fbm-public', $html );
        $this->assertArrayHasKey( 'fbm-public', $GLOBALS['fbm_styles'] );
    }
}
