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
// phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom plugin capability.
if ( ! current_user_can( 'fb_manage_forms' ) ) {
				wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ), '', array( 'response' => 403 ) );
}
?>
<?php echo '<div id="fbm-ui" class="fbm-scope fbm-app">'; ?>
<div class="wrap fbm-admin">
<?php \FBM\Core\Trace::mark( 'admin:forms' ); ?>
				<h1><?php \esc_html_e( 'Forms', 'foodbank-manager' ); ?></h1>
		<table class="wp-list-table widefat fixed striped">
				<thead>
						<tr>
								<th><?php \esc_html_e( 'Preset', 'foodbank-manager' ); ?></th>
								<th><?php \esc_html_e( 'Fields', 'foodbank-manager' ); ?></th>
								<th><?php \esc_html_e( 'Shortcode', 'foodbank-manager' ); ?></th>
						</tr>
				</thead>
				<tbody>
				<?php foreach ( $presets as $preset_id => $fields ) : ?>
						<tr>
								<td><?php echo esc_html( (string) $preset_id ); ?></td>
								<td>
										<?php foreach ( $fields as $field ) : ?>
												<div>
														<code><?php echo esc_html( (string) $field['type'] ); ?></code>
														<?php echo esc_html( (string) $field['label'] ); ?>
														<?php echo ! empty( $field['required'] ) ? ' *' : ''; ?>
												</div>
										<?php endforeach; ?>
								</td>
								<td>
										<?php
										$code = '[fbm_form preset="' . $preset_id . '"]';
										?>
										<input type="text" readonly value="<?php echo esc_attr( $code ); ?>" class="regular-text" />
										<button type="button"
												class="button fbm-copy"
												data-code="<?php echo esc_attr( $code ); ?>">
												<?php \esc_html_e( 'Copy', 'foodbank-manager' ); ?>
										</button>
								</td>
						</tr>
				<?php endforeach; ?>
				</tbody>
		</table>
<script>
document.addEventListener('DOMContentLoaded', function () {
const buttons = document.querySelectorAll('.fbm-copy');
buttons.forEach(function (btn) {
btn.addEventListener('click', function () {
navigator.clipboard.writeText(btn.dataset.code);
btn.textContent = '<?php echo esc_js( __( 'Copied', 'foodbank-manager' ) ); ?>';
});
});
});
</script>
</div>
<?php echo '</div>'; ?>
