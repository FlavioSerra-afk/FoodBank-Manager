<?php
/**
 * Bootstrap constants for PHPStan when running outside WordPress.
 *
 * @package FoodBankManager
 */

declare( strict_types=1 );
// Define plugin constants for static analysis only.
// These do NOT affect runtime when WordPress defines real values.
if ( ! defined( 'FBM_FILE' ) ) {
	define( 'FBM_FILE', __FILE__ );
}
if ( ! defined( 'FBM_PATH' ) ) {
	define( 'FBM_PATH', __DIR__ . '/' );
}
if ( ! defined( 'FBM_URL' ) ) {
	define( 'FBM_URL', 'https://example.invalid/wp-content/plugins/foodbank-manager/' );
}
if ( ! defined( 'FBM_VER' ) ) {
		define( 'FBM_VER', '0.0.0-dev' );
}

if ( ! function_exists( 'wp_privacy_personal_data_erasers' ) ) {
	/**
	 * Provide default privacy eraser registry for static analysis.
	 */
	function wp_privacy_personal_data_erasers(): array {
		return array();
	}
}

if ( ! function_exists( 'wp_privacy_process_personal_data_erasure' ) ) {
	/**
	 * Simulate WordPress privacy eraser batch response for static analysis.
	 *
	 * @param string $email_address Identifier passed to core.
	 * @param array  $erasers       Registered erasers.
	 * @param int    $page          Batch number.
	 */
	function wp_privacy_process_personal_data_erasure( string $email_address, array $erasers, int $page ): array { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid -- Matching WordPress core signature.
		unset( $email_address );
		unset( $erasers );
		unset( $page );

		return array(
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => array(),
			'done'           => true,
		);
	}
}
