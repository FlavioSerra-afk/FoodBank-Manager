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

				$issuance = $this->issue_member_token( $existing['id'], 'reactivation' );
			if ( null === $issuance ) {
					return null;
			}

																return array(
																	'member_id'        => $existing['id'],
																	'member_reference' => $existing['member_reference'],
																	'token'            => $issuance['token'],
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

			$issuance = $this->issue_member_token( $member_id, 'registration' );
		if ( null === $issuance ) {
				return null;
		}

									return array(
										'member_id'        => $member_id,
										'member_reference' => $reference,
										'token'            => $issuance['token'],
										'reactivated'      => false,
									);
	}

		/**
		 * Approve a pending member and issue a persistent token.
		 *
		 * @param int      $member_id Member identifier.
		 * @param int|null $issued_by Acting user identifier.
		 *
		 * @return array{member_id:int,member_reference:string,first_name:string,email:string,status:string,token:string,issued_at:string,meta:array<string,mixed>}|null
		 */
	public function approve( int $member_id, ?int $issued_by = null ): ?array {
			$member = $this->repository->find( $member_id );
		if ( null === $member ) {
				return null;
		}

		if ( 'active' !== $member['status'] ) {
			if ( ! $this->repository->mark_active( $member_id ) ) {
					return null;
			}

				$member['status'] = 'active';
		}

			$issuance = $this->issue_member_token( $member_id, 'approval', $issued_by );
		if ( null === $issuance ) {
				return null;
		}

			return array(
				'member_id'        => $member['id'],
				'member_reference' => $member['member_reference'],
				'first_name'       => $member['first_name'],
				'email'            => $member['email'],
				'status'           => $member['status'],
				'token'            => $issuance['token'],
				'issued_at'        => $issuance['issued_at'],
				'meta'             => $issuance['meta'],
			);
	}

		/**
		 * Regenerate a member token without changing status.
		 *
		 * @param int      $member_id Member identifier.
		 * @param string   $context   Issuance context descriptor.
		 * @param int|null $issued_by Acting user identifier.
		 *
		 * @return array{member_id:int,member_reference:string,first_name:string,email:string,status:string,token:string,issued_at:string,meta:array<string,mixed>}|null
		 */
	public function regenerate( int $member_id, string $context = 'regenerate', ?int $issued_by = null ): ?array {
			$member = $this->repository->find( $member_id );
		if ( null === $member ) {
				return null;
		}

			$issuance = $this->issue_member_token( $member_id, $context, $issued_by );
		if ( null === $issuance ) {
				return null;
		}

			return array(
				'member_id'        => $member['id'],
				'member_reference' => $member['member_reference'],
				'first_name'       => $member['first_name'],
				'email'            => $member['email'],
				'status'           => $member['status'],
				'token'            => $issuance['token'],
				'issued_at'        => $issuance['issued_at'],
				'meta'             => $issuance['meta'],
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

		/**
		 * Issue and persist a token for a member with contextual metadata.
		 *
		 * @param int      $member_id Member identifier.
		 * @param string   $context   Issuance context descriptor.
		 * @param int|null $issued_by Acting user identifier.
		 *
		 * @return array{token:string,issued_at:string,meta:array<string,mixed>}|null
		 */
	private function issue_member_token( int $member_id, string $context, ?int $issued_by = null ): ?array {
			$meta = array(
				'context' => $context,
			);

			if ( null !== $issued_by ) {
					$meta['issued_by'] = (int) $issued_by;
			}

			try {
					return $this->tokens->issue_with_details( $member_id, $meta );
			} catch ( RuntimeException $exception ) {
					unset( $exception );

					return null;
			}
	}
}
