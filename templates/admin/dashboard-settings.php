<?php
/**
 * Staff dashboard admin template.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

use function admin_url;
use function esc_attr;
use function esc_html;
use function esc_html__;
use function esc_url;
use function checked;
use function wp_nonce_field;

$settings     = $data['settings'] ?? array();
$status_value = $data['status'] ?? '';
$message_text = $data['message'] ?? '';
$form_action  = $data['form_action'] ?? 'fbm_staff_dashboard_save';
$nonce_action = $data['nonce_action'] ?? 'fbm_staff_dashboard_save';
$nonce_name   = $data['nonce_name'] ?? 'fbm_staff_dashboard_nonce';

$show_counters  = isset( $settings['show_counters'] ) ? (bool) $settings['show_counters'] : true;
$allow_override = isset( $settings['allow_override'] ) ? (bool) $settings['allow_override'] : true;
$scanner        = $settings['scanner'] ?? array();
$prefer_torch   = isset( $scanner['prefer_torch'] ) ? (bool) $scanner['prefer_torch'] : false;
$roi            = isset( $scanner['roi'] ) ? (int) $scanner['roi'] : 80;
$debounce       = isset( $scanner['decode_debounce'] ) ? (int) $scanner['decode_debounce'] : 1200;

$notice_class = '';
if ( 'success' === $status_value ) {
		$notice_class = 'notice-success';
} elseif ( '' !== $message_text ) {
		$notice_class = 'notice-error';
}
?>
<div class="wrap">
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Staff Dashboard', 'foodbank-manager' ); ?></h1>

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
								<th scope="row"><?php esc_html_e( 'Show counters', 'foodbank-manager' ); ?></th>
								<td>
										<label for="fbm_staff_dashboard_show_counters">
												<input type="checkbox" id="fbm_staff_dashboard_show_counters" name="fbm_staff_dashboard[show_counters]" value="1"<?php checked( $show_counters ); ?> />
												<?php esc_html_e( 'Display the today counters for recorded collections, duplicates, and overrides.', 'foodbank-manager' ); ?>
										</label>
								</td>
						</tr>
						<tr>
								<th scope="row"><?php esc_html_e( 'Allow override prompts', 'foodbank-manager' ); ?></th>
								<td>
										<label for="fbm_staff_dashboard_allow_override">
												<input type="checkbox" id="fbm_staff_dashboard_allow_override" name="fbm_staff_dashboard[allow_override]" value="1"<?php checked( $allow_override ); ?> />
												<?php esc_html_e( 'Show the manager override prompt when a member collected within the last week.', 'foodbank-manager' ); ?>
										</label>
										<p class="description"><?php esc_html_e( 'Disable to prevent override prompts from appearing in the dashboard UI.', 'foodbank-manager' ); ?></p>
								</td>
						</tr>
						<tr>
								<th scope="row"><?php esc_html_e( 'Attempt to enable torch', 'foodbank-manager' ); ?></th>
								<td>
										<label for="fbm_staff_dashboard_prefer_torch">
												<input type="checkbox" id="fbm_staff_dashboard_prefer_torch" name="fbm_staff_dashboard[scanner][prefer_torch]" value="1"<?php checked( $prefer_torch ); ?> />
												<?php esc_html_e( 'When supported, switch on the device torch when starting the scanner.', 'foodbank-manager' ); ?>
										</label>
								</td>
						</tr>
						<tr>
								<th scope="row">
										<label for="fbm_staff_dashboard_roi"><?php esc_html_e( 'Scanner region size (%)', 'foodbank-manager' ); ?></label>
								</th>
								<td>
										<input type="number" id="fbm_staff_dashboard_roi" name="fbm_staff_dashboard[scanner][roi]" value="<?php echo esc_attr( (string) $roi ); ?>" min="30" max="100" />
										<p class="description"><?php esc_html_e( 'Controls the size of the focus region overlay. Higher values widen the active scanning area.', 'foodbank-manager' ); ?></p>
								</td>
						</tr>
						<tr>
								<th scope="row">
										<label for="fbm_staff_dashboard_debounce"><?php esc_html_e( 'Decode debounce (ms)', 'foodbank-manager' ); ?></label>
								</th>
								<td>
										<input type="number" id="fbm_staff_dashboard_debounce" name="fbm_staff_dashboard[scanner][decode_debounce]" value="<?php echo esc_attr( (string) $debounce ); ?>" min="0" max="5000" step="50" />
										<p class="description"><?php esc_html_e( 'Minimum delay between successful scans. Increase if duplicate scans fire too quickly.', 'foodbank-manager' ); ?></p>
								</td>
						</tr>
						</tbody>
				</table>

				<p class="submit">
						<button type="submit" class="button button-primary"><?php esc_html_e( 'Save staff dashboard settings', 'foodbank-manager' ); ?></button>
				</p>
		</form>

		<h2><?php esc_html_e( 'Shortcode', 'foodbank-manager' ); ?></h2>
		<p><?php esc_html_e( 'Embed the staff dashboard using this shortcode:', 'foodbank-manager' ); ?></p>
		<code>[fbm_staff_dashboard]</code>
</div>
