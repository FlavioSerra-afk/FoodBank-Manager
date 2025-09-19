<?php
/**
 * Privacy exporter callbacks.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Privacy;

use FoodBankManager\Core\Install;
use wpdb;

use function __;
use function esc_sql;
use function is_array;
use function is_email;
use function sanitize_email;
use function sanitize_text_field;
use function sprintf;
use function trim;
use const ARRAY_A;

/**
 * Provides personal data exports for FoodBank Manager records.
 */
final class Exporter {
	public const ID = 'foodbank-manager';

		/**
		 * Export member and attendance records for the provided identifier.
		 *
		 * @param string $identifier Email address or member reference supplied by WordPress privacy tools.
		 * @param int    $page       Page number (unused, exporter returns all rows in one pass).
		 *
		 * @return array{data:array<int,array<string,mixed>>,done:bool}
		 */
	public static function export( string $identifier, int $page = 1 ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Signature dictated by WordPress.
			unset( $page );

			global $wpdb;

		if ( ! $wpdb instanceof wpdb ) {
				return self::empty_response();
		}

			$member = self::locate_member( $wpdb, $identifier );

		if ( null === $member ) {
				return self::empty_response();
		}

			$data   = array();
			$data[] = array(
				'group_id'    => 'foodbank-manager-member',
				'group_label' => __( 'FoodBank Manager Member', 'foodbank-manager' ),
				'item_id'     => 'fbm-member-' . $member['id'],
				'data'        => self::format_member_fields( $member ),
			);

			$attendance = self::fetch_attendance( $wpdb, $member['member_reference'] );

			if ( ! empty( $attendance ) ) {
				foreach ( $attendance as $index => $record ) {
						$data[] = array(
							'group_id'    => 'foodbank-manager-attendance',
							'group_label' => __( 'FoodBank Manager Attendance', 'foodbank-manager' ),
							'item_id'     => 'fbm-attendance-' . $member['id'] . '-' . $index,
							'data'        => self::format_attendance_fields( $record ),
						);
				}
			}

			$overrides = self::fetch_attendance_overrides( $wpdb, $member['member_reference'] );

			if ( ! empty( $overrides ) ) {
				foreach ( $overrides as $index => $record ) {
						$data[] = array(
							'group_id'    => 'foodbank-manager-attendance-overrides',
							'group_label' => __( 'FoodBank Manager Attendance Overrides', 'foodbank-manager' ),
							'item_id'     => 'fbm-attendance-override-' . $member['id'] . '-' . $index,
							'data'        => self::format_override_fields( $record ),
						);
				}
			}

			return array(
				'data' => $data,
				'done' => true,
			);
	}

		/**
		 * Provide an empty export response when no data exists.
		 *
		 * @return array{data:array<empty>,done:bool}
		 */
	private static function empty_response(): array {
			return array(
				'data' => array(),
				'done' => true,
			);
	}

