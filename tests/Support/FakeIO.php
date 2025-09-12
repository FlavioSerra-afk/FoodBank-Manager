<?php
declare(strict_types=1);

namespace Tests\Support;

use FBM\CLI\IO;

final class FakeIO implements IO {
    /** @var list<string> */
    public array $lines = [];
    /** @var list<string> */
    public array $success = [];
    /** @var list<string> */
    public array $errors = [];

    public function line(string $message): void { $this->lines[] = $message; }
    public function success(string $message): void { $this->success[] = $message; }
    public function error(string $message): void { $this->errors[] = $message; }
}

