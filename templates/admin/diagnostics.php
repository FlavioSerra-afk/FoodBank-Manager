<?php
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
	<p><?php echo \esc_html( 'Sodium: ' . ( \extension_loaded( 'sodium' ) ? 'native' : ( \class_exists( '\\ParagonIE_Sodium_Compat' ) ? 'polyfill' : 'none' ) ) ); ?></p>
</div>
