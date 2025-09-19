<?php
/**
 * WP-CLI command for managing encryption adapters.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\CLI;

use FoodBankManager\Crypto\EncryptionAdapter;
use FoodBankManager\Crypto\EncryptionManager;
use FoodBankManager\Crypto\EncryptionSettings;
use RuntimeException;

use function absint;
use function array_key_exists;
use function count;
use function is_array;
use function is_string;
use function sanitize_key;
use function sprintf;

/**
 * Provides status, migration, rotation, and verification tooling for encryption.
 */
final class CryptoCommand {
	/**
	 * Display encryption adapter status metrics.
	 *
	 * ## OPTIONS
	 *
	 * [--adapter=<adapter>]
	 * : Limit output to a single adapter identifier.
	 *
	 * @param array<int,string>   $args       Positional CLI arguments (unused).
	 * @param array<string,mixed> $assoc_args Associative CLI flags.
	 */
	public function status( array $args, array $assoc_args ): void {
			unset( $args );

			$adapters = $this->resolve_adapters( $assoc_args );

		if ( empty( $adapters ) ) {
				\WP_CLI::log( 'No encryption adapters are registered.' );

				return;
		}

			\WP_CLI::log( sprintf( 'Encrypt new writes: %s', EncryptionSettings::encrypt_new_writes_enabled() ? 'enabled' : 'disabled' ) );

		foreach ( $adapters as $adapter ) {
				$status    = $adapter->status();
				$total     = (int) ( $status['total'] ?? 0 );
				$encrypted = (int) ( $status['encrypted'] ?? 0 );
				$remaining = (int) ( $status['remaining'] ?? max( 0, $total - $encrypted ) );

				\WP_CLI::log(
					sprintf(
						'%s [%s]: %d total / %d encrypted / %d remaining',
						$adapter->label(),
						$adapter->id(),
						$total,
						$encrypted,
						$remaining
					)
				);

			if ( isset( $status['progress'] ) && is_array( $status['progress'] ) ) {
				$mode   = (string) ( $status['progress']['mode'] ?? '' );
				$cursor = (string) ( $status['progress']['cursor'] ?? '' );

				if ( '' !== $mode || '' !== $cursor ) {
							\WP_CLI::log( sprintf( '  Progress: mode=%s cursor=%s', $mode, $cursor ) );
				}
			}
		}

			\WP_CLI::success( 'Encryption adapter status listed.' );
	}

	/**
	 * Migrate plaintext records to envelopes.
	 *
	 * ## OPTIONS
	 *
	 * [--adapter=<adapter>]
	 * : Target a specific adapter identifier. Defaults to all adapters.
	 *
	 * [--limit=<number>]
	 * : Maximum records to process per adapter (default: 50).
	 *
	 * [--dry-run]
	 * : Calculate the impact without writing any changes.
	 *
	 * @param array<int,string>   $args       Positional CLI arguments (unused).
	 * @param array<string,mixed> $assoc_args Associative CLI flags.
	 */
	public function migrate( array $args, array $assoc_args ): void {
			unset( $args );

			$this->run_operation( 'migrate', $assoc_args );
	}

	/**
	 * Rotate existing envelopes by re-encrypting ciphertext.
	 *
	 * ## OPTIONS
	 *
	 * [--adapter=<adapter>]
	 * : Target a specific adapter identifier. Defaults to all adapters.
	 *
	 * [--limit=<number>]
	 * : Maximum records to process per adapter (default: 50).
	 *
	 * [--dry-run]
	 * : Calculate the impact without writing any changes.
	 *
	 * @param array<int,string>   $args       Positional CLI arguments (unused).
	 * @param array<string,mixed> $assoc_args Associative CLI flags.
	 */
	public function rotate( array $args, array $assoc_args ): void {
			unset( $args );

			$this->run_operation( 'rotate', $assoc_args );
	}

