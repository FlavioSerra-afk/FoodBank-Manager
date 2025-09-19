<?php
/**
 * Settings admin template.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

use FoodBankManager\Registration\RegistrationSettings;

$settings     = $data['settings'] ?? array();
$registration = array();

if ( isset( $settings['registration'] ) && is_array( $settings['registration'] ) ) {
		$registration = $settings['registration'];
} else {
		$defaults     = RegistrationSettings::defaults();
		$registration = $defaults['registration'];
}

$status_value = $data['status'] ?? '';
$message_text = $data['message'] ?? '';

$form_action  = $data['form_action'] ?? 'fbm_settings_save';
$nonce_action = $data['nonce_action'] ?? 'fbm_settings_save';
$nonce_name   = $data['nonce_name'] ?? 'fbm_settings_nonce';
$uninstall    = $data['uninstall'] ?? array();

$notice_class = '';

if ( 'success' === $status_value ) {
	$notice_class = 'notice-success';
} elseif ( '' !== $message_text ) {
	$notice_class = 'notice-error';
}

$auto_approve = ! empty( $registration['auto_approve'] );

?>
<div class="wrap">
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Food Bank Settings', 'foodbank-manager' ); ?></h1>

<?php if ( '' !== $message_text ) : ?>
<div class="notice <?php echo esc_attr( $notice_class ); ?> is-dismissible">
<p><?php echo esc_html( $message_text ); ?></p>
</div>
<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( (string) $nonce_action, (string) $nonce_name ); ?>
				<input type="hidden" name="action" value="<?php echo esc_attr( (string) $form_action ); ?>" />

				<table class="form-table" role="presentation">
						<tbody>
						<tr>
								<th scope="row">
										<?php esc_html_e( 'Auto-approve new registrations', 'foodbank-manager' ); ?>
								</th>
								<td>
										<label>
												<input type="checkbox" name="fbm_settings[registration][auto_approve]" value="1" <?php checked( $auto_approve ); ?> />
												<?php esc_html_e( 'Automatically approve and email new registrations', 'foodbank-manager' ); ?>
										</label>
										<p class="description">
												<?php esc_html_e( 'When unchecked, new registrations remain pending until approved in the members dashboard.', 'foodbank-manager' ); ?>
										</p>
								</td>
						</tr>
						</tbody>
				</table>

				<?php submit_button( esc_html__( 'Save settings', 'foodbank-manager' ) ); ?>
		</form>

		<?php
		$uninstall_context = $uninstall;
		if ( is_array( $uninstall_context ) ) {
				include FBM_PATH . 'templates/admin/settings-uninstall.php';
		}
		?>
</div>
