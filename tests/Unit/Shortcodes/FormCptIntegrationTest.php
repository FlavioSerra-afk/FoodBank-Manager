<?php
declare(strict_types=1);

namespace Tests\Unit\Shortcodes;

use FBM\Shortcodes\FormShortcode;
use FBM\Forms\FormRepo;
use FoodBankManager\Forms\PresetsRepo;

final class FormCptIntegrationTest extends \BaseTestCase {
    public function testRenderFromCpt(): void {
        $id = FormRepo::create([
            'title' => 'CPT Form',
            'schema' => [
                ['type' => 'text', 'label' => 'First'],
            ],
        ]);
        $html = FormShortcode::render(['id' => (string) $id]);
        $this->assertStringContainsString('field_1', $html);
        $this->assertStringContainsString('First', $html);
    }

    public function testPresetFallbackStillWorks(): void {
        PresetsRepo::upsert([
            'meta' => ['name' => 'Preset', 'slug' => 'preset_x'],
            'fields' => [ ['id' => 'email', 'type' => 'email', 'label' => 'Email'] ],
        ]);
        $html = FormShortcode::render(['preset' => 'preset_x']);
        $this->assertStringContainsString('name="email"', $html);
    }
}
