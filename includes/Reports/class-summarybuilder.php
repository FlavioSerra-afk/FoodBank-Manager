<?php
/**
 * Report summary and pagination orchestrator.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Reports;

use DateTimeImmutable;
use FoodBankManager\Core\Cache;
use function array_key_exists;
use function array_values;
use function is_array;
use function is_int;
use function is_string;
use function ksort;
use function max;
use function sort;
use function time;
use function trim;

/**
 * Provides cached summaries and paginated record lookups.
 */
final class SummaryBuilder {
	private const SUMMARY_TTL = 120;
	private const TOTAL_TTL   = 300;
	private const PAGE_TTL    = 300;

		/**
		 * Underlying reports repository.
		 *
		 * @var ReportsRepository
		 */
	private ReportsRepository $repository;

	/**
	 * Constructor.
	 *
	 * @param ReportsRepository $repository Data access layer.
	 */
	public function __construct( ReportsRepository $repository ) {
			$this->repository = $repository;
	}

		/**
		 * Retrieve a cached summary for the provided range.
		 *
		 * @param DateTimeImmutable   $start         Inclusive range start (UTC).
		 * @param DateTimeImmutable   $end           Inclusive range end (UTC).
		 * @param array<string,mixed> $filters       Optional filters.
		 * @param bool                $force_refresh When true, bypass the cache.
		 *
		 * @return array{data:array{start:string,end:string,total:int,active:int,revoked:int,other:int},generated_at:int,cache_hit:bool}
		 */
	public function get_summary( DateTimeImmutable $start, DateTimeImmutable $end, array $filters = array(), bool $force_refresh = false ): array {
			$context = $this->context_payload( $start, $end, $filters );
			$key     = Cache::build_key( 'reports', 'summary', $context );

		if ( ! $force_refresh ) {
				$cached = Cache::get( $key );

			if ( $this->is_valid_cached_payload( $cached ) ) {
				return array(
					'data'         => $cached['data'],
					'generated_at' => (int) $cached['generated_at'],
					'cache_hit'    => true,
				);
			}
		}

			$summary = $this->repository->summarize( $start, $end, $filters );
			$payload = array(
				'data'         => $summary,
				'generated_at' => time(),
			);

			Cache::set( $key, $payload, self::SUMMARY_TTL );

			return array(
				'data'         => $summary,
				'generated_at' => (int) $payload['generated_at'],
				'cache_hit'    => false,
			);
	}

		/**
		 * Retrieve a cached total count for the provided range.
		 *
		 * @param DateTimeImmutable   $start         Inclusive range start (UTC).
		 * @param DateTimeImmutable   $end           Inclusive range end (UTC).
		 * @param array<string,mixed> $filters       Optional filters.
		 * @param bool                $force_refresh When true, bypass the cache.
		 *
		 * @return array{total:int,generated_at:int,cache_hit:bool}
		 */
	public function get_total( DateTimeImmutable $start, DateTimeImmutable $end, array $filters = array(), bool $force_refresh = false ): array {
			$context = $this->context_payload( $start, $end, $filters );
			$key     = Cache::build_key( 'reports', 'total', $context );

		if ( ! $force_refresh ) {
				$cached = Cache::get( $key );

			if ( $this->is_valid_total_payload( $cached ) ) {
				return array(
					'total'        => (int) $cached['total'],
					'generated_at' => (int) $cached['generated_at'],
					'cache_hit'    => true,
				);
			}
		}

			$total   = $this->repository->count( $start, $end, $filters );
			$payload = array(
				'total'        => $total,
				'generated_at' => time(),
			);

			Cache::set( $key, $payload, self::TOTAL_TTL );

			return array(
				'total'        => $total,
				'generated_at' => (int) $payload['generated_at'],
				'cache_hit'    => false,
			);
	}

