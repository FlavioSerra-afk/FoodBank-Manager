<?php
// phpcs:ignoreFile


if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap fbm-admin">
<?php \FBM\Core\Trace::mark( 'admin:events' ); ?>
<h1><?php esc_html_e( 'Events', 'foodbank-manager' ); ?></h1>
<?php if ( 'event_saved' === $notice ) : ?>
<div class="notice notice-success"><p><?php esc_html_e( 'Event saved.', 'foodbank-manager' ); ?></p></div>
<?php elseif ( 'event_deleted' === $notice ) : ?>
<div class="notice notice-success"><p><?php esc_html_e( 'Event deleted.', 'foodbank-manager' ); ?></p></div>
<?php endif; ?>
<form method="get" class="fbm-filters">
    <input type="hidden" name="page" value="fbm_events" />
    <input type="search" name="q" value="<?php echo esc_attr( $filters['q'] ); ?>" placeholder="<?php esc_attr_e( 'Search', 'foodbank-manager' ); ?>" />
    <select name="status">
        <option value="" <?php selected( '', $filters['status'] ); ?>><?php esc_html_e( 'All statuses', 'foodbank-manager' ); ?></option>
        <option value="active" <?php selected( 'active', $filters['status'] ); ?>><?php esc_html_e( 'Active', 'foodbank-manager' ); ?></option>
        <option value="cancelled" <?php selected( 'cancelled', $filters['status'] ); ?>><?php esc_html_e( 'Cancelled', 'foodbank-manager' ); ?></option>
    </select>
    <label><?php esc_html_e( 'From', 'foodbank-manager' ); ?> <input type="date" name="from" value="<?php echo esc_attr( $filters['from'] ); ?>" /></label>
    <label><?php esc_html_e( 'To', 'foodbank-manager' ); ?> <input type="date" name="to" value="<?php echo esc_attr( $filters['to'] ); ?>" /></label>
    <button class="button"><?php esc_html_e( 'Filter', 'foodbank-manager' ); ?></button>
</form>
<table class="wp-list-table widefat fixed striped">
    <thead>
        <tr>
            <th><?php esc_html_e( 'Title', 'foodbank-manager' ); ?></th>
            <th><?php esc_html_e( 'Starts', 'foodbank-manager' ); ?></th>
            <th><?php esc_html_e( 'Ends', 'foodbank-manager' ); ?></th>
            <th><?php esc_html_e( 'Status', 'foodbank-manager' ); ?></th>
            <th><?php esc_html_e( 'Actions', 'foodbank-manager' ); ?></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ( $data['rows'] as $row ) : ?>
        <tr>
            <td><?php echo esc_html( $row['title'] ); ?></td>
            <td><?php echo esc_html( $row['starts_at'] ); ?></td>
            <td><?php echo esc_html( $row['ends_at'] ); ?></td>
            <td><?php echo esc_html( $row['status'] ); ?></td>
            <td>
                <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'fbm_events', 'id' => $row['id'] ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'Edit', 'foodbank-manager' ); ?></a>
                <form method="post" style="display:inline">
                    <input type="hidden" name="fbm_action" value="delete" />
                    <input type="hidden" name="id" value="<?php echo esc_attr( (string) $row['id'] ); ?>" />
                    <?php wp_nonce_field( 'fbm_events_delete_' . $row['id'], 'fbm_nonce' ); ?>
                    <button class="button-link-delete" onclick="return confirm('<?php echo esc_attr( __( 'Are you sure?', 'foodbank-manager' ) ); ?>')"><?php esc_html_e( 'Delete', 'foodbank-manager' ); ?></button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php $editing = $event ? true : false; ?>
<h2><?php echo $editing ? esc_html__( 'Edit Event', 'foodbank-manager' ) : esc_html__( 'Add Event', 'foodbank-manager' ); ?></h2>
<form method="post">
    <input type="hidden" name="fbm_action" value="save" />
    <?php if ( $editing ) : ?>
    <input type="hidden" name="id" value="<?php echo esc_attr( (string) $event['id'] ); ?>" />
    <?php endif; ?>
    <?php wp_nonce_field( 'fbm_events_save', 'fbm_nonce' ); ?>
    <p><label><?php esc_html_e( 'Title', 'foodbank-manager' ); ?> <input type="text" name="title" value="<?php echo esc_attr( $event['title'] ?? '' ); ?>" required /></label></p>
    <p><label><?php esc_html_e( 'Starts', 'foodbank-manager' ); ?> <input type="datetime-local" name="starts_at" value="<?php echo esc_attr( $event['starts_at'] ?? '' ); ?>" required /></label></p>
    <p><label><?php esc_html_e( 'Ends', 'foodbank-manager' ); ?> <input type="datetime-local" name="ends_at" value="<?php echo esc_attr( $event['ends_at'] ?? '' ); ?>" required /></label></p>
    <p><label><?php esc_html_e( 'Location', 'foodbank-manager' ); ?> <input type="text" name="location" value="<?php echo esc_attr( $event['location'] ?? '' ); ?>" /></label></p>
    <p><label><?php esc_html_e( 'Capacity', 'foodbank-manager' ); ?> <input type="number" name="capacity" min="0" value="<?php echo isset( $event['capacity'] ) ? esc_attr( (string) $event['capacity'] ) : ''; ?>" /></label></p>
    <p><label><?php esc_html_e( 'Notes', 'foodbank-manager' ); ?> <textarea name="notes" rows="3"><?php echo isset( $event['notes'] ) ? esc_html( $event['notes'] ) : ''; ?></textarea></label></p>
    <p><label><?php esc_html_e( 'Status', 'foodbank-manager' ); ?>
        <select name="status">
            <option value="active" <?php selected( $event['status'] ?? 'active', 'active' ); ?>><?php esc_html_e( 'Active', 'foodbank-manager' ); ?></option>
            <option value="cancelled" <?php selected( $event['status'] ?? 'active', 'cancelled' ); ?>><?php esc_html_e( 'Cancelled', 'foodbank-manager' ); ?></option>
        </select>
    </label></p>
    <p><button class="button button-primary"><?php esc_html_e( 'Save', 'foodbank-manager' ); ?></button></p>
</form>
<?php if ( $editing ) { require FBM_PATH . 'templates/admin/events-tickets.php'; } ?>
</div>
