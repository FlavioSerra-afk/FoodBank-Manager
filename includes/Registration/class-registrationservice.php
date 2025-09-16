<?php
/**
 * Registration application service.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Registration;

use Exception;
use function bin2hex;
use function is_array;
use function random_bytes;
use function strtoupper;
use function wp_generate_password;

/**
 * Coordinates member registration writes.
 */
final class RegistrationService {

	private const MAX_REFERENCE_ATTEMPTS = 5;

		/**
		 * Members repository dependency.
		 *
		 * @var MembersRepository
		 */
	private MembersRepository $repository;

		/**
		 * Constructor.
		 *
		 * @param MembersRepository $repository Members repository instance.
		 */
	public function __construct( MembersRepository $repository ) {
			$this->repository = $repository;
	}

		/**
		 * Register or reactivate a member record.
		 *
		 * @param string $first_name     Sanitized first name.
		 * @param string $last_initial   Sanitized last initial.
		 * @param string $email          Normalized email address.
		 * @param int    $household_size Household size clamp.
		 */
	public function register( string $first_name, string $last_initial, string $email, int $household_size ): bool {
				$existing = $this->repository->find_by_email( $email );

		if ( null !== $existing ) {
				return $this->repository->mark_active( $existing['id'] );
		}

			$reference = $this->generate_reference();

		if ( '' === $reference ) {
				return false;
		}

			return $this->repository->insert_active_member( $reference, $first_name, $last_initial, $email, $household_size );
	}

		/**
		 * Generate a unique member reference token.
		 */
	private function generate_reference(): string {
			$attempts = 0;

		while ( $attempts < self::MAX_REFERENCE_ATTEMPTS ) {
				++$attempts;

				$token     = $this->random_segment();
				$reference = 'FBM-' . $token;

			if ( ! $this->repository->reference_exists( $reference ) ) {
				return $reference;
			}
		}

			return '';
	}

		/**
		 * Produce a random uppercase segment for member references.
		 */
	private function random_segment(): string {
		try {
				return strtoupper( bin2hex( random_bytes( 4 ) ) );
		} catch ( Exception $exception ) {
				unset( $exception );

				return strtoupper( wp_generate_password( 8, false ) );
		}
	}
}
