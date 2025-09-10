<?php
declare(strict_types=1);

use FoodBankManager\Core\Options;
use FoodBankManager\UI\Theme;

final class ThemeSaveTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        fbm_grant_admin();
        update_option('fbm_options', Options::defaults());
    }

    public function test_valid_payload_saved(): void {
        $input = [
            'theme' => [
                'admin' => [
                    'style' => 'glass',
                    'preset' => 'dark',
                    'accent' => '#112233',
                    'glass' => ['alpha' => 0.2, 'blur' => 10, 'elev' => 4, 'radius' => 10, 'border' => 2],
                ],
            ],
        ];
        $san = Options::sanitize_all($input);
        update_option('fbm_options', $san);
        $opt = get_option('fbm_options');
        $this->assertSame('#112233', $opt['theme']['admin']['accent']);
    }

    public function test_oversize_payload_rejected(): void {
        $before = get_option('fbm_options');
        $big    = str_repeat('A', 70000);
        $input  = ['theme' => ['admin' => Theme::defaults()['admin'], 'huge' => $big]];
        $san    = Options::sanitize_all($input);
        update_option('fbm_options', $san);
        $after  = get_option('fbm_options');
        $this->assertSame($before, $after);
        $this->assertNotEmpty(settings_errors());
    }

    public function test_match_front_to_self_idempotent(): void {
        $input = [
            'theme' => [
                'admin' => Theme::defaults()['admin'],
                'front' => ['enabled' => true],
                'match_front_to_admin' => true,
            ],
        ];
        $san1 = Options::sanitize_all($input);
        $len1 = strlen(wp_json_encode($san1['theme']));
        $san2 = Options::sanitize_all(['theme' => $san1['theme']]);
        $len2 = strlen(wp_json_encode($san2['theme']));
        $this->assertSame($len1, $len2);
    }
}
