<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class AdminTemplatesTest extends TestCase
{
    public function testSettingsTemplateWrapped(): void
    {
        $tpl = __DIR__ . '/../../templates/admin/settings.php';
        $buf = file_get_contents($tpl);
        self::assertStringContainsString('class="wrap fbm-admin"', $buf);
    }
}
