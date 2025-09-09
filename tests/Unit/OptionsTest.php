<?php
declare(strict_types=1);

final class OptionsTest extends \BaseTestCase {
    public function testDefaultsAndSave(): void {
        delete_option('fbm_options');
        $this->assertIsArray(\FBM\Core\Options::all()); // includes defaults
        \FBM\Core\Options::save(['theme'=>['primary_color'=>'#000000']]);
        $this->assertSame('#000000', \FBM\Core\Options::get('theme','primary_color'));
    }
}
