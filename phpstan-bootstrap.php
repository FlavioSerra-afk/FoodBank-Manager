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
if ( ! defined( 'FBM_VERSION' ) ) {
	define( 'FBM_VERSION', '0.0.0-dev' );
}
