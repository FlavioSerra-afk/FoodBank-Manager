<?php
declare(strict_types=1);

use FoodBankManager\UI\Theme;

final class ThemeSaveTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        fbm_grant_admin();
        Theme::defaults();
        update_option('fbm_theme', fbm_theme_defaults());
    }

    public function test_valid_payload_saved(): void {
        $input = [
            'admin' => [
                'style' => 'glass',
                'preset' => 'dark',
                'accent' => '#112233',
                'glass' => ['alpha' => 0.2, 'blur' => 10, 'elev' => 4, 'radius' => 10, 'border' => 2],
            ],
        ];
        $san = Theme::sanitize($input);
        update_option('fbm_theme', $san);
        $opt = get_option('fbm_theme');
        $this->assertSame('#112233', $opt['admin']['accent']);
    }

    public function test_oversize_payload_rejected(): void {
        $before = get_option('fbm_theme');
        $big    = str_repeat('A', 70000);
        $input  = ['admin' => Theme::defaults()['admin'], 'huge' => $big];
        $san    = Theme::sanitize($input);
        update_option('fbm_theme', $san);
        $after  = get_option('fbm_theme');
        $this->assertSame($before, $after);
        $this->assertNotEmpty(settings_errors());
    }

    public function test_match_front_to_self_idempotent(): void {
        $input = [
            'admin' => Theme::defaults()['admin'],
            'front' => ['enabled' => true],
            'match_front_to_admin' => true,
        ];
        $san1 = Theme::sanitize($input);
        $len1 = strlen(wp_json_encode($san1));
        $san2 = Theme::sanitize($san1);
        $len2 = strlen(wp_json_encode($san2));
        $this->assertSame($len1, $len2);
    }
}