		/**
		 * Locate a member by email address or reference string.
		 *
		 * @param wpdb   $wpdb       WordPress database abstraction.
		 * @param string $identifier Candidate email or reference.
		 *
		 * @return array{id:int,member_reference:string,first_name:string,last_initial:string,email:string,status:string,household_size:int,created_at:?string,updated_at:?string,activated_at:?string,consent_recorded_at:?string}|null
		 */
	private static function locate_member( wpdb $wpdb, string $identifier ): ?array {
			$members_table = Install::members_table_name( $wpdb );

		$email = sanitize_email( $identifier );

		if ( is_email( $email ) ) {
				$sql = $wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber -- Table name sanitized via esc_sql().
					sprintf(
						'SELECT id, member_reference, first_name, last_initial, email, status, household_size, created_at, updated_at, activated_at, consent_recorded_at FROM `%s` WHERE email = %%s LIMIT 1',
						esc_sql( $members_table )
					),
					$email
				);

			if ( is_string( $sql ) ) {
				/**
				 * Member row.
				 *
				 * @var array<string,mixed>|null $row
				 */
				$row = $wpdb->get_row( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Prepared via $wpdb->prepare().

				if ( is_array( $row ) ) {
						return array(
							'id'                  => isset( $row['id'] ) ? (int) $row['id'] : 0,
							'member_reference'    => isset( $row['member_reference'] ) ? (string) $row['member_reference'] : '',
							'first_name'          => isset( $row['first_name'] ) ? (string) $row['first_name'] : '',
							'last_initial'        => isset( $row['last_initial'] ) ? (string) $row['last_initial'] : '',
							'email'               => isset( $row['email'] ) ? (string) $row['email'] : '',
							'status'              => isset( $row['status'] ) ? (string) $row['status'] : '',
							'household_size'      => isset( $row['household_size'] ) ? (int) $row['household_size'] : 0,
							'created_at'          => isset( $row['created_at'] ) ? (string) $row['created_at'] : null,
							'updated_at'          => isset( $row['updated_at'] ) ? (string) $row['updated_at'] : null,
							'activated_at'        => isset( $row['activated_at'] ) ? (string) $row['activated_at'] : null,
							'consent_recorded_at' => isset( $row['consent_recorded_at'] ) ? (string) $row['consent_recorded_at'] : null,
						);
				}
			}
		}

				$reference = trim( sanitize_text_field( $identifier ) );

		if ( '' === $reference ) {
				return null;
		}

				$sql = $wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber -- Table name sanitized via esc_sql().
					sprintf(
						'SELECT id, member_reference, first_name, last_initial, email, status, household_size, created_at, updated_at, activated_at, consent_recorded_at FROM `%s` WHERE member_reference = %%s LIMIT 1',
						esc_sql( $members_table )
					),
					$reference
				);

		if ( ! is_string( $sql ) ) {
			return null;
		}

		/**
		 * Member row.
		 *
		 * @var array<string,mixed>|null $row
		 */
				$row = $wpdb->get_row( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Prepared via $wpdb->prepare().

		if ( ! is_array( $row ) ) {
			return null;
		}

		return array(
			'id'                  => isset( $row['id'] ) ? (int) $row['id'] : 0,
			'member_reference'    => isset( $row['member_reference'] ) ? (string) $row['member_reference'] : '',
			'first_name'          => isset( $row['first_name'] ) ? (string) $row['first_name'] : '',
			'last_initial'        => isset( $row['last_initial'] ) ? (string) $row['last_initial'] : '',
			'email'               => isset( $row['email'] ) ? (string) $row['email'] : '',
			'status'              => isset( $row['status'] ) ? (string) $row['status'] : '',
			'household_size'      => isset( $row['household_size'] ) ? (int) $row['household_size'] : 0,
			'created_at'          => isset( $row['created_at'] ) ? (string) $row['created_at'] : null,
			'updated_at'          => isset( $row['updated_at'] ) ? (string) $row['updated_at'] : null,
			'activated_at'        => isset( $row['activated_at'] ) ? (string) $row['activated_at'] : null,
			'consent_recorded_at' => isset( $row['consent_recorded_at'] ) ? (string) $row['consent_recorded_at'] : null,
		);
	}

		/**
		 * Format member fields for the privacy exporter payload.
		 *
		 * @param array<string,mixed> $member Member record payload.
		 *
		 * @return array<int,array<string,string>>
		 */
	private static function format_member_fields( array $member ): array {
			return array(
				array(
					'name'  => __( 'Member Reference', 'foodbank-manager' ),
					'value' => (string) $member['member_reference'],
				),
				array(
					'name'  => __( 'Status', 'foodbank-manager' ),
					'value' => (string) $member['status'],
				),
				array(
					'name'  => __( 'First Name', 'foodbank-manager' ),
					'value' => (string) $member['first_name'],
				),
				array(
					'name'  => __( 'Last Initial', 'foodbank-manager' ),
					'value' => (string) $member['last_initial'],
				),
				array(
					'name'  => __( 'Email', 'foodbank-manager' ),
					'value' => (string) $member['email'],
				),
				array(
					'name'  => __( 'Household Size', 'foodbank-manager' ),
					'value' => (string) ( $member['household_size'] ?? 0 ),
				),
				array(
					'name'  => __( 'Created At', 'foodbank-manager' ),
					'value' => (string) ( $member['created_at'] ?? '' ),
				),
				array(
					'name'  => __( 'Updated At', 'foodbank-manager' ),
					'value' => (string) ( $member['updated_at'] ?? '' ),
				),
				array(
					'name'  => __( 'Activated At', 'foodbank-manager' ),
					'value' => (string) ( $member['activated_at'] ?? '' ),
				),
				array(
					'name'  => __( 'Consent Recorded At', 'foodbank-manager' ),
					'value' => (string) ( $member['consent_recorded_at'] ?? '' ),
				),
			);
	}

