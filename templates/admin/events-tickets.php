<?php
// phpcs:ignoreFile
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="fbm-tickets">
<h2><?php esc_html_e( 'Tickets', 'foodbank-manager' ); ?></h2>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <input type="hidden" name="action" value="fbm_tickets_issue" />
    <input type="hidden" name="event_id" value="<?php echo esc_attr( (string) ( $event['id'] ?? 0 ) ); ?>" />
    <?php wp_nonce_field( 'fbm_tickets_issue' ); ?>
    <p><input type="email" name="recipient" placeholder="recipient@example.com" required />
    <button class="button"><?php esc_html_e( 'Issue', 'foodbank-manager' ); ?></button></p>
</form>
<?php if ( ! empty( $tickets['rows'] ) ) : ?>
<table class="wp-list-table widefat fixed striped">
<thead><tr><th><?php esc_html_e( 'Recipient', 'foodbank-manager' ); ?></th><th><?php esc_html_e( 'Status', 'foodbank-manager' ); ?></th><th><?php esc_html_e( 'Actions', 'foodbank-manager' ); ?></th></tr></thead>
<tbody>
<?php foreach ( $tickets['rows'] as $t ) : ?>
<tr>
<td><?php echo esc_html( $t['recipient'] ); ?></td>
<td><?php echo esc_html( $t['status'] ); ?></td>
<td>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline">
    <input type="hidden" name="action" value="fbm_tickets_send" />
    <input type="hidden" name="id" value="<?php echo esc_attr( (string) $t['id'] ); ?>" />
    <?php wp_nonce_field( 'fbm_tickets_send' ); ?>
    <button class="button"><?php esc_html_e( 'Send', 'foodbank-manager' ); ?></button>
</form>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline">
    <input type="hidden" name="action" value="fbm_tickets_regen" />
    <input type="hidden" name="id" value="<?php echo esc_attr( (string) $t['id'] ); ?>" />
    <?php wp_nonce_field( 'fbm_tickets_regen' ); ?>
    <button class="button"><?php esc_html_e( 'Regenerate', 'foodbank-manager' ); ?></button>
</form>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline" onsubmit="return confirm('<?php echo esc_js( __( 'Are you sure?', 'foodbank-manager' ) ); ?>');">
    <input type="hidden" name="action" value="fbm_tickets_revoke" />
    <input type="hidden" name="id" value="<?php echo esc_attr( (string) $t['id'] ); ?>" />
    <?php wp_nonce_field( 'fbm_tickets_revoke' ); ?>
    <button class="button-link-delete"><?php esc_html_e( 'Revoke', 'foodbank-manager' ); ?></button>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
</div>
