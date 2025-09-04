<?php // phpcs:ignoreFile
/**
 * Dashboard CSV export.
 *
 * @package FoodBankManager\Exports
 */

declare(strict_types=1);

namespace FoodBankManager\Exports;

use DateInterval;
use DateTimeImmutable;
use function __;
use function fputcsv;
use function rewind;
use function stream_get_contents;

final class DashboardCsv {
	/**
	 * Render CSV string.
	 *
	 * @param array<string,int>              $totals Aggregated totals.
	 * @param array<int,int>                 $split Daily counts.
	 * @param string                         $period Period key.
	 * @param array{since:DateTimeImmutable} $filters Filters including since.
	 * @return string CSV with BOM.
	 */
	public static function render( array $totals, array $split, string $period, array $filters ): string {
		$fh = fopen( 'php://temp', 'r+' );
		fputcsv( $fh, array( __( 'Metric', 'foodbank-manager' ), __( 'Count', 'foodbank-manager' ) ) );
		foreach ( $totals as $k => $v ) {
			$label = ucwords( str_replace( '_', ' ', $k ) );
			fputcsv( $fh, array( __( $label, 'foodbank-manager' ), (string) (int) $v ) );
		}
		if ( $split ) {
			fputcsv( $fh, array() );
			fputcsv( $fh, array( __( 'Day', 'foodbank-manager' ), __( 'Present', 'foodbank-manager' ) ) );
			$since = $filters['since'];
			for ( $i = 0; $i < count( $split ); $i++ ) {
				$day = $since->add( new DateInterval( 'P' . $i . 'D' ) );
				fputcsv( $fh, array( $day->format( 'Y-m-d' ), (string) $split[ $i ] ) );
			}
		}
		rewind( $fh );
		return "\xEF\xBB\xBF" . (string) stream_get_contents( $fh );
	}
}
