<?php
/**
 * Perf trace tracker tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * @coversNothing
 */
final class RegistrationDebuggerPerfTest extends TestCase {
        public function test_tracker_aggregates_samples(): void {
                $script = <<<'JS'
const path = require('path');
const factory = require(path.resolve(process.cwd(), '../assets/js/registration-debugger-perf.js'));
const tracker = factory.createTracker({ maxSamples: 2 });
tracker.record({ parse: 5, apply: 10 }, { timestamp: 1, groups: 1 });
tracker.record({ parse: 7, apply: 20 }, { timestamp: 2, groups: 2 });
tracker.record({ parse: 3, apply: 30 }, { timestamp: 3, groups: 3 });
const stats = tracker.stats();
const history = tracker.history();
process.stdout.write(JSON.stringify({ stats, history }));
JS;

                $tmp = tempnam(sys_get_temp_dir(), 'fbm-trace');
                if (false === $tmp) {
                        $this->fail('Unable to create temporary file.');
                }

                $script_file = $tmp . '.js';
                file_put_contents($script_file, $script);

                $process = new Process(['node', $script_file], dirname(__DIR__, 1));
                $process->mustRun();

                $output = $process->getOutput();
                $data = json_decode($output, true);

                @unlink($script_file);
                @unlink($tmp);

                $this->assertIsArray($data, 'Node output should decode to array.');
                $this->assertArrayHasKey('stats', $data);
                $this->assertArrayHasKey('history', $data);
                $this->assertArrayHasKey('parse', $data['stats']);
                $this->assertSame(2, $data['stats']['parse']['count']);
                $this->assertEqualsWithDelta(5.0, $data['stats']['parse']['average'], 0.001);
                $this->assertSame(3.0, (float) $data['stats']['parse']['min']);
                $this->assertSame(7.0, (float) $data['stats']['parse']['max']);
                $this->assertCount(2, $data['history']);
                $this->assertSame(2, $data['history'][0]['groups']);
                $this->assertSame(3, $data['history'][1]['groups']);
        }
}
