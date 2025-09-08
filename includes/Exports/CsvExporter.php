<?php
/**
 * CSV export utilities.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Exports;

use FBM\Exports\CsvWriter;
use FoodBankManager\Security\Helpers;

/**
 * CSV export helpers.
 */
class CsvExporter {
	/**
	 * Stream a CSV list export.
	 *
	 * @param array<int,array> $rows          Rows ready to export.
	 * @param bool             $mask_sensitive Whether to mask sensitive fields.
	 * @param string           $filename       Output filename.
	 */
	public static function stream_list( array $rows, bool $mask_sensitive = true, string $filename = 'fbm-entries.csv' ): void {
                $filename = sanitize_file_name( $filename );
                $headers  = array(
                        'Content-Type: text/csv; charset=UTF-8',
                        'X-Content-Type-Options: nosniff',
                        'Content-Disposition: attachment; filename="' . $filename . '"',
                );
                fbm_send_headers( $headers );
                $out = fopen( 'php://output', 'wb' );
                CsvWriter::writeBom( $out );

                $keys    = array( 'id', 'created_at', 'name', 'email', 'postcode', 'status' );
                $headers = array(
                        __( 'ID', 'foodbank-manager' ),
                        __( 'Created At', 'foodbank-manager' ),
                        __( 'Name', 'foodbank-manager' ),
                        __( 'Email', 'foodbank-manager' ),
                        __( 'Postcode', 'foodbank-manager' ),
                        __( 'Status', 'foodbank-manager' ),
                );
                if ( ! empty( $rows ) ) {
                        CsvWriter::put( $out, $headers, ',', '"', '\\' );
                        foreach ( $rows as $row ) {
                                if ( $mask_sensitive ) {
                                        $row['email']    = Helpers::mask_email( (string) ( $row['email'] ?? '' ) );
                                        $row['postcode'] = Helpers::mask_postcode( (string) ( $row['postcode'] ?? '' ) );
                                }
                                $out_row = array();
                                foreach ( $keys as $k ) {
                                        $out_row[] = $row[ $k ] ?? '';
                                }
                                CsvWriter::put( $out, $out_row, ',', '"', '\\' );
                        }
                }
        }

	/**
	 * Stream attendance people CSV.
	 *
	 * @param array<int,array> $rows           Data rows.
	 * @param bool             $mask_sensitive Whether to mask sensitive fields.
	 * @param bool             $include_voided Include voided flag column.
	 * @param string           $filename       Output filename.
	 */
	public static function stream_attendance_people(
		array $rows,
		bool $mask_sensitive = true,
		bool $include_voided = false,
		string $filename = 'fbm-attendance.csv'
	): void {
                $filename = sanitize_file_name( $filename );
                $headers  = array(
                        'Content-Type: text/csv; charset=UTF-8',
                        'X-Content-Type-Options: nosniff',
                        'Content-Disposition: attachment; filename="' . $filename . '"',
                );
                fbm_send_headers( $headers );
                $out = fopen( 'php://output', 'wb' );
                CsvWriter::writeBom( $out );

                $header = array(
                        __( 'Application ID', 'foodbank-manager' ),
                        __( 'Name', 'foodbank-manager' ),
                        __( 'Email', 'foodbank-manager' ),
                        __( 'Postcode', 'foodbank-manager' ),
                        __( 'Last Attended', 'foodbank-manager' ),
			__( 'Visits (Range)', 'foodbank-manager' ),
			__( 'No-shows (Range)', 'foodbank-manager' ),
			__( 'Visits (12m)', 'foodbank-manager' ),
			__( 'Policy', 'foodbank-manager' ),
		);
                if ( $include_voided ) {
                        $header[] = __( 'Voided', 'foodbank-manager' );
                }
                if ( ! empty( $rows ) ) {
                        CsvWriter::put( $out, $header, ',', '"', '\\' );
                        foreach ( $rows as $row ) {
                                if ( $mask_sensitive ) {
                                        $row['email']    = Helpers::mask_email( (string) ( $row['email'] ?? '' ) );
                                        $row['postcode'] = Helpers::mask_postcode( (string) ( $row['postcode'] ?? '' ) );
                                }
                                $row_out = array(
                                        $row['application_id'] ?? '',
                                        $row['name'] ?? '',
                                        $row['email'] ?? '',
                                        $row['postcode'] ?? '',
                                        $row['last_attended'] ?? '',
                                        $row['visits_range'] ?? '',
                                        $row['noshows_range'] ?? '',
                                        $row['visits_12m'] ?? '',
                                        $row['policy_badge'] ?? '',
                                );
                                if ( $include_voided ) {
                                        $row_out[] = ! empty( $row['is_void'] ) ? __( 'Yes', 'foodbank-manager' ) : __( 'No', 'foodbank-manager' );
                                }
                                CsvWriter::put( $out, $row_out, ',', '"', '\\' );
                        }
                }
        }
}
