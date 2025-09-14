<?php
declare(strict_types=1);

namespace Tests\Unit\Meta;

final class PluginHeaderConsistencyTest extends \BaseTestCase {
    public function testVersionsAreConsistent(): void {
        $core     = defined('FBM_VER') ? FBM_VER : $this->readVersionFromPluginHeader();
        $header   = $this->readVersionFromPluginHeader();   // foodbank-manager.php header
        $stable   = $this->readStableTagFromReadme();       // readme.txt Stable tag
        $composer = $this->readComposerVersion();           // composer.json version

        $this->assertSame($core, $header,   'Header version mismatch');
        $this->assertSame($core, $stable,   'Readme stable tag mismatch');
        $this->assertSame($core, $composer, 'Composer version mismatch');
    }

    private function readVersionFromPluginHeader(): string {
        $root       = dirname(__DIR__, 3);
        $pluginFile = file_get_contents($root . '/foodbank-manager.php');
        preg_match('/^\s*\*\s*Version:\s*([0-9.]+)/m', (string) $pluginFile, $matches);
        return $matches[1] ?? '';
    }

    private function readStableTagFromReadme(): string {
        $root   = dirname(__DIR__, 3);
        $readme = file_get_contents($root . '/readme.txt');
        preg_match('/^Stable tag:\s*([0-9.]+)/m', (string) $readme, $matches);
        return $matches[1] ?? '';
    }

    private function readComposerVersion(): string {
        $root     = dirname(__DIR__, 3);
        $composer = json_decode((string) file_get_contents($root . '/composer.json'), true);
        return (string) ($composer['version'] ?? '');
    }
}

