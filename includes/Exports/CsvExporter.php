<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Exports;

use FoodBankManager\Security\Helpers;

class CsvExporter {
	/**
	 * @param array<int,array> $rows normalized rows ready to dump
	 * @param bool             $maskPII default true
	 */
	public static function streamList( array $rows, bool $maskPII = true, string $filename = 'fbm-entries.csv' ): void {
		if ( headers_sent() ) {
			return;
		}
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		echo "\xEF\xBB\xBF"; // UTF-8 BOM

		if ( class_exists( '\\League\\Csv\\Writer' ) ) {
			$csv = \League\Csv\Writer::createFromFileObject( new \SplTempFileObject() );
			if ( ! empty( $rows ) ) {
				$csv->insertOne( array_keys( $rows[0] ) );
				foreach ( $rows as $row ) {
					if ( $maskPII ) {
						$row['email']    = Helpers::mask_email( (string) ( $row['email'] ?? '' ) );
						$row['postcode'] = Helpers::mask_postcode( (string) ( $row['postcode'] ?? '' ) );
					}
					$csv->insertOne( $row );
				}
			}
			$csv->output( $filename );
		} else {
			$out = fopen( 'php://output', 'wb' );
			if ( ! empty( $rows ) ) {
				fputcsv( $out, array_keys( $rows[0] ) );
				foreach ( $rows as $row ) {
					if ( $maskPII ) {
						$row['email']    = Helpers::mask_email( (string) ( $row['email'] ?? '' ) );
						$row['postcode'] = Helpers::mask_postcode( (string) ( $row['postcode'] ?? '' ) );
					}
					fputcsv( $out, $row );
				}
			}
			fclose( $out );
		}
	}
}
