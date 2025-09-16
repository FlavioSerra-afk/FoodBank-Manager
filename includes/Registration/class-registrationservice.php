<?php
/**
 * Registration application service.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Registration;

use Exception;
use FoodBankManager\Token\TokenService;
use RuntimeException;
use function bin2hex;
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
				 * Token issuance service.
				 *
				 * @var TokenService
				 */
	private TokenService $tokens;

				/**
				 * Constructor.
				 *
				 * @param MembersRepository $repository   Members repository instance.
				 * @param TokenService      $token_service Token issuance service.
				 */
	public function __construct( MembersRepository $repository, TokenService $token_service ) {
			$this->repository = $repository;
			$this->tokens     = $token_service;
	}

				/**
				 * Register or reactivate a member record.
				 *
				 * @param string $first_name     Sanitized first name.
				 * @param string $last_initial   Sanitized last initial.
				 * @param string $email          Normalized email address.
				 * @param int    $household_size Household size clamp.
				 *
				 * @return array{member_id:int,member_reference:string,token:string,reactivated:bool}|null
				 */
	public function register( string $first_name, string $last_initial, string $email, int $household_size ): ?array {
					$existing = $this->repository->find_by_email( $email );

		if ( null !== $existing ) {
			if ( ! $this->repository->mark_active( $existing['id'] ) ) {
								return null;
			}

			try {
							$token = $this->tokens->issue( $existing['id'] );
			} catch ( RuntimeException $exception ) {
							unset( $exception );

							return null;
			}

									return array(
										'member_id'        => $existing['id'],
										'member_reference' => $existing['member_reference'],
										'token'            => $token,
										'reactivated'      => true,
									);
		}

					$reference = $this->generate_reference();

		if ( '' === $reference ) {
			return null;
		}

					$member_id = $this->repository->insert_active_member( $reference, $first_name, $last_initial, $email, $household_size );

		if ( null === $member_id ) {
				return null;
		}

		try {
				$token = $this->tokens->issue( $member_id );
		} catch ( RuntimeException $exception ) {
				unset( $exception );

				return null;
		}

					return array(
						'member_id'        => $member_id,
						'member_reference' => $reference,
						'token'            => $token,
						'reactivated'      => false,
					);
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
