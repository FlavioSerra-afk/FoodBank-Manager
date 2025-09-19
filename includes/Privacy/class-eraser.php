<?php
/**
 * Privacy eraser callbacks.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Privacy;

use FoodBankManager\Core\Install;
use FoodBankManager\Diagnostics\MailFailureLog;
use FoodBankManager\Registration\MembersRepository;
use wpdb;

use function __;
use function esc_sql;
use function gmdate;
use function is_array;
use function is_email;
use function sanitize_email;
use function sanitize_text_field;
use function sprintf;
use function count;
use function get_option;
use function update_option;
use function str_starts_with;
use function trim;

use const ARRAY_A;

/**
 * Provides eraser callbacks for FoodBank Manager data.
 */
final class Eraser {
	public const ID = 'foodbank-manager';

		/**
		 * Erase personal data for the provided email address or member reference.
		 *
		 * @param string $identifier Email address or member reference supplied by WordPress privacy tools.
		 * @param int    $page       Batch number (unused, eraser completes in a single pass).
		 *
		 * @return array{items_removed:bool,items_retained:bool,messages:array<int,string>,done:bool}
		 */
	public static function erase( string $identifier, int $page = 1 ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Required by WordPress.
			unset( $page );

			global $wpdb;

		if ( ! $wpdb instanceof wpdb ) {
				return self::nothing_to_do();
		}

			$member = self::locate_member( $wpdb, $identifier );

		if ( null === $member ) {
				return self::nothing_to_do();
		}

			$messages      = array();
			$items_removed = false;

			$new_reference = self::sanitized_reference( $member['id'] );

		if ( self::anonymize_member( $wpdb, $member, $new_reference ) ) {
				$items_removed = true;
				$messages[]    = __( 'FoodBank Manager member profile anonymized.', 'foodbank-manager' );
		}

		if ( self::scrub_attendance( $wpdb, $member['member_reference'], $new_reference ) ) {
				$items_removed = true;
				$messages[]    = __( 'Attendance history detached and notes cleared.', 'foodbank-manager' );
		}

		if ( self::scrub_attendance_overrides( $wpdb, $member['member_reference'], $new_reference ) ) {
				$items_removed = true;
				$messages[]    = __( 'Attendance override audit entries anonymized.', 'foodbank-manager' );
		}

		if ( self::remove_tokens( $wpdb, $member['id'] ) ) {
				$items_removed = true;
				$messages[]    = __( 'Authentication tokens revoked.', 'foodbank-manager' );
		}

		if ( self::scrub_audit_log( $member['id'] ) ) {
				$items_removed = true;
		}

			self::resolve_mail_failures( $member['id'] );

			return array(
				'items_removed'  => $items_removed,
				'items_retained' => false,
				'messages'       => $messages,
				'done'           => true,
			);
	}

		/**
		 * Provide a default eraser response when no action is required.
		 *
		 * @return array{items_removed:bool,items_retained:bool,messages:array<int,string>,done:bool}
		 */
	private static function nothing_to_do(): array {
			return array(
				'items_removed'  => false,
				'items_retained' => false,
				'messages'       => array(),
				'done'           => true,
			);
	}

		/**
		 * Locate a member record by email address or member reference.
		 *
		 * @param wpdb   $wpdb       WordPress database abstraction.
		 * @param string $identifier Email address or reference string.
		 *
		 * @return array{id:int,member_reference:string,email:string}|null
		 */
	private static function locate_member( wpdb $wpdb, string $identifier ): ?array {
			$members_table = Install::members_table_name( $wpdb );

		$email = sanitize_email( $identifier );

		if ( is_email( $email ) ) {
				$sql = $wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber -- Table name sanitized via esc_sql().
					sprintf(
						'SELECT id, member_reference, email FROM `%s` WHERE email = %%s LIMIT 1',
						esc_sql( $members_table )
					),
					$email
				);

			if ( is_string( $sql ) ) {
				/**
				 * Member row payload.
				 *
				 * @var array<string,mixed>|null $row
				 */
				$row = $wpdb->get_row( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Prepared via $wpdb->prepare().

				if ( is_array( $row ) ) {
						return array(
							'id'               => isset( $row['id'] ) ? (int) $row['id'] : 0,
							'member_reference' => isset( $row['member_reference'] ) ? (string) $row['member_reference'] : '',
							'email'            => isset( $row['email'] ) ? (string) $row['email'] : '',
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
						'SELECT id, member_reference, email FROM `%s` WHERE member_reference = %%s LIMIT 1',
						esc_sql( $members_table )
					),
					$reference
				);

		if ( ! is_string( $sql ) ) {
				return null;
		}

				/**
				 * Member row payload.
				 *
				 * @var array<string,mixed>|null $row
				 */
				$row = $wpdb->get_row( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Prepared via $wpdb->prepare().

		if ( ! is_array( $row ) ) {
				return null;
		}

				return array(
					'id'               => isset( $row['id'] ) ? (int) $row['id'] : 0,
					'member_reference' => isset( $row['member_reference'] ) ? (string) $row['member_reference'] : '',
					'email'            => isset( $row['email'] ) ? (string) $row['email'] : '',
				);
	}

		/**
		 * Compose an anonymized member reference for erased accounts.
		 *
		 * @param int $member_id Member identifier.
		 */
	private static function sanitized_reference( int $member_id ): string {
					return 'erased-' . $member_id;
	}

		/**
		 * Replace personally identifiable fields on the member record.
		 *
		 * @param wpdb                $wpdb          WordPress database abstraction.
		 * @param array<string,mixed> $member        Existing member record.
		 * @param string              $new_reference Replacement member reference.
		 */
	private static function anonymize_member( wpdb $wpdb, array $member, string $new_reference ): bool {
			$members_table = Install::members_table_name( $wpdb );

		if ( str_starts_with( (string) $member['member_reference'], 'erased-' ) ) {
				// Already anonymized, skip additional writes.
				return false;
		}

			$now = gmdate( 'Y-m-d H:i:s' );

			$placeholder_email = sprintf( 'deleted-member-%d@example.invalid', (int) $member['id'] );

			$data = array(
				'member_reference' => $new_reference,
				'first_name'       => 'Erased',
				'last_initial'     => 'X',
				'email'            => $placeholder_email,
				'household_size'   => 0,
				'status'           => MembersRepository::STATUS_REVOKED,
				'updated_at'       => $now,
			);

			$updated = $wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				$members_table,
				$data,
				array( 'id' => (int) $member['id'] ),
				array( '%s', '%s', '%s', '%s', '%d', '%s', '%s' ),
				array( '%d' )
			);

		// Explicitly clear activation and consent timestamps.
				$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
					$wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber -- Table name sanitized via esc_sql().
						sprintf(
							'UPDATE `%s` SET activated_at = NULL, consent_recorded_at = NULL WHERE id = %%d',
							esc_sql( $members_table )
						),
						(int) $member['id']
					)
				);

			return false !== $updated;
	}

