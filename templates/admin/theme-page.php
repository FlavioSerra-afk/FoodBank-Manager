<?php
/**
 * Theme settings admin template.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

use FoodBankManager\Admin\ThemePage;
use function admin_url;
use function esc_attr;
use function esc_html;
use function esc_html_e;
use function esc_url;
use function sanitize_key;
use function sanitize_text_field;
use function wp_nonce_field;

$status_value = '';
$message_text = '';

if ( isset( $data['status'] ) ) {
	$status_value = sanitize_key( (string) $data['status'] );
}

if ( isset( $data['message'] ) ) {
	$message_text = sanitize_text_field( (string) $data['message'] );
}

$notice_class = '';

if ( 'success' === $status_value ) {
	$notice_class = 'notice-success';
} elseif ( '' !== $message_text ) {
	$notice_class = 'notice-error';
}

?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Food Bank Theme', 'foodbank-manager' ); ?></h1>

<?php if ( '' !== $message_text ) : ?>
<div class="notice <?php echo esc_attr( $notice_class ); ?> is-dismissible">
<p><?php echo esc_html( $message_text ); ?></p>
</div>
<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<?php wp_nonce_field( 'fbm_theme_save', 'fbm_theme_nonce' ); ?>
	<input type="hidden" name="action" value="fbm_theme_save" />

	<h2 class="title"><?php esc_html_e( 'Display', 'foodbank-manager' ); ?></h2>
	<?php ThemePage::render_style_section(); ?>
	<table class="form-table" role="presentation">
	<tbody>
	<tr>
	<th scope="row">
	<label for="fbm_theme_style"><?php esc_html_e( 'Theme style', 'foodbank-manager' ); ?></label>
	</th>
	<td><?php ThemePage::render_style_field(); ?></td>
	</tr>
	<tr>
	<th scope="row">
	<label for="fbm_theme_preset"><?php esc_html_e( 'Preset', 'foodbank-manager' ); ?></label>
	</th>
	<td><?php ThemePage::render_preset_field(); ?></td>
	</tr>
	<tr>
	<th scope="row">
	<label for="fbm_theme_accent"><?php esc_html_e( 'Accent color', 'foodbank-manager' ); ?></label>
	</th>
	<td><?php ThemePage::render_accent_field(); ?></td>
	</tr>
	</tbody>
	</table>

	<h2 class="title"><?php esc_html_e( 'Glass effects', 'foodbank-manager' ); ?></h2>
	<?php ThemePage::render_glass_section(); ?>
	<table class="form-table" role="presentation">
	<tbody>
	<tr>
	<th scope="row">
	<label for="fbm_theme_glass_alpha"><?php esc_html_e( 'Alpha', 'foodbank-manager' ); ?></label>
	</th>
	<td><?php ThemePage::render_glass_alpha_field(); ?></td>
	</tr>
	<tr>
	<th scope="row">
	<label for="fbm_theme_glass_blur"><?php esc_html_e( 'Blur', 'foodbank-manager' ); ?></label>
	</th>
	<td><?php ThemePage::render_glass_blur_field(); ?></td>
	</tr>
	<tr>
	<th scope="row">
	<label for="fbm_theme_glass_elev"><?php esc_html_e( 'Elevation', 'foodbank-manager' ); ?></label>
	</th>
	<td><?php ThemePage::render_glass_elev_field(); ?></td>
	</tr>
	<tr>
	<th scope="row">
	<label for="fbm_theme_glass_radius"><?php esc_html_e( 'Radius', 'foodbank-manager' ); ?></label>
	</th>
	<td><?php ThemePage::render_glass_radius_field(); ?></td>
	</tr>
	<tr>
	<th scope="row">
	<label for="fbm_theme_glass_border"><?php esc_html_e( 'Border width', 'foodbank-manager' ); ?></label>
	</th>
	<td><?php ThemePage::render_glass_border_field(); ?></td>
	</tr>
	</tbody>
	</table>

	<p class="submit">
	<button type="submit" class="button button-primary"><?php esc_html_e( 'Save theme settings', 'foodbank-manager' ); ?></button>
	</p>
	</form>
</div>
