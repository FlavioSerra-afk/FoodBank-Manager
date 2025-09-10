<?php
/**
 * Retention runner contract.
 *
 * @package FoodBankManager\Diagnostics
 */

declare(strict_types=1);

namespace FoodBankManager\Diagnostics;

/**
 * Runs retention routines.
 */
interface RetentionRunnerInterface {
    /**
     * Execute retention routines.
     *
     * @param bool $dryRun Whether to simulate.
     * @return array{affected:int,anonymised:int,errors:int,log_id:?string}
     */
    public function run(bool $dryRun = false): array;
}
