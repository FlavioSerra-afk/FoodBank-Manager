<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FBM\Core\RetentionConfig;

final class RetentionConfigTest extends TestCase
{
    public function testNullYieldsDefaults(): void
    {
        $out = RetentionConfig::normalize(null);
        $this->assertSame(['days' => 0, 'policy' => 'delete'], $out['applications']);
    }

    public function testJsonStringIsParsed(): void
    {
        $json = json_encode(['applications' => ['days' => 5, 'policy' => 'anonymise']]);
        $out  = RetentionConfig::normalize($json);
        $this->assertSame(5, $out['applications']['days']);
        $this->assertSame('anonymise', $out['applications']['policy']);
    }

    public function testInvalidPolicyAndNegativeDaysAreCorrected(): void
    {
        $raw = ['attendance' => ['days' => -3, 'policy' => 'purge']];
        $out = RetentionConfig::normalize($raw);
        $this->assertSame(0, $out['attendance']['days']);
        $this->assertSame('delete', $out['attendance']['policy']);
    }

    public function testMailLogKeyIsMapped(): void
    {
        $raw = ['mail_log' => ['days' => 2, 'policy' => 'anonymise']];
        $out = RetentionConfig::normalize($raw);
        $this->assertSame('anonymise', $out['mail']['policy']);
    }
}
