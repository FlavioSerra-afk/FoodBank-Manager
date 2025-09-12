<?php
declare(strict_types=1);

namespace Tests\Unit\Exports;

use FBM\Exports\CsvWriter;

final class CsvInjectionGuardTest extends \BaseTestCase {
    public function testNeutralizesDangerousPrefixes(): void {
        $h = fopen('php://temp', 'wb+');
        CsvWriter::put($h, array('=sum(A1:A2)', '+A1', '-A1', '@cmd', "\tfoo", "\rbar", 'ok'));
        rewind($h);
        $row = str_getcsv(trim(stream_get_contents($h)));
        $this->assertSame("'=sum(A1:A2)", $row[0]);
        $this->assertSame("'+A1", $row[1]);
        $this->assertSame("'-A1", $row[2]);
        $this->assertSame("'@cmd", $row[3]);
        $this->assertSame("'\tfoo", $row[4]);
        $this->assertSame("'\rbar", $row[5]);
        $this->assertSame('ok', $row[6]);
    }
}
