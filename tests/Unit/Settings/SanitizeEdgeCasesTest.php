<?php
declare(strict_types=1);

namespace Tests\Unit\Settings;

use FBM\Core\Options;

final class SanitizeEdgeCasesTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        $GLOBALS['fbm_settings_errors'] = array();
    }

    public function testInvalidEmailCleared(): void {
        $out = Options::sanitize_all(['emails' => ['from_address' => 'bad', 'reply_to' => 'no']]);
        $this->assertSame('', $out['emails']['from_address']);
        $this->assertSame('', $out['emails']['reply_to']);
        $errs = settings_errors();
        $this->assertNotEmpty($errs);
    }

    public function testOversizedJsonRejected(): void {
        $big = str_repeat('a', 70000);
        $theme = ['admin' => ['style' => 'glass', 'preset' => $big]];
        $out = Options::sanitize_all(['theme' => $theme]);
        $errs = settings_errors();
        $this->assertNotEmpty($errs);
        $defaults = Options::defaults()['theme'];
        $this->assertSame($defaults, $out['theme']);
    }

    public function testStringClamped(): void {
        $long = str_repeat('à', 300);
        $out = Options::sanitize_all(['emails' => ['from_name' => $long]]);
        $this->assertSame(200, mb_strlen($out['emails']['from_name']));
    }
}