	/**
	 * Verify stored envelopes for integrity issues.
	 *
	 * ## OPTIONS
	 *
	 * [--adapter=<adapter>]
	 * : Target a specific adapter identifier. Defaults to all adapters.
	 *
	 * @param array<int,string>   $args       Positional CLI arguments (unused).
	 * @param array<string,mixed> $assoc_args Associative CLI flags.
	 */
	public function verify( array $args, array $assoc_args ): void {
			unset( $args );

			$adapters = $this->resolve_adapters( $assoc_args );

		if ( empty( $adapters ) ) {
				\WP_CLI::log( 'No encryption adapters are registered.' );

				return;
		}

		foreach ( $adapters as $adapter ) {
				$result   = $adapter->verify();
				$checked  = (int) ( $result['checked'] ?? 0 );
				$failures = is_array( $result['failures'] ?? null ) ? count( $result['failures'] ) : 0;

				\WP_CLI::log(
					sprintf(
						'%s [%s]: %d envelopes checked, %d failures',
						$adapter->label(),
						$adapter->id(),
						$checked,
						$failures
					)
				);

			if ( $failures > 0 ) {
				\WP_CLI::log( '  Investigate listed failure identifiers before proceeding.' );
			}
		}

			\WP_CLI::success( 'Encryption verification completed.' );
	}

	/**
	 * Execute migrate/rotate operations for the provided adapters.
	 *
	 * @param 'migrate'|'rotate'  $operation Operation name.
	 * @param array<string,mixed> $assoc_args CLI flags.
	 *
	 * @throws RuntimeException If an adapter returns an invalid response structure.
	 */
	private function run_operation( string $operation, array $assoc_args ): void {
			$adapters = $this->resolve_adapters( $assoc_args );

		if ( empty( $adapters ) ) {
				\WP_CLI::log( 'No encryption adapters are registered.' );

				return;
		}

			$limit  = 50;
			$dryrun = false;

		if ( array_key_exists( 'limit', $assoc_args ) && is_string( $assoc_args['limit'] ) ) {
				$candidate = absint( $assoc_args['limit'] );

			if ( $candidate > 0 ) {
					$limit = $candidate;
			}
		}

		if ( array_key_exists( 'dry-run', $assoc_args ) || array_key_exists( 'dry_run', $assoc_args ) ) {
				$dryrun = true;
		}

		foreach ( $adapters as $adapter ) {
				$result = 'migrate' === $operation
						? $adapter->migrate( $limit, $dryrun )
						: $adapter->rotate( $limit, $dryrun );

			if ( ! is_array( $result ) ) {
				throw new RuntimeException( sprintf( 'Adapter %s returned an invalid response.', sanitize_key( $adapter->id() ) ) );
			}

				$processed = (int) ( $result['processed'] ?? 0 );
				$changed   = (int) ( $result['changed'] ?? 0 );
				$complete  = ! empty( $result['complete'] );
				$failures  = is_array( $result['failures'] ?? null ) ? count( $result['failures'] ) : 0;

				\WP_CLI::log(
					sprintf(
						'%s [%s]: %d processed / %d changed / complete=%s / failures=%d%s',
						$adapter->label(),
						$adapter->id(),
						$processed,
						$changed,
						$complete ? 'yes' : 'no',
						$failures,
						$dryrun ? ' (dry-run)' : ''
					)
				);
		}

			$summary = sprintf( 'Encryption %s finished.', 'migrate' === $operation ? 'migration' : 'rotation' );
			\WP_CLI::success( $summary );
	}

	/**
	 * Resolve requested adapters based on CLI flags.
	 *
	 * @param array<string,mixed> $assoc_args CLI flags.
	 * @return array<string,EncryptionAdapter>
	 */
	private function resolve_adapters( array $assoc_args ): array {
			$adapters = EncryptionManager::adapters();

		if ( array_key_exists( 'adapter', $assoc_args ) ) {
				$adapter_id = $assoc_args['adapter'];

			if ( ! is_string( $adapter_id ) ) {
				\WP_CLI::error( 'Adapter identifier must be a string.' );
			}

				$normalized = sanitize_key( $adapter_id );
				$adapter    = EncryptionManager::get( $normalized );

			if ( null === $adapter ) {
					\WP_CLI::error( sprintf( 'Unknown encryption adapter: %s', $adapter_id ) );
			}

				return array( $normalized => $adapter );
		}

			return $adapters;
	}
}
