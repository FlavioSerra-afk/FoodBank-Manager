<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class AccentAndFocusTest extends TestCase
{
    public function testAccentColorAndFocusVisible(): void
    {
        $css = file_get_contents(__DIR__ . '/../../assets/css/admin.css');
        self::assertStringContainsString('accent-color', $css);
        self::assertStringContainsString(':focus-visible', $css);
    }
}
