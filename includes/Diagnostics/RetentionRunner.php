<?php
/**
 * Default retention runner.
 *
 * @package FoodBankManager\Diagnostics
 */

declare(strict_types=1);

namespace FoodBankManager\Diagnostics;

use FBM\Core\Retention;

/**
 * Wraps core retention routines.
 */
final class RetentionRunner implements RetentionRunnerInterface {
	/**
	 * Execute retention routines.
	 *
	 * @param bool $dryRun Whether to simulate.
	 * @return array{affected:int,anonymised:int,errors:int,log_id:?string}
	 */
	public function run( bool $dryRun = false ): array {
		$summary = $dryRun ? Retention::dry_run() : Retention::run_now();
		return self::summarize( $summary );
	}

	/**
	 * Aggregate detailed summary into flat counts.
	 *
	 * @param array<string,array<string,int>> $summary Detailed summary.
	 * @return array{affected:int,anonymised:int,errors:int,log_id:?string}
	 */
	public static function summarize( array $summary ): array {
		$affected   = 0;
		$anonymised = 0;
		foreach ( $summary as $data ) {
			$deleted     = (int) ( $data['deleted'] ?? 0 );
			$anon        = (int) ( $data['anonymised'] ?? 0 );
			$affected   += $deleted + $anon;
			$anonymised += $anon;
		}
		return array(
			'affected'   => $affected,
			'anonymised' => $anonymised,
			'errors'     => 0,
			'log_id'     => null,
		);
	}
}
