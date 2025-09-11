<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Admin\DiagnosticsPdf;

final class DiagnosticsPdfTest extends TestCase {
    public function testSanitizeSettings(): void {
        $raw = array(
            'logo' => '5',
            'org_name' => '<b>Org</b>',
            'org_address' => "Street\nCity",
            'primary_color' => '#ff0000',
            'footer_text' => 'Footer',
            'page_size' => 'Letter',
            'orientation' => 'landscape',
        );
        $out = DiagnosticsPdf::sanitize_settings($raw);
        $this->assertSame(5, $out['logo']);
        $this->assertSame('Org', $out['org_name']);
        $this->assertSame("Street\nCity", $out['org_address']);
        $this->assertSame('#ff0000', $out['primary_color']);
        $this->assertSame('Footer', $out['footer_text']);
        $this->assertSame('Letter', $out['page_size']);
        $this->assertSame('landscape', $out['orientation']);
    }
}
