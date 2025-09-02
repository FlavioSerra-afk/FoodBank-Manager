<?php // phpcs:ignoreFile

declare(strict_types=1);

namespace FoodBankManager\Security;

class Helpers {

	public static function verify_nonce( string $action, string $name = '_wpnonce' ): bool {
		$nonce = isset( $_REQUEST[ $name ] ) ? wp_unslash( (string) $_REQUEST[ $name ] ) : '';
		return wp_verify_nonce( $nonce, $action ) !== false;
	}

	public static function require_nonce( string $action ): void {
		if ( ! self::verify_nonce( $action ) ) {
			wp_die( esc_html__( 'Invalid nonce.', 'foodbank-manager' ), esc_html__( 'Error', 'foodbank-manager' ), array( 'response' => 403 ) );
		}
	}

	public static function sanitize_text( string $value ): string {
		return sanitize_text_field( $value );
	}

        public static function esc_html( string $value ): string {
                return esc_html( $value );
        }

        public static function mask_email( string $email ): string {
                $email = sanitize_email( $email );
                if ( $email === '' ) {
                        return '';
                }
                $parts = explode( '@', $email );
                if ( count( $parts ) !== 2 ) {
                        return '';
                }
                $user = mb_substr( $parts[0], 0, 1 ) . '***';
                return $user . '@' . $parts[1];
        }

        public static function mask_postcode( string $postcode ): string {
                $postcode = strtoupper( trim( $postcode ) );
                $parts    = preg_split( '/\s+/', $postcode );
                if ( count( $parts ) !== 2 ) {
                        return $postcode;
                }
                $first  = mb_substr( $parts[0], 0, 2 ) . '*';
                $second = mb_substr( $parts[1], 0, 1 ) . '**';
                return $first . ' ' . $second;
        }
}
