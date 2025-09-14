<?php
declare(strict_types=1);

use FoodBankManager\UI\Theme;

final class SettingsThemeTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        update_option( 'fbm_theme', function_exists( 'fbm_theme_defaults' ) ? fbm_theme_defaults() : [] );
    }

    public function test_valid_values_emit_tokens(): void {
        $raw = array(
            'admin' => array(
                'style' => 'glass',
                'preset' => 'dark',
                'accent' => '#112233',
                'glass' => array('alpha' => 0.2, 'blur' => 10, 'elev' => 4, 'radius' => 10, 'border' => 2),
            ),
            'front' => array(
                'style' => 'basic',
                'preset' => 'light',
                'accent' => '#112233',
                'glass' => array('alpha' => 0.1, 'blur' => 5, 'elev' => 2, 'radius' => 6, 'border' => 1),
                'enabled' => true,
            ),
            'match_front_to_admin' => false,
        );
        $san = Theme::sanitize($raw);
        $css = Theme::css_vars($san['admin'], '.test');
        $this->assertStringContainsString('--fbm-color-accent:#112233', $css);
        $this->assertStringContainsString('--fbm-glass-blur:10px', $css);
    }

    public function test_invalid_values_clamped(): void {
        $raw = array(
            'admin' => array(
                'style' => 'weird',
                'preset' => 'nope',
                'accent' => 'zzzz',
                'glass' => array('alpha' => 99, 'blur' => 99, 'elev' => 99, 'radius' => 1, 'border' => 9),
            ),
        );
        $san = Theme::sanitize($raw);
        $this->assertSame('glass', $san['admin']['style']);
        $this->assertSame('light', $san['admin']['preset']);
        $this->assertSame('#0B5FFF', $san['admin']['accent']);
        $this->assertSame(12, $san['admin']['glass']['blur']);
        $this->assertSame(24, $san['admin']['glass']['elev']);
        $this->assertSame(6, $san['admin']['glass']['radius']);
        $this->assertSame(2, $san['admin']['glass']['border']);
    }

    public function test_match_front_to_admin_mirrors(): void {
        $raw = array(
            'admin' => array(
                'style' => 'basic',
                'preset' => 'dark',
                'accent' => '#000000',
                'glass' => array('alpha' => 0.3, 'blur' => 5, 'elev' => 2, 'radius' => 8, 'border' => 1),
            ),
            'front' => array(
                'style' => 'glass',
                'preset' => 'light',
                'accent' => '#ffffff',
                'glass' => array('alpha' => 0.1, 'blur' => 4, 'elev' => 1, 'radius' => 6, 'border' => 1),
                'enabled' => true,
            ),
            'match_front_to_admin' => true,
        );
        $san = Theme::sanitize($raw);
        $this->assertTrue($san['match_front_to_admin']);
        $this->assertSame($san['admin']['style'], $san['front']['style']);
        $this->assertSame($san['admin']['preset'], $san['front']['preset']);
        $this->assertSame($san['admin']['accent'], $san['front']['accent']);
    }
}
