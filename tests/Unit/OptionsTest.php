<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class OptionsTest extends TestCase {
    public function testDefaultsAndSave(): void {
        fbm_test_reset_globals();
        delete_option('fbm_options');
        $this->assertIsArray(\FBM\Core\Options::all()); // includes defaults
        \FBM\Core\Options::save(['theme'=>['primary_color'=>'#000000']]);
        $this->assertSame('#000000', \FBM\Core\Options::get('theme','primary_color'));
    }
}