		/**
		 * Update attendance records to remove personal notes and detach references.
		 *
		 * @param wpdb   $wpdb               WordPress database abstraction.
		 * @param string $original_reference Original member reference.
		 * @param string $new_reference      Replacement member reference.
		 */
	private static function scrub_attendance( wpdb $wpdb, string $original_reference, string $new_reference ): bool {
			$attendance_table = Install::attendance_table_name( $wpdb );

		$result = $wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$attendance_table,
			array(
				'member_reference' => $new_reference,
				'note'             => '',
			),
			array( 'member_reference' => $original_reference ),
			array( '%s', '%s' ),
			array( '%s' )
		);

			return false !== $result;
	}

		/**
		 * Anonymize attendance override audit entries for the erased member.
		 *
		 * @param wpdb   $wpdb               WordPress database abstraction.
		 * @param string $original_reference Original member reference.
		 * @param string $new_reference      Replacement member reference.
		 */
	private static function scrub_attendance_overrides( wpdb $wpdb, string $original_reference, string $new_reference ): bool {
			$overrides_table = Install::attendance_overrides_table_name( $wpdb );

		$result = $wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$overrides_table,
			array(
				'member_reference' => $new_reference,
				'override_note'    => '',
			),
			array( 'member_reference' => $original_reference ),
			array( '%s', '%s' ),
			array( '%s' )
		);

			return false !== $result;
	}

		/**
		 * Remove token records for the erased member.
		 *
		 * @param wpdb $wpdb      WordPress database abstraction.
		 * @param int  $member_id Target member identifier.
		 */
	private static function remove_tokens( wpdb $wpdb, int $member_id ): bool {
			$tokens_table = Install::tokens_table_name( $wpdb );

		$deleted = $wpdb->delete( $tokens_table, array( 'member_id' => $member_id ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

			return false !== $deleted && $deleted > 0;
	}

		/**
		 * Remove audit log entries referencing the erased member.
		 *
		 * @param int $member_id Target member identifier.
		 */
	private static function scrub_audit_log( int $member_id ): bool {
			$log = get_option( 'fbm_members_action_audit', array() );

		if ( ! is_array( $log ) || empty( $log ) ) {
				return false;
		}

			$filtered = array();
			$changed  = false;

		foreach ( $log as $entry ) {
			if ( ! is_array( $entry ) || ! isset( $entry['member_id'] ) ) {
					$filtered[] = $entry;
					continue;
			}

			if ( (int) $entry['member_id'] === $member_id ) {
					$changed = true;
					continue;
			}

				$filtered[] = $entry;
		}

		if ( ! $changed ) {
				return false;
		}

			update_option( 'fbm_members_action_audit', array_values( $filtered ), false );

			return true;
	}

		/**
		 * Remove mail failure log entries for the erased member.
		 *
		 * @param int $member_id Target member identifier.
		 */
	private static function resolve_mail_failures( int $member_id ): bool {
			$before = get_option( 'fbm_mail_failures', array() );

			$log = new MailFailureLog();
			$log->resolve_member( $member_id );

			$after = get_option( 'fbm_mail_failures', array() );

		if ( is_array( $before ) && is_array( $after ) && count( $after ) < count( $before ) ) {
				return true;
		}

			return false;
	}
}
