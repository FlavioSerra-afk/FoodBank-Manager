<?php
/**
 * Diagnostics template.
 *
 * @package FoodBankManager
 * @since 0.1.1
 */

namespace FoodBankManager\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'fb_manage_settings' ) && ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ) );
}
?>
<div class="wrap">
	<h1><?php \esc_html_e( 'Diagnostics', 'foodbank-manager' ); ?></h1>
	<p><?php \esc_html_e( 'Coming soon.', 'foodbank-manager' ); ?></p>
		<?php
		$sodium_mode = \extension_loaded( 'sodium' )
			? esc_html__( 'native', 'foodbank-manager' )
			: ( \class_exists( '\\ParagonIE_Sodium_Compat' ) ? esc_html__( 'polyfill', 'foodbank-manager' ) : esc_html__( 'none', 'foodbank-manager' ) );
		?>
		<p><?php echo esc_html__( 'Sodium:', 'foodbank-manager' ) . ' ' . esc_html( $sodium_mode ); ?></p>
</div>