		/**
		 * Retrieve a cached page of attendance rows.
		 *
		 * @param DateTimeImmutable   $start         Inclusive range start (UTC).
		 * @param DateTimeImmutable   $end           Inclusive range end (UTC).
		 * @param array<string,mixed> $filters       Optional filters.
		 * @param int                 $page          1-indexed page number.
		 * @param int                 $per_page      Rows per page.
		 * @param bool                $force_refresh When true, bypass the cache.
		 *
		 * @return array{rows:array<int,array<string,mixed>>,generated_at:int,cache_hit:bool}
		 */
	public function get_rows( DateTimeImmutable $start, DateTimeImmutable $end, array $filters, int $page, int $per_page, bool $force_refresh = false ): array {
			$page     = max( 1, $page );
			$per_page = max( 1, $per_page );
			$offset   = ( $page - 1 ) * $per_page;

			$context             = $this->context_payload( $start, $end, $filters );
			$context['page']     = $page;
			$context['per_page'] = $per_page;
			$context['offset']   = $offset;

			$key = Cache::build_key( 'reports', 'page', $context );

		if ( ! $force_refresh ) {
				$cached = Cache::get( $key );

			if ( $this->is_valid_rows_payload( $cached ) ) {
				return array(
					'rows'         => $cached['rows'],
					'generated_at' => (int) $cached['generated_at'],
					'cache_hit'    => true,
				);
			}
		}

			$rows    = $this->repository->get_rows( $start, $end, $filters, $per_page, $offset );
			$payload = array(
				'rows'         => $rows,
				'generated_at' => time(),
			);

			Cache::set( $key, $payload, self::PAGE_TTL );

			return array(
				'rows'         => $rows,
				'generated_at' => (int) $payload['generated_at'],
				'cache_hit'    => false,
			);
	}

		/**
		 * Normalize filters and range for cache payloads.
		 *
		 * @param DateTimeImmutable   $start   Inclusive range start (UTC).
		 * @param DateTimeImmutable   $end     Inclusive range end (UTC).
		 * @param array<string,mixed> $filters Optional filters.
		 *
		 * @return array<string,mixed>
		 */
	private function context_payload( DateTimeImmutable $start, DateTimeImmutable $end, array $filters ): array {
			$normalized = $this->normalize_filters( $filters );

			return array(
				'start'   => $start->format( 'Y-m-d' ),
				'end'     => $end->format( 'Y-m-d' ),
				'filters' => $normalized,
			);
	}

		/**
		 * Determine if the cached payload contains a summary.
		 *
		 * @param mixed $cached Cached value from {@see Cache::get()}.
		 */
	private function is_valid_cached_payload( $cached ): bool {
			return is_array( $cached )
					&& array_key_exists( 'data', $cached )
					&& array_key_exists( 'generated_at', $cached )
					&& is_int( $cached['generated_at'] );
	}

		/**
		 * Validate a cached total payload.
		 *
		 * @param mixed $cached Cached value from {@see Cache::get()}.
		 */
	private function is_valid_total_payload( $cached ): bool {
			return is_array( $cached )
					&& array_key_exists( 'total', $cached )
					&& array_key_exists( 'generated_at', $cached )
					&& is_int( $cached['generated_at'] );
	}

		/**
		 * Validate a cached rows payload.
		 *
		 * @param mixed $cached Cached value from {@see Cache::get()}.
		 */
	private function is_valid_rows_payload( $cached ): bool {
			return is_array( $cached )
					&& array_key_exists( 'rows', $cached )
					&& array_key_exists( 'generated_at', $cached )
					&& is_int( $cached['generated_at'] );
	}

		/**
		 * Produce a deterministic representation of filters for cache keys.
		 *
		 * @param array<string,mixed> $filters Filters from the request.
		 *
		 * @return array<string,mixed>
		 */
	private function normalize_filters( array $filters ): array {
		if ( empty( $filters ) ) {
				return array();
		}

			ksort( $filters );

		foreach ( $filters as $key => $value ) {
			if ( is_array( $value ) ) {
					sort( $value );
					$filters[ $key ] = array_values( $value );
			} elseif ( is_string( $value ) ) {
					$filters[ $key ] = trim( $value );
			}
		}

			return $filters;
	}
}
