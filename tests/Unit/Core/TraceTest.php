<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FBM\Core\Trace;

final class TraceTest extends TestCase {
    public function testCountsIncrement(): void {
        Trace::mark( 'count-test' );
        Trace::mark( 'count-test' );
        $counts = Trace::counts();
        $this->assertSame(2, $counts['count-test'] ?? 0);
    }

    public function testMarkOutputsComment(): void {
        ob_start();
        Trace::mark( 'output-test' );
        $out = (string) ob_get_clean();
        $this->assertSame("\n<!-- fbm-render output-test pass=1 -->\n", $out);
    }
}
