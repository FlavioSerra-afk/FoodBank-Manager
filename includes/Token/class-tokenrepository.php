<?php
/**
 * Token repository.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Token;

use FoodBankManager\Core\Install;
use wpdb;

use function is_array;
use function is_string;

use const ARRAY_A;

/**
 * Persists authentication tokens.
 */
final class TokenRepository {

		/**
		 * WordPress database abstraction.
		 *
		 * @var wpdb
		 */
	private wpdb $wpdb;

		/**
		 * Fully qualified table name.
		 *
		 * @var string
		 */
	private string $table;

		/**
		 * Constructor.
		 *
		 * @param wpdb $wpdb WordPress database abstraction.
		 */
	public function __construct( wpdb $wpdb ) {
			$this->wpdb  = $wpdb;
			$this->table = Install::tokens_table_name( $wpdb );
	}

		/**
		 * Persist an active token for a member, replacing any previous value.
		 *
		 * @param int    $member_id Member identifier.
		 * @param string $token_hash Hashed token value.
		 * @param string $issued_at  Issue timestamp (UTC).
		 * @param string $version    Token version identifier.
		 */
	public function persist_active( int $member_id, string $token_hash, string $issued_at, string $version ): bool {
			$data = array(
				'member_id'  => $member_id,
				'token_hash' => $token_hash,
				'issued_at'  => $issued_at,
				'version'    => $version,
			);

			$formats = array( '%d', '%s', '%s', '%s' );

			$result = $this->wpdb->replace( $this->table, $data, $formats );

			return false !== $result;
	}

		/**
		 * Find an active token by its hashed value.
		 *
		 * @param string $token_hash Hashed token value.
		 *
		 * @return array{member_id:int,token_hash:string,version:string}|null
		 */
	public function find_active_by_hash( string $token_hash ): ?array {
		$sql = $this->wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is trusted.
			"SELECT member_id, token_hash, version FROM `{$this->table}` WHERE token_hash = %s AND revoked_at IS NULL LIMIT 1",
			$token_hash
		);

		if ( ! is_string( $sql ) ) {
				return null;
		}

		/**
		 * Result row.
		 *
		 * @var array{member_id:int|string,token_hash:string,version?:string}|null $row
		 */
		$row = $this->wpdb->get_row( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Prepared above.
		if ( ! is_array( $row ) ) {
			return null;
		}

		return array(
			'member_id'  => (int) $row['member_id'],
			'token_hash' => (string) $row['token_hash'],
			'version'    => (string) ( $row['version'] ?? 'v1' ),
		);
	}

		/**
		 * Mark all tokens for a member as revoked.
		 *
		 * @param int    $member_id  Member identifier.
		 * @param string $revoked_at Revocation timestamp (UTC).
		 */
	public function revoke_member( int $member_id, string $revoked_at ): bool {
			$data = array(
				'revoked_at' => $revoked_at,
			);

			$where = array(
				'member_id' => $member_id,
			);

			$result = $this->wpdb->update( $this->table, $data, $where, array( '%s' ), array( '%d' ) );

			if ( false === $result || 0 === $result ) {
				return false;
			}

			return true;
	}
}
