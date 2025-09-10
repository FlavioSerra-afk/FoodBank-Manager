<?php
declare(strict_types=1);

namespace FoodBankManager\Diagnostics;

use PHPUnit\Framework\TestCase;

final class RetentionRunnerTest extends TestCase {
    public function testSummarizeAggregates(): void {
        $out = RetentionRunner::summarize(
            array(
                'one' => array('deleted' => 1, 'anonymised' => 2),
                'two' => array('deleted' => 3, 'anonymised' => 4),
            )
        );
        $this->assertSame(
            array('affected' => 10, 'anonymised' => 6, 'errors' => 0, 'log_id' => null),
            $out
        );
    }
}
