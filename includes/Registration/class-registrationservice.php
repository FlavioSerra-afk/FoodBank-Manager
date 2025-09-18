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
use WP_User;
use function bin2hex;
use function get_user_by;
use function gmdate;
use function is_wp_error;
use function preg_replace;
use function random_bytes;
use function strtolower;
use function substr;
use function trim;
use function strtoupper;
use function wp_generate_password;
use function wp_insert_user;

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
	 * Registration settings accessor.
	 *
	 * @var RegistrationSettings
	 */
	private RegistrationSettings $settings;

	/**
	 * Constructor.
	 *
	 * @param MembersRepository         $repository    Members repository instance.
	 * @param TokenService              $token_service Token issuance service.
	 * @param RegistrationSettings|null $settings  Optional settings accessor.
	 */
	public function __construct( MembersRepository $repository, TokenService $token_service, ?RegistrationSettings $settings = null ) {
		$this->repository = $repository;
		$this->tokens     = $token_service;
		$this->settings   = $settings ?? new RegistrationSettings();
	}

	/**
	 * Register or reactivate a member record.
	 *
	 * @param string   $first_name          Sanitized first name.
	 * @param string   $last_initial        Sanitized last initial.
	 * @param string   $email               Normalized email address.
	 * @param int      $household_size      Household size clamp.
	 * @param int|null $consent_recorded_at Consent acknowledgement timestamp.
	 *
	 * @return array{member_id:int,member_reference:string,token:?string,reactivated:bool,status?:string}|null
	 */
	public function register( string $first_name, string $last_initial, string $email, int $household_size, ?int $consent_recorded_at = null ): ?array {
		$consent_at = null;
		if ( null !== $consent_recorded_at && $consent_recorded_at > 0 ) {
			$consent_at = gmdate( 'Y-m-d H:i:s', $consent_recorded_at );
		}

		$existing = $this->repository->find_by_email( $email );

		if ( null !== $existing ) {
			if ( ! $this->repository->mark_active( $existing['id'], $consent_at ) ) {
				return null;
			}

			$issuance = $this->issue_member_token( $existing['id'], 'reactivation' );
			if ( null === $issuance ) {
				return null;
			}

			$this->ensure_foodbank_member_user( $email, $first_name, $last_initial );

			return array(
				'member_id'        => $existing['id'],
				'member_reference' => $existing['member_reference'],
				'token'            => $issuance['token'],
				'reactivated'      => true,
				'status'           => MembersRepository::STATUS_ACTIVE,
			);
		}

		$reference = $this->generate_reference();
		if ( '' === $reference ) {
			return null;
		}

		if ( $this->settings->auto_approve() ) {
			$member_id = $this->repository->insert_active_member( $reference, $first_name, $last_initial, $email, $household_size, $consent_at );
			if ( null === $member_id ) {
				return null;
			}

			$issuance = $this->issue_member_token( $member_id, 'registration' );
			if ( null === $issuance ) {
				return null;
			}

			$this->ensure_foodbank_member_user( $email, $first_name, $last_initial );

			return array(
				'member_id'        => $member_id,
				'member_reference' => $reference,
				'token'            => $issuance['token'],
				'reactivated'      => false,
				'status'           => MembersRepository::STATUS_ACTIVE,
			);
		}

		$member_id = $this->repository->insert_pending_member( $reference, $first_name, $last_initial, $email, $household_size, $consent_at );
		if ( null === $member_id ) {
			return null;
		}

		return array(
			'member_id'        => $member_id,
			'member_reference' => $reference,
			'token'            => null,
			'reactivated'      => false,
			'status'           => MembersRepository::STATUS_PENDING,
		);
	}

	/**
	 * Ensure the associated WordPress user has the FoodBank Member role.
	 *
	 * @param string $email        Normalized email address.
	 * @param string $first_name   First name for user provisioning.
	 * @param string $last_initial Last initial for user provisioning.
	 */
	public function ensure_foodbank_member_user( string $email, string $first_name, string $last_initial ): void {
		if ( '' === $email ) {
			return;
		}

		$user = get_user_by( 'email', $email );

		if ( $user instanceof WP_User ) {
			$user->add_role( 'foodbank_member' );

			return;
		}

		$user_id = wp_insert_user(
			array(
				'user_login'   => $this->generate_user_login( $email ),
				'user_email'   => $email,
				'user_pass'    => wp_generate_password( 20, true ),
				'first_name'   => $first_name,
				'last_name'    => $last_initial,
				'display_name' => trim( $first_name . ' ' . $last_initial ),
				'role'         => '',
			)
		);

		if ( is_wp_error( $user_id ) ) {
			return;
		}

		$user_id = (int) $user_id;
		if ( $user_id <= 0 ) {
			return;
		}

		$user = get_user_by( 'id', $user_id );

		if ( $user instanceof WP_User ) {
			$user->add_role( 'foodbank_member' );
		}
	}

	/**
	 * Generate a safe fallback login for provisioned users.
	 *
	 * @param string $email Normalized email address.
	 */
	private function generate_user_login( string $email ): string {
		$seed = preg_replace( '/[^a-z0-9]/', '', strtolower( $email ) );

		if ( ! is_string( $seed ) || '' === $seed ) {
			$seed = 'fbm';
		}

		$suffix = strtolower( $this->random_segment() );
		$base   = substr( $seed, 0, 40 );
		$login  = $base . '_' . $suffix;

		return substr( $login, 0, 60 );
	}

	/**
	 * Approve a pending member and issue a persistent token.
	 *
	 * @param int      $member_id Member identifier.
	 * @param int|null $issued_by Acting user identifier.
	 *
         * @return array{member_id:int,member_reference:string,first_name:string,email:string,status:string,token:string,token_hash:string,issued_at:string,meta:array<string,mixed>}|null
	 */
        public function approve( int $member_id, ?int $issued_by = null ): ?array {
                $member = $this->repository->find( $member_id );
                if ( null === $member ) {
                        return null;
                }

                $existing_token = $this->tokens->find_active_for_member( $member_id );

                if ( MembersRepository::STATUS_ACTIVE !== $member['status'] ) {
                        if ( ! $this->repository->mark_active( $member_id ) ) {
                                return null;
                        }

                        $member['status'] = MembersRepository::STATUS_ACTIVE;
                }

                $issuance = null;

                if ( null !== $existing_token ) {
                        $payload = isset( $existing_token['meta']['payload'] )
                                ? (string) $existing_token['meta']['payload']
                                : '';

                        if ( '' !== $payload ) {
                                $issuance = array(
                                        'token'      => $payload,
                                        'token_hash' => $existing_token['token_hash'],
                                        'issued_at'  => $existing_token['issued_at'],
                                        'meta'       => $existing_token['meta'],
                                );
                        }
                }

                if ( null === $issuance ) {
                        $issuance = $this->issue_member_token( $member_id, 'approval', $issued_by );
                        if ( null === $issuance ) {
                                return null;
                        }
                }

                $last_initial = (string) ( $member['last_initial'] ?? '' );
                $this->ensure_foodbank_member_user( $member['email'], $member['first_name'], $last_initial );

                return array(
                        'member_id'        => $member['id'],
                        'member_reference' => $member['member_reference'],
                        'first_name'       => $member['first_name'],
                        'email'            => $member['email'],
                        'status'           => MembersRepository::STATUS_ACTIVE,
                        'token'            => $issuance['token'],
                        'token_hash'       => $issuance['token_hash'],
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
         * @return array{member_id:int,member_reference:string,first_name:string,email:string,status:string,token:string,token_hash:string,issued_at:string,meta:array<string,mixed>}|null
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
                        'token_hash'       => $issuance['token_hash'],
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
         * @return array{token:string,token_hash:string,issued_at:string,meta:array<string,mixed>}|null
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
