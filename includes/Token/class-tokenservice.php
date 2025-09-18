<?php
/**
 * Token service.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Token;

use RuntimeException;

/**
 * Issues and validates signed access tokens.
 */
final class TokenService {

	/**
	 * Token core implementation.
	 *
	 * @var Token
	 */
	private Token $token;

	/**
	 * Constructor.
	 *
	 * @param TokenRepository $repository     Token repository implementation.
	 * @param string|null     $current_secret Active signing secret override.
	 * @param string|null     $previous_secret Previous signing secret override.
	 */
	public function __construct( TokenRepository $repository, ?string $current_secret = null, ?string $previous_secret = null ) {
		$this->token = new Token( $repository, $current_secret, $previous_secret );
	}

	/**
	 * Issue a new token for the provided member identifier.
	 *
	 * @param int                  $member_id Member identifier.
	 * @param array<string, mixed> $meta      Optional token metadata to persist.
	 *
	 * @throws RuntimeException When the token cannot be persisted.
	 */
	public function issue( int $member_id, array $meta = array() ): string {
		$details = $this->issue_with_details( $member_id, $meta );

		return $details['token'];
	}

	/**
	 * Issue a token and return the full issuance payload.
	 *
	 * @param int                  $member_id Member identifier.
	 * @param array<string, mixed> $meta      Optional metadata context.
	 *
	 * @return array{token:string,token_hash:string,version:string,issued_at:string,meta:array<string,mixed>}
	 *
	 * @throws RuntimeException When the token cannot be persisted.
	 */
	public function issue_with_details( int $member_id, array $meta = array() ): array {
		$issuance = $this->token->issue( $member_id, $meta );

		return array(
			'token'      => $issuance['payload'],
			'token_hash' => $issuance['token_hash'],
			'version'    => $issuance['version'],
			'issued_at'  => $issuance['issued_at'],
			'meta'       => $issuance['meta'],
		);
	}

	/**
	 * Verify a raw token string and return the associated member identifier.
	 *
	 * @param string $raw_token Token provided by the caller.
	 *
	 * @return int|null Member identifier when valid; otherwise null.
	 */
	public function verify( string $raw_token ): ?int {
		$result = $this->token->verify( $raw_token );

		if ( ! $result['ok'] ) {
			return null;
		}

		return $result['member_id'];
	}

	/**
	 * Revoke all active tokens for the provided member.
	 *
	 * @param int $member_id Member identifier.
	 */
        public function revoke( int $member_id ): bool {
                return $this->token->revoke( $member_id );
        }

        /**
         * Retrieve the active token details for a member.
         *
         * @param int $member_id Member identifier.
         *
         * @return array{member_id:int,token_hash:string,version:string,issued_at:string,meta:array<string,mixed>}|null
         */
        public function find_active_for_member( int $member_id ): ?array {
                return $this->token->find_active_for_member( $member_id );
        }
}
