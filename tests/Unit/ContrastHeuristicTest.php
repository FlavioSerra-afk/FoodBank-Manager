<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\UI\Theme;

final class ContrastHeuristicTest extends TestCase
{
    private function luminance(string $hex): float
    {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;
        foreach ([$r, $g, $b] as &$v) {
            $v = ($v <= 0.03928) ? $v / 12.92 : pow(($v + 0.055) / 1.055, 2.4);
        }
        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    private function contrast(string $a, string $b): float
    {
        $l1 = $this->luminance($a);
        $l2 = $this->luminance($b);
        $light = max($l1, $l2);
        $dark  = min($l1, $l2);
        return ($light + 0.05) / ($dark + 0.05);
    }

    public function testButtonContrast(): void
    {
        $vars = Theme::css_vars(Theme::defaults()['admin'], ':root');
        if (preg_match('/--fbm-button-bg:([^;]+)/', $vars, $bg) && preg_match('/--fbm-button-fg:([^;]+)/', $vars, $fg)) {
            $ratio = $this->contrast(trim($bg[1]), trim($fg[1]));
            self::assertGreaterThanOrEqual(4.5, $ratio);
        } else {
            self::markTestSkipped('Tokens missing');
        }
    }
}
