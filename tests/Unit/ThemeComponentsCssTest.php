<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\UI\Theme;

final class ThemeComponentsCssTest extends TestCase
{
    public function testSelectorsAndVariables(): void
    {
        $css  = file_get_contents(__DIR__ . '/../../assets/css/admin.css');
        self::assertStringContainsString('.fbm-btn', $css);
        self::assertStringContainsString('.fbm-input', $css);
        $vars = Theme::css_vars(Theme::defaults()['admin'], ':root');
        self::assertStringContainsString('--fbm-button-bg', $vars);
        self::assertStringContainsString('--fbm-input-bg', $vars);
    }
}
