<?php
/**
 * Reports data access layer.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Reports;

use DateTimeImmutable;
use FoodBankManager\Core\Install;
use FoodBankManager\Registration\MembersRepository;
use wpdb;
use function array_fill;
use function array_key_exists;
use function array_merge;
use function array_unique;
use function array_values;
use function count;
use function implode;
use function in_array;
use function is_array;
use function is_string;
use function trim;
use function strcmp;
use const ARRAY_A;

/**
 * Retrieves report summaries and paginated attendance records.
 */
final class ReportsRepository {
		/**
		 * WordPress database abstraction.
		 *
		 * @var wpdb
		 */
	private wpdb $wpdb;

		/**
		 * Fully-qualified attendance table name.
		 *
		 * @var string
		 */
	private string $attendance_table;

		/**
		 * Fully-qualified members table name.
		 *
		 * @var string
		 */
	private string $members_table;

	/**
	 * Constructor.
	 *
	 * @param wpdb $wpdb WordPress database abstraction.
	 */
	public function __construct( wpdb $wpdb ) {
			$this->wpdb             = $wpdb;
			$this->attendance_table = Install::attendance_table_name( $wpdb );
			$this->members_table    = Install::members_table_name( $wpdb );
	}

		/**
		 * Summarize attendance records grouped by member status.
		 *
		 * @param DateTimeImmutable   $start   Inclusive range start (UTC).
		 * @param DateTimeImmutable   $end     Inclusive range end (UTC).
		 * @param array<string,mixed> $filters Optional filters.
		 *
		 * @return array{start:string,end:string,total:int,active:int,revoked:int,other:int}
		 */
	public function summarize( DateTimeImmutable $start, DateTimeImmutable $end, array $filters = array() ): array {
		$clauses = $this->build_filters( $filters );

		$args = array_merge(
			array(
				MembersRepository::STATUS_ACTIVE,
				MembersRepository::STATUS_REVOKED,
				$start->format( 'Y-m-d' ),
				$end->format( 'Y-m-d' ),
			),
			$clauses['params']
		);

				$query = sprintf(
					'SELECT COUNT(*) AS total,
SUM(CASE WHEN m.status = %%s THEN 1 ELSE 0 END) AS active_total,
SUM(CASE WHEN m.status = %%s THEN 1 ELSE 0 END) AS revoked_total
FROM `%1$s` a
LEFT JOIN `%2$s` m ON m.member_reference = a.member_reference
WHERE a.collected_date BETWEEN %%s AND %%s%3$s',
					$this->attendance_table,
					$this->members_table,
					$clauses['where']
				);

                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQLPlaceholders.ReplacementsMismatch -- Table names injected safely; filters validated.
				$sql = $this->wpdb->prepare(
					$query, // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared -- Table names injected safely.
					$args
				);

		if ( ! is_string( $sql ) ) {
				return $this->empty_summary( $start, $end );
		}

		/**
		 * Aggregated row from wpdb::get_row().
		 *
		 * @var array<string,mixed>|null $row
		 */
				$row = $this->wpdb->get_row( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Prepared in $sql.

		if ( ! is_array( $row ) ) {
				return $this->empty_summary( $start, $end );
		}

		$total   = isset( $row['total'] ) ? (int) $row['total'] : 0;
		$active  = isset( $row['active_total'] ) ? (int) $row['active_total'] : 0;
		$revoked = isset( $row['revoked_total'] ) ? (int) $row['revoked_total'] : 0;
		$other   = $total - $active - $revoked;

		if ( $other < 0 ) {
				$other = 0;
		}

		return array(
			'start'   => $start->format( 'Y-m-d' ),
			'end'     => $end->format( 'Y-m-d' ),
			'total'   => $total,
			'active'  => $active,
			'revoked' => $revoked,
			'other'   => $other,
		);
	}

		/**
		 * Count attendance rows for the provided range and filters.
		 *
		 * @param DateTimeImmutable   $start   Inclusive range start (UTC).
		 * @param DateTimeImmutable   $end     Inclusive range end (UTC).
		 * @param array<string,mixed> $filters Optional filters.
		 */
	public function count( DateTimeImmutable $start, DateTimeImmutable $end, array $filters = array() ): int {
		$clauses = $this->build_filters( $filters );

		$args = array_merge(
			array(
				$start->format( 'Y-m-d' ),
				$end->format( 'Y-m-d' ),
			),
			$clauses['params']
		);

				$query = sprintf(
					'SELECT COUNT(*)
FROM `%1$s` a
LEFT JOIN `%2$s` m ON m.member_reference = a.member_reference
WHERE a.collected_date BETWEEN %%s AND %%s%3$s',
					$this->attendance_table,
					$this->members_table,
					$clauses['where']
				);

                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQLPlaceholders.ReplacementsMismatch -- Table names injected safely; filters validated.
				$sql = $this->wpdb->prepare(
					$query, // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared -- Table names injected safely.
					$args
				);

		if ( ! is_string( $sql ) ) {
				return 0;
		}

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Prepared above.
				$value = $this->wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Prepared in $sql.

		return (int) $value;
	}

		/**
		 * Retrieve a paginated list of attendance rows.
		 *
		 * @param DateTimeImmutable   $start   Inclusive range start (UTC).
		 * @param DateTimeImmutable   $end     Inclusive range end (UTC).
		 * @param array<string,mixed> $filters Optional filters.
		 * @param int                 $limit   Maximum rows to return.
		 * @param int                 $offset  Offset for pagination.
		 *
		 * @return array<int,array<string,mixed>>
		 */
        public function get_rows( DateTimeImmutable $start, DateTimeImmutable $end, array $filters, int $limit, int $offset ): array {
                $clauses = $this->build_filters( $filters );

                $args = array_merge(
                        array(
                                $start->format( 'Y-m-d' ),
				$end->format( 'Y-m-d' ),
			),
			$clauses['params'],
			array( $limit, $offset )
		);

				$query = sprintf(
                                        'SELECT a.member_reference, a.collected_at, a.collected_date, a.method, a.note, a.recorded_by, m.status, m.first_name, m.last_initial
FROM `%1$s` a
LEFT JOIN `%2$s` m ON m.member_reference = a.member_reference
WHERE a.collected_date BETWEEN %%s AND %%s%3$s
ORDER BY a.collected_at ASC
LIMIT %%d OFFSET %%d',
					$this->attendance_table,
					$this->members_table,
					$clauses['where']
				);

                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQLPlaceholders.ReplacementsMismatch -- Table names injected safely; filters validated.
				$sql = $this->wpdb->prepare(
					$query, // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared -- Table names injected safely.
					$args
				);

		if ( ! is_string( $sql ) ) {
				return array();
		}

		/**
		 * Rows retrieved via wpdb::get_results().
		 *
		 * @var array<int,array<string,mixed>>|null $rows
		 */
				$rows = $this->wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Prepared in $sql.

		if ( ! is_array( $rows ) ) {
				return array();
		}

		$normalized = array();

		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
					continue;
			}

                                        $normalized[] = array(
                                                'member_reference' => isset( $row['member_reference'] ) ? (string) $row['member_reference'] : '',
                                                'collected_at'     => isset( $row['collected_at'] ) ? (string) $row['collected_at'] : '',
                                                'collected_date'   => isset( $row['collected_date'] ) ? (string) $row['collected_date'] : '',
                                                'method'           => isset( $row['method'] ) ? (string) $row['method'] : '',
                                                'note'             => array_key_exists( 'note', $row ) ? ( null !== $row['note'] ? (string) $row['note'] : null ) : null,
                                                'recorded_by'      => array_key_exists( 'recorded_by', $row ) && null !== $row['recorded_by'] ? (int) $row['recorded_by'] : null,
                                                'status'           => isset( $row['status'] ) ? (string) $row['status'] : '',
                                                'first_name'       => isset( $row['first_name'] ) ? (string) $row['first_name'] : '',
                                                'last_initial'     => isset( $row['last_initial'] ) ? (string) $row['last_initial'] : '',
                                        );
                }

