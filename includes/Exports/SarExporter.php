<?php
/**
 * Subject Access Request exporter.
 *
 * @package FBM
 */

declare(strict_types=1);

namespace FBM\Exports;

use FoodBankManager\Security\Helpers;
use function sanitize_file_name;
use function wp_json_encode;
use function esc_html;

/**
 * Build SAR exports.
 */
class SarExporter {
	private const CHUNK = 500;

		/**
		 * Stream an export, falling back to HTML when ZipArchive is missing.
		 *
		 * @param array  $subject   Data arrays keyed by 'applications', 'attendance', 'emails'.
		 * @param bool   $masked    Whether to mask sensitive fields.
		 * @param string $base_name Base filename without extension.
		 */
	public static function stream( array $subject, bool $masked, string $base_name ): void {
			$base_name = sanitize_file_name( $base_name );
		if ( class_exists( \ZipArchive::class ) ) {
				$zip      = self::build_zip( $subject, $masked );
				$filename = $base_name . '.zip';
			if ( ! headers_sent() ) {
				header( 'Content-Type: application/zip' );
				header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
			}
			$handle = fopen( $zip, 'rb' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
			if ( $handle ) {
				while ( ! feof( $handle ) ) {
					echo fread( $handle, 8192 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.WP.AlternativeFunctions.file_system_operations_fread
				}
				fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
			}
			if ( file_exists( $zip ) ) {
				wp_delete_file( $zip );
			}
			if ( is_dir( dirname( $zip ) ) ) {
				rmdir( dirname( $zip ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir
			}
		} else {
			if ( ! headers_sent() ) {
					header( 'Content-Type: text/html; charset=utf-8' );
			}
						echo self::render_html( $subject, $masked ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

		/**
		 * Build a SAR ZIP from provided data.
		 *
		 * @param array $subject Data arrays keyed by 'applications', 'attendance', 'emails'.
		 * @param bool  $masked  Whether to mask sensitive fields.
		 * @return string Path to generated ZIP file.
		 */
	public static function build_zip( array $subject, bool $masked ): string {
			$dir = function_exists( 'wp_tempnam' ) ? wp_tempnam( 'fbm_sar_' ) : tempnam( sys_get_temp_dir(), 'fbm_sar_' );
		if ( $dir && file_exists( $dir ) ) {
			wp_delete_file( $dir );
		}
		if ( function_exists( 'wp_mkdir_p' ) ) {
				wp_mkdir_p( $dir );
		} else {
			mkdir( $dir, 0700, true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_mkdir
		}
			$zip_path = $dir . '/export.zip';
			$zip      = new \ZipArchive();
			$zip->open( $zip_path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE );
			$manifest = array();

		foreach ( $subject['applications'] ?? array() as $app ) {
				$data       = self::maybe_mask_application( $app, $masked );
				$fname      = sanitize_file_name( 'application-' . (int) ( $app['id'] ?? 0 ) . '.json' );
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
						$content = '';
						$path    = (string) ( $file['stored_path'] ?? '' );
					if ( '' !== $path && file_exists( $path ) && is_readable( $path ) ) {
						$content = (string) file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
					}
						$zip->addFromString( $orig, $content );
						$manifest[] = array(
							'type' => 'file',
							'file' => $orig,
						);
				}
		}

			self::add_json_chunks( $zip, 'attendance', $subject['attendance'] ?? array(), $manifest );
			self::add_json_chunks( $zip, 'email', $subject['emails'] ?? array(), $manifest );

			$csv = "type,file\n";
		foreach ( $manifest as $line ) {
				$csv .= $line['type'] . ',' . $line['file'] . "\n";
		}
			$zip->addFromString( 'manifest.csv', $csv );

			$readme  = "FoodBank Manager SAR Export\n";
			$readme .= $masked ? "Sensitive fields masked.\n" : "Sensitive fields unmasked.\n";
			$readme .= "Files include applications, attachments, attendance records, and email logs.\n";
			$zip->addFromString( 'README.txt', $readme );

			$zip->close();
			return $zip_path;
	}

		/**
		 * Render HTML fallback when ZipArchive is missing.
		 *
		 * @param array $subject Data arrays keyed by 'applications', 'attendance', 'emails'.
		 * @param bool  $masked  Whether to mask sensitive fields.
		 * @return string HTML output.
		 */
	public static function render_html( array $subject, bool $masked ): string {
			$out  = '<p>' . esc_html( 'ZipArchive not available; displaying SAR data inline.' ) . '</p>';
			$out .= '<p>' . esc_html( $masked ? 'Sensitive fields masked.' : 'Sensitive fields unmasked.' ) . '</p>';
		foreach ( $subject['applications'] ?? array() as $app ) {
						$data = self::maybe_mask_application( $app, $masked );
						unset( $data['files'] );
						$out .= '<h2>' . esc_html( 'Application ' . (int) ( $app['id'] ?? 0 ) ) . '</h2>';
						$out .= '<pre>' . esc_html( wp_json_encode( $data ) ) . '</pre>';
			foreach ( $app['files'] ?? array() as $file ) {
				$orig = sanitize_file_name( (string) ( $file['original_name'] ?? '' ) );
				if ( $masked ) {
						$ext  = pathinfo( $orig, PATHINFO_EXTENSION );
						$orig = substr( hash( 'sha256', $orig ), 0, 8 ) . ( $ext ? '.' . $ext : '' );
				}
				$out .= '<p>' . esc_html( $orig ) . '</p>';
			}
		}
		if ( ! empty( $subject['attendance'] ) ) {
				$out .= '<h2>' . esc_html( 'Attendance' ) . '</h2>';
				$out .= '<pre>' . esc_html( wp_json_encode( $subject['attendance'] ) ) . '</pre>';
		}
		if ( ! empty( $subject['emails'] ) ) {
				$out .= '<h2>' . esc_html( 'Emails' ) . '</h2>';
				$out .= '<pre>' . esc_html( wp_json_encode( $subject['emails'] ) ) . '</pre>';
		}
			return $out;
	}

		/**
		 * Mask application fields when requested.
		 *
		 * @param array $app    Application data.
		 * @param bool  $masked Whether to mask sensitive fields.
		 * @return array
		 */
	private static function maybe_mask_application( array $app, bool $masked ): array {
			$data = $app;
		if ( $masked ) {
			if ( isset( $data['email'] ) ) {
				$data['email'] = Helpers::mask_email( (string) $data['email'] );
			}
			if ( isset( $data['postcode'] ) ) {
					$data['postcode'] = Helpers::mask_postcode( (string) $data['postcode'] );
			}
		}
			return $data;
	}

		/**
		 * Add JSON chunks to ZIP.
		 *
		 * @param \ZipArchive      $zip      ZIP archive instance.
		 * @param string           $prefix   File prefix.
		 * @param array<int,array> $rows     Rows to encode.
		 * @param array<int,array> $manifest Manifest array (by reference).
		 */
	private static function add_json_chunks( \ZipArchive $zip, string $prefix, array $rows, array &$manifest ): void {
			$chunks = array_chunk( $rows, self::CHUNK );
		foreach ( $chunks as $i => $chunk ) {
				$fname      = sanitize_file_name( $prefix . '-' . $i . '.json' );
				$manifest[] = array(
					'type' => $prefix,
					'file' => $fname,
				);
				$zip->addFromString( $fname, wp_json_encode( $chunk ) );
		}
	}
}
