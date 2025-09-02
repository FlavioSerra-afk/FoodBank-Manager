<?php
/**
 * Forms page template.
 *
 * @package FoodBankManager
 * @since 0.1.1
 */

namespace FoodBankManager\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Forms management placeholder.

if ( ! current_user_can( 'fb_manage_forms' ) && ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ) );
}
?>
<div class="wrap">
	<h1><?php \esc_html_e( 'Forms', 'foodbank-manager' ); ?></h1>
	<p><?php \esc_html_e( 'Coming soon.', 'foodbank-manager' ); ?></p>
</div>
