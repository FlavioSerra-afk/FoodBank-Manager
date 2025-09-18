<?php
/**
 * WP-CLI command for probing tokens.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\CLI;

use FoodBankManager\Diagnostics\TokenProbeService;
use FoodBankManager\Token\Token;
use FoodBankManager\Token\TokenRepository;
use RuntimeException;
use wpdb;

use function count;
use function json_encode;
use function trim;

/**
 * Provides redacted diagnostics for opaque member tokens.
 */
final class TokenCommand {
        /**
         * Token probe service.
         *
         * @var TokenProbeService
         */
        private TokenProbeService $service;

        /**
         * Constructor.
         *
         * @param TokenProbeService|null $service Optional probe service override for tests.
         */
        public function __construct( ?TokenProbeService $service = null ) {
                if ( null !== $service ) {
                        $this->service = $service;

                        return;
                }

                $this->service = $this->build_service();
        }

        /**
         * Probe a token payload for diagnostics.
         *
         * ## OPTIONS
         *
         * <payload>
         * : Raw token payload to analyse.
         *
         * @param array<int,string>   $args       Positional CLI arguments.
         * @param array<string,mixed> $assoc_args Associative CLI arguments (unused).
         */
        public function probe( array $args, array $assoc_args ): void {
                unset( $assoc_args );

                if ( 1 !== count( $args ) ) {
                        \WP_CLI::error( 'Exactly one token payload argument is required.' );
                }

                $payload = trim( (string) $args[0] );

                if ( '' === $payload ) {
                        \WP_CLI::error( 'The token payload cannot be empty.' );
                }

                $result  = $this->service->probe( $payload );
                $encoded = json_encode( $result );

                if ( false === $encoded ) {
                        \WP_CLI::error( 'Unable to encode probe result.' );
                }

                \WP_CLI::success( $encoded );
        }

        /**
         * Build the default probe service instance.
         */
        private function build_service(): TokenProbeService {
                global $wpdb;

                if ( ! $wpdb instanceof wpdb ) {
                        throw new RuntimeException( 'Token probe requires the WordPress database layer.' );
                }

                $repository = new TokenRepository( $wpdb );
                $token      = new Token( $repository );

                return new TokenProbeService( $token );
        }
}
