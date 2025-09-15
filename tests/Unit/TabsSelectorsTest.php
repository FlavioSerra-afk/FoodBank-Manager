<?php
use FoodBankManager\UI\Theme;

if (!defined('FBM_PATH')) {
    define('FBM_PATH', __DIR__ . '/../..' . '/');
}
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__);
}

final class TabsSelectorsTest extends \PHPUnit\Framework\TestCase {
    public function test_preview_markup_and_css(): void {
        ob_start();
        include FBM_PATH . 'templates/admin/_preview.php';
        $html = ob_get_clean();
        $this->assertStringContainsString('role="tablist"', $html);
        $this->assertSame(3, substr_count($html, 'role="tab"'));
        $this->assertSame(1, substr_count($html, 'aria-selected="true"'));
        update_option('fbm_theme', fbm_theme_defaults());
        $css = Theme::css_variables_scoped();
        $this->assertStringContainsString('[role="tab"][aria-selected="true"]::after', $css);
        $this->assertStringContainsString('var(--fbm-tabs-indicator-h)', $css);
    }
}
