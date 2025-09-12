<?php
declare(strict_types=1);

namespace Tests\Unit\CLI;

require_once __DIR__ . '/../../Support/FakeIO.php';

use FoodBankManager\CLI\Commands;
use FoodBankManager\Diagnostics\RetentionRunnerInterface;
use Tests\Support\FakeIO;

use function add_filter;
use function delete_transient;
use function remove_all_filters;
use function set_transient;

final class RetentionCommandTest extends \BaseTestCase {
    private function runner(): RetentionRunnerInterface {
        return new class implements RetentionRunnerInterface {
            public array $runs = [];
            public function run(bool $dryRun = false): array {
                $this->runs[] = $dryRun;
                return [ 'affected' => 1, 'anonymised' => 0, 'errors' => 0, 'log_id' => null ];
            }
        };
    }

    public function testDryRunAndRealRun(): void {
        $fake = $this->runner();
        add_filter('fbm_retention_runner', fn() => $fake);
        $io  = new FakeIO();
        $cmd = new Commands($io);
        $cmd->retention_run([], ['dry-run' => true]);
        $cmd->retention_run([], []);
        remove_all_filters('fbm_retention_runner');
        $this->assertSame([true, false], $fake->runs);
        $this->assertStringContainsString('affected=1', $io->lines[0] ?? '');
    }

    public function testLockRespected(): void {
        set_transient('fbm_retention_lock', 1, 300);
        $io  = new FakeIO();
        $cmd = new Commands($io);
        $cmd->retention_run([], []);
        delete_transient('fbm_retention_lock');
        $this->assertNotEmpty($io->errors);
    }
}

