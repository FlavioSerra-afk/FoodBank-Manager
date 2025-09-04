<?php
/**
 * Subject Access Request exporter.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Exports;

use FoodBankManager\Security\Helpers;
use function sanitize_file_name;
use function wp_json_encode;

/**
 * Build SAR ZIP exports.
 */
class SarExporter {
	/**
	 * Build a SAR ZIP from provided data.
	 *
	 * @param array $subject Data arrays keyed by 'applications', 'attendance', 'emails'.
	 * @param bool  $masked  Whether to mask sensitive fields.
	 * @return string Path to generated ZIP file.
	 */
	public static function build_zip( array $subject, bool $masked ): string {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		\WP_Filesystem();
		global $wp_filesystem;
		$zip_path = tempnam( sys_get_temp_dir(), 'fbm_sar_' ) . '.zip';
		$zip      = new \ZipArchive();
		$zip->open( $zip_path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE );
		$manifest = array();

		foreach ( $subject['applications'] ?? array() as $app ) {
			$data = $app;
			if ( $masked ) {
				if ( isset( $data['email'] ) ) {
					$data['email'] = Helpers::mask_email( (string) $data['email'] );
				}
				if ( isset( $data['postcode'] ) ) {
					$data['postcode'] = Helpers::mask_postcode( (string) $data['postcode'] );
				}
			}
			$fname      = 'application-' . (int) ( $app['id'] ?? 0 ) . '.json';
			$manifest[] = array(
				'type' => 'application',
				'file' => $fname,
			);
			$zip->addFromString( $fname, wp_json_encode( $data ) );
			foreach ( $app['files'] ?? array() as $file ) {
				$orig = sanitize_file_name( (string) ( $file['original_name'] ?? '' ) );
				if ( $masked ) {
					$ext  = pathinfo( $orig, PATHINFO_EXTENSION );
					$orig = substr( hash( 'sha256', $orig ), 0, 8 ) . ( $ext ? '.' . $ext : '' );
				}
				$content = $wp_filesystem && $wp_filesystem->exists( $file['stored_path'] ?? '' )
					? (string) $wp_filesystem->get_contents( $file['stored_path'] )
					: '';
				$zip->addFromString( $orig, $content );
				$manifest[] = array(
					'type' => 'file',
					'file' => $orig,
				);
			}
		}

		foreach ( $subject['attendance'] ?? array() as $att ) {
			$fname      = 'attendance-' . (int) ( $att['id'] ?? 0 ) . '.json';
			$manifest[] = array(
				'type' => 'attendance',
				'file' => $fname,
			);
			$zip->addFromString( $fname, wp_json_encode( $att ) );
		}
		foreach ( $subject['emails'] ?? array() as $log ) {
			$fname      = 'email-' . (int) ( $log['id'] ?? 0 ) . '.json';
			$manifest[] = array(
				'type' => 'email',
				'file' => $fname,
			);
			$zip->addFromString( $fname, wp_json_encode( $log ) );
		}

		$csv = "type,file\n";
		foreach ( $manifest as $line ) {
			$csv .= $line['type'] . ',' . $line['file'] . "\n";
		}
		$zip->addFromString( 'manifest.csv', $csv );

		$readme  = "FoodBank Manager SAR Export\n";
		$readme .= $masked ? "Sensitive fields masked.\n" : "Sensitive fields unmasked.\n";
		$zip->addFromString( 'README.txt', $readme );

		$zip->close();
		return $zip_path;
	}
}
