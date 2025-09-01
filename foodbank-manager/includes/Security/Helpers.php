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
}
