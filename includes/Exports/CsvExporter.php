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

    /**
     * @param array<int,array> $rows
     * @param bool             $maskPII default true
     */
    public static function streamAttendancePeople( array $rows, bool $maskPII = true, bool $includeVoided = false, string $filename = 'fbm-attendance.csv' ): void {
        if ( headers_sent() ) {
            return;
        }
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=' . $filename );
        echo "\xEF\xBB\xBF";

        $header = array( 'Application ID', 'Name', 'Email', 'Postcode', 'Last Attended', 'Visits (Range)', 'No-shows (Range)', 'Visits (12m)', 'Policy' );
        if ($includeVoided) {
            $header[] = 'Voided';
        }
        if ( class_exists( '\\League\\Csv\\Writer' ) ) {
            $csv = \League\Csv\Writer::createFromFileObject( new \SplTempFileObject() );
            if ( ! empty( $rows ) ) {
                $csv->insertOne( $header );
                foreach ( $rows as $row ) {
                    if ( $maskPII ) {
                        $row['email']    = Helpers::mask_email( (string) ( $row['email'] ?? '' ) );
                        $row['postcode'] = Helpers::mask_postcode( (string) ( $row['postcode'] ?? '' ) );
                    }
                    $rowOut = array(
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
                    if ($includeVoided) {
                        $rowOut[] = !empty($row['is_void']) ? 'Yes' : 'No';
                    }
                    $csv->insertOne($rowOut);
                }
            }
            $csv->output( $filename );
        } else {
            $out = fopen( 'php://output', 'wb' );
            if ( ! empty( $rows ) ) {
                fputcsv( $out, $header );
                foreach ( $rows as $row ) {
                    if ( $maskPII ) {
                        $row['email']    = Helpers::mask_email( (string) ( $row['email'] ?? '' ) );
                        $row['postcode'] = Helpers::mask_postcode( (string) ( $row['postcode'] ?? '' ) );
                    }
                    $rowOut = array(
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
                    if ($includeVoided) {
                        $rowOut[] = !empty($row['is_void']) ? 'Yes' : 'No';
                    }
                    fputcsv($out, $rowOut);
                }
            }
            fclose( $out );
        }
    }
}