		/**
		 * Fetch attendance rows for the provided member reference.
		 *
		 * @param wpdb   $wpdb             WordPress database abstraction.
		 * @param string $member_reference Canonical member reference.
		 *
		 * @return array<int,array<string,mixed>>
		 */
	private static function fetch_attendance( wpdb $wpdb, string $member_reference ): array {
			$attendance_table = Install::attendance_table_name( $wpdb );

				$sql = $wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber -- Table name sanitized via esc_sql().
					sprintf(
						'SELECT collected_date, collected_at, method FROM `%s` WHERE member_reference = %%s ORDER BY collected_at ASC',
						esc_sql( $attendance_table )
					),
					$member_reference
				);

		if ( ! is_string( $sql ) ) {
				return array();
		}

			/**
			 * Attendance rows.
			 *
			 * @var array<int,array<string,mixed>>|null $rows
			 */
		$rows = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Prepared in $sql.

		if ( ! is_array( $rows ) ) {
				return array();
		}

			return $rows;
	}

		/**
		 * Format attendance fields for the exporter payload.
		 *
		 * @param array<string,mixed> $record Attendance record.
		 *
		 * @return array<int,array<string,string>>
		 */
	private static function format_attendance_fields( array $record ): array {
			return array(
				array(
					'name'  => __( 'Collection Date', 'foodbank-manager' ),
					'value' => isset( $record['collected_date'] ) ? (string) $record['collected_date'] : '',
				),
				array(
					'name'  => __( 'Collection Time', 'foodbank-manager' ),
					'value' => isset( $record['collected_at'] ) ? (string) $record['collected_at'] : '',
				),
				array(
					'name'  => __( 'Method', 'foodbank-manager' ),
					'value' => isset( $record['method'] ) ? (string) $record['method'] : '',
				),
			);
	}

		/**
		 * Retrieve attendance override audit records for the provided reference.
		 *
		 * @param wpdb   $wpdb             WordPress database abstraction.
		 * @param string $member_reference Canonical member reference.
		 *
		 * @return array<int,array<string,mixed>>
		 */
	private static function fetch_attendance_overrides( wpdb $wpdb, string $member_reference ): array {
			$overrides_table = Install::attendance_overrides_table_name( $wpdb );

				$sql = $wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber -- Table name sanitized via esc_sql().
					sprintf(
						'SELECT attendance_id, override_note, override_at FROM `%s` WHERE member_reference = %%s ORDER BY override_at ASC',
						esc_sql( $overrides_table )
					),
					$member_reference
				);

		if ( ! is_string( $sql ) ) {
				return array();
		}

			/**
			 * Override audit rows.
			 *
			 * @var array<int,array<string,mixed>>|null $rows
			 */
		$rows = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Prepared in $sql.

		if ( ! is_array( $rows ) ) {
				return array();
		}

			return $rows;
	}

		/**
		 * Format attendance override fields for export.
		 *
		 * @param array<string,mixed> $record Override audit row.
		 *
		 * @return array<int,array<string,string>>
		 */
	private static function format_override_fields( array $record ): array {
			return array(
				array(
					'name'  => __( 'Override Note', 'foodbank-manager' ),
					'value' => isset( $record['override_note'] ) ? (string) $record['override_note'] : '',
				),
				array(
					'name'  => __( 'Override Timestamp', 'foodbank-manager' ),
					'value' => isset( $record['override_at'] ) ? (string) $record['override_at'] : '',
				),
				array(
					'name'  => __( 'Attendance Record ID', 'foodbank-manager' ),
					'value' => isset( $record['attendance_id'] ) ? (string) $record['attendance_id'] : '',
				),
			);
	}
}
