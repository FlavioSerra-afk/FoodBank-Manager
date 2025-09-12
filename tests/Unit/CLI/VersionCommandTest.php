<?php
declare(strict_types=1);

namespace Tests\Unit\CLI;

require_once __DIR__ . '/../../Support/FakeIO.php';

use FBM\CLI\VersionCommand;
use Tests\Support\FakeIO;

final class VersionCommandTest extends \BaseTestCase {
    public function testOutputsVersion(): void {
        $io  = new FakeIO();
        $cmd = new VersionCommand($io);
        $cmd->__invoke([], []);
        $this->assertSame(\FoodBankManager\Core\Plugin::VERSION, $io->lines[0] ?? '');
    }
}
