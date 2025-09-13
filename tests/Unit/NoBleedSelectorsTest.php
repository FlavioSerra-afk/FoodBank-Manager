<?php
declare(strict_types=1);

use FoodBankManager\UI\Theme;

final class NoBleedSelectorsTest extends \PHPUnit\Framework\TestCase {
    public function test_css_files_scoped(): void {
        $pattern = '/(^|,)\s*(html|body|#wpwrap|#adminmenu|#adminmenuwrap|#screen-meta|#wpbody|#wpcontent|#wpfooter|\.wrap|a|button|input|select|textarea|\.notice)\b/';
        foreach (glob(__DIR__ . '/../../assets/css/*.css') as $file) {
            $lines = file($file, FILE_IGNORE_NEW_LINES);
            foreach ($lines as $line) {
                if (preg_match($pattern, $line) && strpos($line, '.fbm-scope') === false) {
                    $this->fail('Unscoped selector in ' . basename($file) . ': ' . trim($line));
                }
            }
        }
        $this->assertTrue(true);
    }

    public function test_inline_vars_scoped(): void {
        $vars = Theme::css_variables_scoped();
        $this->assertStringNotContainsString(':root', $vars);
        $this->assertStringNotContainsString('body', $vars);
    }
}
