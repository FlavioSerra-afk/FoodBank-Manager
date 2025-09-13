<?php
declare(strict_types=1);

namespace Tests\Unit\Meta;

final class PluginHeaderConsistencyTest extends \BaseTestCase {
    public function testVersionsMatch(): void {
        $root = dirname(__DIR__, 3);

        $pluginFile = file_get_contents($root . '/foodbank-manager.php');
        preg_match('/^\s*\* Version:\s*([0-9.]+)/m', (string) $pluginFile, $pluginVersion);
        preg_match('/^\s*\* Stable tag:\s*([0-9.]+)/m', (string) $pluginFile, $headerStable);
        $version = $pluginVersion[1] ?? '';
        $this->assertSame($version, $headerStable[1] ?? '');

        $pluginPhp = file_get_contents($root . '/includes/Core/Plugin.php');
        preg_match("/VERSION = '([^']+)'/", (string) $pluginPhp, $constantVersion);
        $constant = $constantVersion[1] ?? '';

        $composer = json_decode((string) file_get_contents($root . '/composer.json'), true);
        $composerVersion = (string) ($composer['version'] ?? '');

        $readme = file_get_contents($root . '/readme.txt');
        preg_match('/^Stable tag: (.+)$/m', (string) $readme, $readmeStable);
        $readmeTag = trim($readmeStable[1] ?? '');

        $this->assertSame($version, $constant);
        $this->assertSame($version, $composerVersion);
        $this->assertSame($version, $readmeTag);
    }
}
