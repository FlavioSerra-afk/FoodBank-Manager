<?php
declare(strict_types=1);

namespace Tests\Unit\I18n;

final class MakePotSmokeTest extends \BaseTestCase {
    public function testPotExistsAndNotEmpty(): void {
        $path = dirname(__DIR__, 3) . '/languages/foodbank-manager.pot';
        $this->assertFileExists($path);
        $this->assertGreaterThan(0, filesize($path));
    }
}
