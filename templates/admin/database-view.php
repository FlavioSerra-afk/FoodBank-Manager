<?php
/**
 * Database entry view template.
 *
 * @package FoodBankManager
 * @since 0.1.1
 */

use FoodBankManager\Security\Helpers;
use FoodBankManager\Security\Crypto;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$data = json_decode( (string) ( $entry['data_json'] ?? '' ), true );
if ( ! is_array( $data ) ) {
	$data = array();
}
$pii = Crypto::decryptSensitive( (string) ( $entry['pii_encrypted_blob'] ?? '' ) );
if ( ! $can_sensitive ) {
	$pii['email']     = Helpers::mask_email( $pii['email'] ?? '' );
	$data['postcode'] = Helpers::mask_postcode( $data['postcode'] ?? '' );
}
?>
<div class="wrap">
<h1>
	<?php esc_html_e( 'Entry', 'foodbank-manager' ); ?> #<?php echo esc_html( (string) $entry['id'] ); ?>
</h1>
<p>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=fbm-database' ) ); ?>">
		&larr; <?php esc_html_e( 'Back to list', 'foodbank-manager' ); ?>
	</a>
</p>
<table class="form-table">
	<tr>
		<th><?php esc_html_e( 'Created', 'foodbank-manager' ); ?></th>
		<td><?php echo esc_html( get_date_from_gmt( (string) $entry['created_at'] ) ); ?></td>
	</tr>
	<tr>
		<th><?php esc_html_e( 'Status', 'foodbank-manager' ); ?></th>
		<td><?php echo esc_html( (string) $entry['status'] ); ?></td>
	</tr>
	<tr>
		<th><?php esc_html_e( 'Consent time', 'foodbank-manager' ); ?></th>
		<td><?php echo esc_html( (string) $entry['consent_timestamp'] ); ?></td>
	</tr>
</table>
<h2><?php esc_html_e( 'Fields', 'foodbank-manager' ); ?></h2>
<table class="widefat fixed">
<?php foreach ( $data as $k => $v ) : ?>
<tr>
	<th><?php echo esc_html( $k ); ?></th>
	<td><?php echo esc_html( is_array( $v ) ? wp_json_encode( $v ) : (string) $v ); ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php if ( $can_sensitive ) : ?>
<h2><?php esc_html_e( 'Sensitive', 'foodbank-manager' ); ?></h2>
<table class="widefat fixed">
	<?php foreach ( $pii as $k => $v ) : ?>
<tr>
	<th><?php echo esc_html( $k ); ?></th>
	<td><?php echo esc_html( is_array( $v ) ? wp_json_encode( $v ) : (string) $v ); ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php else : ?>
<p><?php esc_html_e( 'Sensitive fields are masked.', 'foodbank-manager' ); ?></p>
<?php endif; ?>
<?php if ( ! empty( $entry['files'] ) ) : ?>
<h2><?php esc_html_e( 'Files', 'foodbank-manager' ); ?></h2>
<ul>
	<?php foreach ( $entry['files'] as $f ) : ?>
<li><?php echo esc_html( $f['original_name'] . ' (' . $f['mime'] . ', ' . size_format( (int) $f['size_bytes'] ) . ')' ); ?></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
<p>
<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=fbm-database' ) ); ?>">
	<?php esc_html_e( 'Back', 'foodbank-manager' ); ?>
</a>
<?php if ( current_user_can( 'fb_manage_database' ) ) : // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability. ?>
<form method="post" style="display:inline">
				<input type="hidden" name="fbm_action" value="export_single" />
				<input type="hidden" name="id" value="<?php echo esc_attr( (string) $entry['id'] ); ?>" />
				<?php wp_nonce_field( 'fbm_export_single_' . $entry['id'], 'fbm_nonce' ); ?>
				<button class="button"><?php esc_html_e( 'CSV', 'foodbank-manager' ); ?></button>
</form>
<?php endif; ?>
<?php if ( current_user_can( 'fb_manage_database' ) ) : // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability. ?>
<form method="post" style="display:inline" onsubmit="return confirm('<?php echo esc_js( __( 'Are you sure?', 'foodbank-manager' ) ); ?>');">
				<input type="hidden" name="fbm_action" value="delete_entry" />
				<input type="hidden" name="id" value="<?php echo esc_attr( (string) $entry['id'] ); ?>" />
				<?php wp_nonce_field( 'fbm_delete_entry_' . $entry['id'], 'fbm_nonce' ); ?>
				<button class="button"><?php esc_html_e( 'Delete', 'foodbank-manager' ); ?></button>
</form>
<?php endif; ?>
</p>
</div>
