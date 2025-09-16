<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

if ( ! defined( 'ARRAY_A' ) ) {
	define( 'ARRAY_A', 'ARRAY_A' );
}

if ( ! class_exists( 'wpdb', false ) ) {
	/**
	 * Minimal wpdb stand-in for unit tests.
	 */
	class wpdb {
		/**
		 * WordPress table prefix.
		 *
		 * @var string
		 */
		public string $prefix = 'wp_';

		/**
		 * Captured token rows keyed by member ID.
		 *
		 * @var array<int,array{member_id:int,token_hash:string,issued_at:string,revoked_at:?string}>
		 */
		public array $tokens = array();

		/**
		 * Arguments from the most recent prepare() call.
		 *
		 * @var array<int|string,mixed>|null
		 */
		private ?array $last_prepare_args = null;

		public function replace( string $table, array $data, array $format ) {
			unset( $format );
			unset( $table );

			$member_id                  = (int) $data['member_id'];
			$this->tokens[ $member_id ] = array(
				'member_id'  => $member_id,
				'token_hash' => (string) $data['token_hash'],
				'issued_at'  => (string) $data['issued_at'],
				'revoked_at' => null,
			);

			return 1;
		}

		public function prepare( string $query, ...$args ) {
			$this->last_prepare_args = $args;

			return $query;
		}

		public function get_row( string $query, $output = ARRAY_A ) {
			unset( $query );
			unset( $output );

			if ( ! is_array( $this->last_prepare_args ) ) {
				return null;
			}

			$hash = (string) ( $this->last_prepare_args[1] ?? '' );

			foreach ( $this->tokens as $record ) {
				if ( $record['token_hash'] === $hash && null === $record['revoked_at'] ) {
					return array(
						'member_id'  => $record['member_id'],
						'token_hash' => $record['token_hash'],
					);
				}
			}

			return null;
		}

		public function update( string $table, array $data, array $where, array $format, array $where_format ) {
			unset( $table );
			unset( $format );
			unset( $where_format );

			$member_id = (int) ( $where['member_id'] ?? 0 );
			if ( ! isset( $this->tokens[ $member_id ] ) ) {
				return 0;
			}

			$this->tokens[ $member_id ]['revoked_at'] = $data['revoked_at'] ?? null;

			return 1;
		}
	}
}

require_once __DIR__ . '/../includes/Core/class-install.php';
require_once __DIR__ . '/../includes/Token/class-tokenrepository.php';
require_once __DIR__ . '/../includes/Token/class-tokenservice.php';