                return $normalized;
        }

        /**
         * Retrieve historical attendance rows for a specific member.
         *
         * @param string $member_reference Canonical member reference string.
         * @param int    $limit            Maximum rows to return.
         *
         * @return array{member:array<string,string>|null,rows:array<int,array<string,mixed>>}
         */
        public function get_member_history( string $member_reference, int $limit = 50 ): array {
                $reference = trim( $member_reference );

                if ( '' === $reference ) {
                        return array(
                                'member' => null,
                                'rows'   => array(),
                        );
                }

                $member_sql = $this->wpdb->prepare(
                        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name injected safely.
                        sprintf(
                                'SELECT member_reference, first_name, last_initial, status FROM `%s` WHERE member_reference = %%s LIMIT 1',
                                $this->members_table
                        ),
                        $reference
                );

                $member = null;

                if ( is_string( $member_sql ) ) {
                        /**
                         * @var array<string,mixed>|null $member_row
                         */
                        $member_row = $this->wpdb->get_row( $member_sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Prepared in $member_sql.

                        if ( is_array( $member_row ) ) {
                                $member = array(
                                        'member_reference' => isset( $member_row['member_reference'] ) ? (string) $member_row['member_reference'] : $reference,
                                        'first_name'       => isset( $member_row['first_name'] ) ? (string) $member_row['first_name'] : '',
                                        'last_initial'     => isset( $member_row['last_initial'] ) ? (string) $member_row['last_initial'] : '',
                                        'status'           => isset( $member_row['status'] ) ? (string) $member_row['status'] : '',
                                );
                        }
                }

                if ( $limit <= 0 ) {
                        $limit = 50;
                }

                $history_sql = $this->wpdb->prepare(
                        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name injected safely.
                        sprintf(
                                'SELECT collected_at, collected_date, method, note, recorded_by FROM `%s` WHERE member_reference = %%s ORDER BY collected_at DESC LIMIT %%d',
                                $this->attendance_table
                        ),
                        $reference,
                        $limit
                );

                if ( ! is_string( $history_sql ) ) {
                        return array(
                                'member' => $member,
                                'rows'   => array(),
                        );
                }

                /**
                 * @var array<int,array<string,mixed>>|null $history_rows
                 */
                $history_rows = $this->wpdb->get_results( $history_sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Prepared in $history_sql.

                if ( ! is_array( $history_rows ) ) {
                        return array(
                                'member' => $member,
                                'rows'   => array(),
                        );
                }

                usort(
                        $history_rows,
                        static function ( $a, $b ): int {
                                $left  = isset( $a['collected_at'] ) ? (string) $a['collected_at'] : '';
                                $right = isset( $b['collected_at'] ) ? (string) $b['collected_at'] : '';

                                return strcmp( $right, $left );
                        }
                );

                $normalized = array();

                foreach ( $history_rows as $row ) {
                        if ( ! is_array( $row ) ) {
                                continue;
                        }

                        $normalized[] = array(
                                'collected_at'   => isset( $row['collected_at'] ) ? (string) $row['collected_at'] : '',
                                'collected_date' => isset( $row['collected_date'] ) ? (string) $row['collected_date'] : '',
                                'method'         => isset( $row['method'] ) ? (string) $row['method'] : '',
                                'note'           => array_key_exists( 'note', $row ) ? ( null !== $row['note'] ? (string) $row['note'] : null ) : null,
                                'recorded_by'    => array_key_exists( 'recorded_by', $row ) && null !== $row['recorded_by'] ? (int) $row['recorded_by'] : null,
                        );
                }

                return array(
                        'member' => $member,
                        'rows'   => $normalized,
                );
        }

		/**
		 * Stream rows in batches and invoke the provided callback for each chunk.
		 *
		 * @param DateTimeImmutable   $start     Inclusive range start (UTC).
		 * @param DateTimeImmutable   $end       Inclusive range end (UTC).
		 * @param array<string,mixed> $filters   Optional filters.
		 * @param int                 $batch_size Rows per chunk.
		 * @param callable            $callback  Invoked for each row array.
		 */
	public function stream( DateTimeImmutable $start, DateTimeImmutable $end, array $filters, int $batch_size, callable $callback ): void {
		$limit  = max( 1, $batch_size );
		$offset = 0;

		do {
				$rows = $this->get_rows( $start, $end, $filters, $limit, $offset );

			if ( empty( $rows ) ) {
				break;
			}

			foreach ( $rows as $row ) {
					$callback( $row );
			}

			if ( count( $rows ) < $limit ) {
					break;
			}

				$offset += $limit;
		} while ( true );
	}

		/**
		 * Generate a baseline summary when a query fails.
		 *
		 * @param DateTimeImmutable $start Range start.
		 * @param DateTimeImmutable $end   Range end.
		 *
		 * @return array{start:string,end:string,total:int,active:int,revoked:int,other:int}
		 */
	private function empty_summary( DateTimeImmutable $start, DateTimeImmutable $end ): array {
			return array(
				'start'   => $start->format( 'Y-m-d' ),
				'end'     => $end->format( 'Y-m-d' ),
				'total'   => 0,
				'active'  => 0,
				'revoked' => 0,
				'other'   => 0,
			);
	}

		/**
		 * Build SQL filter fragments based on allow-listed filters.
		 *
		 * @param array<string,mixed> $filters Filters from the request.
		 *
		 * @return array{where:string,params:array<int|string,mixed>}
		 */
	private function build_filters( array $filters ): array {
			$where  = '';
			$params = array();

		if ( isset( $filters['status'] ) ) {
				$statuses = $this->normalize_status_filter( $filters['status'] );

			if ( ! empty( $statuses ) ) {
				$placeholders = implode( ',', array_fill( 0, count( $statuses ), '%s' ) );
				$where       .= " AND m.status IN ( {$placeholders} )"; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Placeholders appended safely.
				$params       = array_merge( $params, $statuses );
			}
		}

			return array(
				'where'  => $where,
				'params' => $params,
			);
	}

		/**
		 * Normalize the member status filter to the allow-listed values.
		 *
		 * @param mixed $value Raw filter value.
		 *
		 * @return array<int,string>
		 */
	private function normalize_status_filter( $value ): array {
			$allowed = array(
				MembersRepository::STATUS_ACTIVE,
				MembersRepository::STATUS_PENDING,
				MembersRepository::STATUS_REVOKED,
			);

			if ( is_string( $value ) ) {
					$value = array( $value );
			}

			if ( ! is_array( $value ) ) {
					return array();
			}

			$statuses = array();

			foreach ( $value as $item ) {
				if ( ! is_string( $item ) ) {
						continue;
				}

					$item = trim( $item );

				if ( '' === $item ) {
						continue;
				}

				if ( in_array( $item, $allowed, true ) ) {
						$statuses[] = $item;
				}
			}

			return array_values( array_unique( $statuses ) );
	}
}
