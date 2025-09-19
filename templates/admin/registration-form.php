<?php
/**
 * Registration form admin template.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

use function admin_url;
use function esc_attr;
use function esc_url;
use function esc_html;
use function esc_html__;
use function checked;
use function wp_nonce_field;

$settings     = $data['settings'] ?? array();
$labels       = $data['labels'] ?? array();
$copy         = $data['copy'] ?? array();
$status_value = $data['status'] ?? '';
$message_text = $data['message'] ?? '';
$form_action  = $data['form_action'] ?? 'fbm_registration_form_save';
$nonce_action = $data['nonce_action'] ?? 'fbm_registration_form_save';
$nonce_name   = $data['nonce_name'] ?? 'fbm_registration_form_nonce';

$auto_approve    = isset( $settings['auto_approve'] ) ? (bool) $settings['auto_approve'] : false;
$honeypot        = isset( $settings['honeypot'] ) ? (bool) $settings['honeypot'] : true;
$headline        = isset( $labels['headline'] ) ? (string) $labels['headline'] : '';
$submit_label    = isset( $labels['submit'] ) ? (string) $labels['submit'] : '';
$success_auto    = isset( $copy['success_auto'] ) ? (string) $copy['success_auto'] : '';
$success_pending = isset( $copy['success_pending'] ) ? (string) $copy['success_pending'] : '';

$notice_class = '';
if ( 'success' === $status_value ) {
		$notice_class = 'notice-success';
} elseif ( '' !== $message_text ) {
		$notice_class = 'notice-error';
}
?>
<div class="wrap">
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Registration Form', 'foodbank-manager' ); ?></h1>

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
								<th scope="row"><?php esc_html_e( 'Auto-approve registrations', 'foodbank-manager' ); ?></th>
								<td>
										<label for="fbm_registration_auto">
												<input type="checkbox" id="fbm_registration_auto" name="fbm_registration_form[auto_approve]" value="1"<?php checked( $auto_approve ); ?> />
												<?php esc_html_e( 'Immediately approve registrations and send welcome emails.', 'foodbank-manager' ); ?>
										</label>
										<p class="description"><?php esc_html_e( 'When disabled, registrations remain pending until a manager approves them.', 'foodbank-manager' ); ?></p>
								</td>
						</tr>
						<tr>
								<th scope="row"><?php esc_html_e( 'Enable honeypot anti-spam check', 'foodbank-manager' ); ?></th>
								<td>
										<label for="fbm_registration_honeypot">
												<input type="checkbox" id="fbm_registration_honeypot" name="fbm_registration_form[honeypot]" value="1"<?php checked( $honeypot ); ?> />
												<?php esc_html_e( 'Include a hidden honeypot field to block automated submissions.', 'foodbank-manager' ); ?>
										</label>
										<p class="description"><?php esc_html_e( 'Disable only if legitimate visitors are being blocked by the honeypot trap.', 'foodbank-manager' ); ?></p>
								</td>
						</tr>
						<tr>
								<th scope="row">
										<label for="fbm_registration_headline"><?php esc_html_e( 'Form headline', 'foodbank-manager' ); ?></label>
								</th>
								<td>
										<input type="text" id="fbm_registration_headline" name="fbm_registration_form[headline]" class="regular-text" value="<?php echo esc_attr( $headline ); ?>" />
										<p class="description"><?php esc_html_e( 'Displayed above the registration form.', 'foodbank-manager' ); ?></p>
								</td>
						</tr>
						<tr>
								<th scope="row">
										<label for="fbm_registration_submit"><?php esc_html_e( 'Submit button label', 'foodbank-manager' ); ?></label>
								</th>
								<td>
										<input type="text" id="fbm_registration_submit" name="fbm_registration_form[submit]" class="regular-text" value="<?php echo esc_attr( $submit_label ); ?>" />
								</td>
						</tr>
						<tr>
								<th scope="row">
										<label for="fbm_registration_success_auto"><?php esc_html_e( 'Success message (auto-approve enabled)', 'foodbank-manager' ); ?></label>
								</th>
								<td>
										<textarea id="fbm_registration_success_auto" name="fbm_registration_form[success_auto]" rows="4" class="large-text">
										<?php
										if ( function_exists( 'esc_textarea' ) ) {
												echo esc_textarea( $success_auto );
										} else {
												echo esc_html( $success_auto );
										}
										?>
										</textarea>
										<p class="description"><?php esc_html_e( 'Shown after successful submissions when registrations are auto-approved. Basic formatting is allowed.', 'foodbank-manager' ); ?></p>
								</td>
						</tr>
						<tr>
								<th scope="row">
										<label for="fbm_registration_success_pending"><?php esc_html_e( 'Success message (manual approval)', 'foodbank-manager' ); ?></label>
								</th>
								<td>
										<textarea id="fbm_registration_success_pending" name="fbm_registration_form[success_pending]" rows="4" class="large-text">
										<?php
										if ( function_exists( 'esc_textarea' ) ) {
												echo esc_textarea( $success_pending );
										} else {
												echo esc_html( $success_pending );
										}
										?>
										</textarea>
										<p class="description"><?php esc_html_e( 'Displayed when registrations stay pending for manual review.', 'foodbank-manager' ); ?></p>
								</td>
						</tr>
						</tbody>
				</table>

				<p class="submit">
						<button type="submit" class="button button-primary"><?php esc_html_e( 'Save registration form settings', 'foodbank-manager' ); ?></button>
				</p>
		</form>

		<h2><?php esc_html_e( 'Shortcode', 'foodbank-manager' ); ?></h2>
		<p><?php esc_html_e( 'Add this shortcode to any page to publish the registration form:', 'foodbank-manager' ); ?></p>
		<code>[fbm_registration_form]</code>
</div>
