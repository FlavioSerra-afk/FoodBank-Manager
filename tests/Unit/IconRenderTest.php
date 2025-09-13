<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\UI\Icon;

final class IconRenderTest extends TestCase
{
    public function testRenderDefaults(): void
    {
        $svg = Icon::render('check');
        self::assertStringContainsString('fbm-icons.svg#fbm-check', $svg);
        self::assertStringContainsString('aria-hidden="true"', $svg);
        self::assertStringNotContainsString('fill=', $svg);
    }

    public function testRenderWithAriaLabel(): void
    {
        $svg = Icon::render('info', ['aria-label' => 'Info']);
        self::assertStringContainsString('role="img"', $svg);
        self::assertStringContainsString('aria-label="Info"', $svg);
    }
}
