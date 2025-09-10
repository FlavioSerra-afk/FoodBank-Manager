<?php
declare(strict_types=1);

use FoodBankManager\UI\Theme;

final class ThemeJsonTest extends \BaseTestCase {
    /**
     * @dataProvider presetFiles
     */
    public function test_presets_sanitize(string $file): void {
        if (!defined('FBM_PATH')) {
            define('FBM_PATH', dirname(__DIR__, 3) . '/');
        }
        $json = file_get_contents(FBM_PATH . 'themes/' . $file);
        $data = json_decode((string) $json, true);
        $san  = Theme::sanitize(array('admin' => $data));
        $admin = $san['admin'];
        $this->assertSame($data['style'], $admin['style']);
        $this->assertSame($data['preset'], $admin['preset']);
        $this->assertSame('#3B82F6', strtoupper($admin['accent']));
        if ('high_contrast' === $data['preset'] || 'basic' === $data['style']) {
            $this->assertSame(0.0, $admin['glass']['alpha']);
            $this->assertSame(0, $admin['glass']['blur']);
        }
        $this->assertGreaterThanOrEqual(6, $admin['glass']['radius']);
        $this->assertLessThanOrEqual(20, $admin['glass']['radius']);
    }

    /** @return array<int,array{string}> */
    public function presetFiles(): array {
        if (!defined('FBM_PATH')) {
            define('FBM_PATH', dirname(__DIR__, 3) . '/');
        }
        $files = glob(FBM_PATH . 'themes/*.json');
        $out = array();
        foreach ($files as $file) {
            if (basename($file) === 'schema.json') {
                continue;
            }
            $out[] = array(basename($file));
        }
        return $out;
    }
}
