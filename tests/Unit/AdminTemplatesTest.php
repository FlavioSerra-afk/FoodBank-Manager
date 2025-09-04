<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class AdminTemplatesTest extends TestCase {
    /**
     * @return array<int, array{string}>
     */
    public function provider(): array {
        if (! defined('FBM_PATH')) {
            define('FBM_PATH', dirname(__DIR__, 2) . '/');
        }
        $files = glob(FBM_PATH . 'templates/admin/*.php');
        return array_map(static fn($f) => array($f), $files ?: array());
    }

    /**
     * @dataProvider provider
     */
    public function testTemplatesWrapped(string $file): void {
        ob_start();
        include $file;
        $html = trim(ob_get_clean() ?: '');
        $this->assertStringStartsWith('<div class="wrap fbm-admin">', $html);
        $this->assertStringContainsString('</div>', $html);
    }
}
