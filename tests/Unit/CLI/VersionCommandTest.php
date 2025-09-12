<?php
declare(strict_types=1);

namespace Tests\Unit\CLI;

use FBM\CLI\VersionCommand;
use FBM\CLI\IO;

final class FakeIO implements IO {
    public array $lines = [];
    public array $success = [];
    public array $errors = [];

    public function line(string $message): void { $this->lines[] = $message; }
    public function success(string $message): void { $this->success[] = $message; }
    public function error(string $message): void { $this->errors[] = $message; }
}

final class VersionCommandTest extends \BaseTestCase {
    public function testOutputsVersion(): void {
        $io  = new FakeIO();
        $cmd = new VersionCommand($io);
        $cmd->__invoke([], []);
        $this->assertSame(\FoodBankManager\Core\Plugin::VERSION, $io->lines[0] ?? '');
    }
}
