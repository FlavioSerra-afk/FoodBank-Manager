<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ForcedColorsTest extends TestCase
{
    public function testForcedColorsMediaQuery(): void
    {
        $css = file_get_contents(__DIR__ . '/../../assets/css/admin.css');
        self::assertStringContainsString('@media (forced-colors: active)', $css);
    }
}
