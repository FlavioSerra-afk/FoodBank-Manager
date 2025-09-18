<?php
/**
 * Token probe diagnostics service.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Diagnostics;

use FoodBankManager\Token\Token;

use function preg_match;

/**
 * Safely inspects opaque token payloads without exposing PII.
 */
final class TokenProbeService {
        /**
         * Core token verifier.
         *
         * @var Token
         */
        private Token $token;

        /**
         * Constructor.
         *
         * @param Token $token Token core implementation.
         */
        public function __construct( Token $token ) {
                $this->token = $token;
        }

        /**
         * Analyse a raw token payload and surface redacted diagnostics.
         *
         * @param string $raw Raw token payload provided by the caller.
         *
         * @return array{version:?string,hmac_match:bool,revoked:bool}
         */
        public function probe( string $raw ): array {
                $canonical = Token::canonicalize( $raw );
                $version   = $this->resolve_version( $canonical );

                if ( null === $canonical ) {
                        return array(
                                'version'    => $version,
                                'hmac_match' => false,
                                'revoked'    => false,
                        );
                }

                $verification = $this->token->verify( $raw );
                $reason       = (string) $verification['reason'];

                $revoked    = 'revoked' === $reason;
                $hmac_match = $revoked || 'ok' === $reason;

                return array(
                        'version'    => $version,
                        'hmac_match' => $hmac_match,
                        'revoked'    => $revoked,
                );
        }

        /**
         * Resolve the storage version identifier for a canonical payload.
         *
         * @param string|null $canonical Canonical token payload.
         */
        private function resolve_version( ?string $canonical ): ?string {
                if ( null === $canonical ) {
                        return null;
                }

                if ( 1 !== preg_match( '/^FBM(\d+):/', $canonical, $matches ) ) {
                        return null;
                }

                $segment = (string) $matches[1];

                return $segment;
        }
}
