<?php
/**
 * Entry view template.
 *
 * @package FoodBankManager
 */

use FoodBankManager\Security\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
		exit;
}
?>
<?php echo '<div id="fbm-ui" class="fbm-scope fbm-app">'; ?>
<div class="wrap fbm-admin">
	<h1><?php esc_html_e( 'Entry', 'foodbank-manager' ); ?></h1>
	<table class="widefat striped">
		<tbody>
			<tr>
				<th><?php esc_html_e( 'ID', 'foodbank-manager' ); ?></th>
				<td><?php echo esc_html( (string) ( $entry['id'] ?? '' ) ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'First Name', 'foodbank-manager' ); ?></th>
				<td><?php echo esc_html( (string) ( $entry['data']['first_name'] ?? '' ) ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Last Name', 'foodbank-manager' ); ?></th>
				<td><?php echo esc_html( (string) ( $entry['pii']['last_name'] ?? '' ) ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Email', 'foodbank-manager' ); ?></th>
				<td><?php echo esc_html( (string) ( $entry['pii']['email'] ?? '' ) ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Postcode', 'foodbank-manager' ); ?></th>
				<td><?php echo esc_html( (string) ( $entry['data']['postcode'] ?? '' ) ); ?></td>
			</tr>
		</tbody>
	</table>
	<form method="post" style="margin-top:1em;display:inline;">
		<input type="hidden" name="fbm_action" value="entry_pdf" />
		<?php wp_nonce_field( 'fbm_entry_pdf', 'fbm_nonce' ); ?>
		<button type="submit" class="button"><?php esc_html_e( 'Export PDF', 'foodbank-manager' ); ?></button>
	</form>
	<?php if ( $can_sensitive && empty( $unmask ) ) : ?>
		<form method="post" style="margin-left:10px;display:inline;">
			<input type="hidden" name="fbm_action" value="unmask_entry" />
			<?php wp_nonce_field( 'fbm_entry_unmask', 'fbm_nonce' ); ?>
			<button type="submit" class="button"><?php esc_html_e( 'Unmask', 'foodbank-manager' ); ?></button>
		</form>
	<?php endif; ?>
	<h2 style="margin-top:2em;"><?php esc_html_e( 'History', 'foodbank-manager' ); ?></h2>
		<p><?php esc_html_e( 'No history available.', 'foodbank-manager' ); ?></p>
</div>
<?php echo '</div>'; ?>
