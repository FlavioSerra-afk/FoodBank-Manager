<?php
/**
 * Base class for envelope encryption adapters.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Crypto;

use function delete_option;
use function get_option;
use function is_array;
use function update_option;

/**
 * Defines shared progress and detection helpers for adapters.
 */
abstract class EncryptionAdapter {
		/**
		 * Unique adapter identifier (used for CLI/Admin references).
		 */
	abstract public function id(): string;

		/**
		 * Human-readable adapter label.
		 */
	abstract public function label(): string;

		/**
		 * Compute adapter status metrics.
		 *
		 * @return array<string,mixed>
		 */
	abstract public function status(): array;

		/**
		 * Migrate plaintext records to envelopes.
		 *
		 * @param int  $limit   Maximum rows to evaluate.
		 * @param bool $dry_run When true, do not persist changes or checkpoints.
		 *
		 * @return array<string,mixed>
		 */
	abstract public function migrate( int $limit, bool $dry_run ): array;

		/**
		 * Rotate existing envelopes by re-encrypting their contents.
		 *
		 * @param int  $limit   Maximum rows to evaluate.
		 * @param bool $dry_run When true, do not persist changes or checkpoints.
		 *
		 * @return array<string,mixed>
		 */
	abstract public function rotate( int $limit, bool $dry_run ): array;

		/**
		 * Verify integrity of stored envelopes.
		 *
		 * @return array<string,mixed>
		 */
	abstract public function verify(): array;

		/**
		 * Clear stored checkpoint progress.
		 */
	public function reset_progress(): void {
			delete_option( $this->progress_option() );
	}

	/**
	 * Determine whether a stored value is likely an FBM envelope.
	 *
	 * @param string $value Value to inspect.
	 *
	 * @return bool
	 */
	protected function is_envelope( string $value ): bool {
		return Crypto::is_envelope( $value );
	}

		/**
		 * Load previously stored progress metadata.
		 *
		 * @return array<string,mixed>
		 */
	protected function load_progress(): array {
			$raw = get_option( $this->progress_option(), array() );

			return is_array( $raw ) ? $raw : array();
	}

		/**
		 * Persist progress metadata for resumable batches.
		 *
		 * @param array<string,mixed> $progress Progress payload to store.
		 */
	protected function save_progress( array $progress ): void {
			update_option( $this->progress_option(), $progress, false );
	}

		/**
		 * Resolve the WordPress option name for storing progress.
		 */
	private function progress_option(): string {
			return 'fbm_encryption_progress_' . $this->id();
	}
}
