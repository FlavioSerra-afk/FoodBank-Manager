<?php
/**
 * CSV export streaming utility.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Reports;

use DateTimeImmutable;
use function __;
use function array_key_exists;
use function call_user_func;
use function defined;
use function esc_html__;
use function fflush;
use function flush;
use function fputcsv;
use function function_exists;
use function header;
use function sanitize_file_name;
use function sprintf;
use function substr;
use function strtolower;
use function trim;
use function wp_die;

/**
 * Streams attendance CSV exports without loading the full dataset into memory.
 */
final class CsvExporter {
		/**
		 * Underlying reports repository.
		 *
		 * @var ReportsRepository
		 */
	private ReportsRepository $repository;

		/**
		 * Header emitter callable.
		 *
		 * @var callable
		 */
		private $header_emitter;

		/**
		 * Stream URI used for writing CSV output.
		 *
		 * @var string
		 */
	private string $stream_uri;

		/**
		 * Number of rows per streamed batch.
		 *
		 * @var int
		 */
	private int $batch_size;

		/**
		 * Constructor.
		 *
		 * @param ReportsRepository $repository     Data access layer for reports.
		 * @param callable|null     $header_emitter Optional header emitter override (defaults to {@see header()}).
		 * @param string            $stream_uri     Stream URI for writing (defaults to php://output).
		 * @param int               $batch_size     Chunk size for streaming rows.
		 */
	public function __construct( ReportsRepository $repository, ?callable $header_emitter = null, string $stream_uri = 'php://output', int $batch_size = 200 ) {
			$this->repository = $repository;

		if ( null === $header_emitter ) {
				$header_emitter = static function ( string $value ): void {
								header( $value );
				};
		}

			$this->header_emitter = $header_emitter;
			$this->stream_uri     = $stream_uri;
			$this->batch_size     = max( 1, $batch_size );
	}

		/**
		 * Stream the CSV export for the provided range and filters.
		 *
		 * @param DateTimeImmutable   $start    Inclusive range start (UTC).
		 * @param DateTimeImmutable   $end      Inclusive range end (UTC).
		 * @param array<string,mixed> $filters  Optional filters.
		 * @param string              $filename Suggested filename (without extension).
		 */
	public function stream( DateTimeImmutable $start, DateTimeImmutable $end, array $filters, string $filename ): void {
			$sanitized = sanitize_file_name( trim( $filename ) );

		if ( '' === $sanitized ) {
				$sanitized = 'fbm-attendance';
		}

		if ( strtolower( substr( $sanitized, -4 ) ) !== '.csv' ) {
				$sanitized .= '.csv';
		}

			$this->emit_headers( $sanitized );

				$handle = fopen( $this->stream_uri, 'wb' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- Streaming export requires direct handle.

		if ( false === $handle ) {
				wp_die( esc_html__( 'Unable to open export stream.', 'foodbank-manager' ) );
		}

				fwrite( $handle, "\xEF\xBB\xBF" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- Streaming export requires direct handle.

                        fputcsv(
                                $handle,
                                array(
                                        __( 'Collected Date', 'foodbank-manager' ),
                                        __( 'Collected Time', 'foodbank-manager' ),
                                        __( 'Member Reference', 'foodbank-manager' ),
                                        __( 'Member Status', 'foodbank-manager' ),
                                        __( 'Method', 'foodbank-manager' ),
                                        __( 'Note', 'foodbank-manager' ),
                                        __( 'Recorded By', 'foodbank-manager' ),
                                ),
                                ',',
                                '"',
                                '\\'
                        );

			$this->repository->stream(
				$start,
				$end,
				$filters,
				$this->batch_size,
				function ( array $row ) use ( $handle ): void {
						$date   = isset( $row['collected_date'] ) ? (string) $row['collected_date'] : '';
						$time   = isset( $row['collected_at'] ) ? (string) $row['collected_at'] : '';
						$ref    = isset( $row['member_reference'] ) ? (string) $row['member_reference'] : '';
						$status = isset( $row['status'] ) ? (string) $row['status'] : '';
						$method = isset( $row['method'] ) ? (string) $row['method'] : '';
						$note   = array_key_exists( 'note', $row ) && null !== $row['note'] ? (string) $row['note'] : '';
						$user   = array_key_exists( 'recorded_by', $row ) && null !== $row['recorded_by'] ? (string) $row['recorded_by'] : '';

                                                                fputcsv( $handle, array( $date, $time, $ref, $status, $method, $note, $user ), ',', '"', '\\' );

								fflush( $handle );

					if ( function_exists( 'flush' ) ) {
							flush();
					}
				}
			);

				fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Streaming export requires direct handle.

		if ( ! defined( 'FBM_TESTING' ) || ! FBM_TESTING ) {
				exit;
		}
	}

		/**
		 * Emit HTTP headers for the CSV download.
		 *
		 * @param string $filename Sanitized filename including extension.
		 */
	private function emit_headers( string $filename ): void {
			$emitter = $this->header_emitter;

			call_user_func( $emitter, 'Content-Type: text/csv; charset=UTF-8' );
			call_user_func( $emitter, sprintf( 'Content-Disposition: attachment; filename="%s"', $filename ) );
			call_user_func( $emitter, 'Pragma: no-cache' );
			call_user_func( $emitter, 'Expires: 0' );
	}
}
