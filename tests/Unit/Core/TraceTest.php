<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FBM\Core\Trace;

final class TraceTest extends TestCase {
    public function testCountsIncrement(): void {
        \FBM\Core\Trace::mark( 'k' );
        \FBM\Core\Trace::mark( 'k' );
        $counts = \FBM\Core\Trace::counts();
        $this->assertSame(2, $counts['k'] ?? 0);
    }
}
