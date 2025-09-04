<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Exports\DashboardCsv;

final class DashboardCsvTest extends TestCase {
    public function testBomAndHeaders(): void {
        $since = new \DateTimeImmutable('2025-09-01');
        $csv = DashboardCsv::render(
            array('present'=>1,'households'=>1,'no_shows'=>0,'in_person'=>1,'delivery'=>0,'voided'=>0),
            array(1),
            '7d',
            array('since'=>$since)
        );
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('Metric', $csv);
    }
}
