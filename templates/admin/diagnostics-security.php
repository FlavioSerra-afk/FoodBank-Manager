<?php // phpcs:ignoreFile
/**
 * Diagnostics security panel template.
 *
 * @package FoodBankManager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$settings = $settings ?? \FBM\Security\ThrottleSettings::get();
$roles    = $roles ?? get_editable_roles();
?>
<?php echo '<div id="fbm-ui" class="fbm-scope fbm-app">'; ?>
<h2><?php esc_html_e( 'Security & Throttling', 'foodbank-manager' ); ?></h2>
<?php settings_errors( 'fbm_security' ); ?>
<form method="post" action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>">
    <?php if ( function_exists( 'settings_fields' ) ) { settings_fields( 'fbm_security' ); } ?>
    <table class="form-table" role="presentation">
        <tr>
            <th scope="row"><label for="fbm-throttle-window"><?php esc_html_e( 'Window (seconds)', 'foodbank-manager' ); ?></label></th>
            <td><input type="number" min="5" max="300" id="fbm-throttle-window" name="fbm_throttle[window_seconds]" value="<?php echo esc_attr( $settings['window_seconds'] ); ?>" /></td>
        </tr>
        <tr>
            <th scope="row"><label for="fbm-throttle-base"><?php esc_html_e( 'Base limit', 'foodbank-manager' ); ?></label></th>
            <td><input type="number" min="1" max="120" id="fbm-throttle-base" name="fbm_throttle[base_limit]" value="<?php echo esc_attr( $settings['base_limit'] ); ?>" /></td>
        </tr>
    </table>
    <h3><?php esc_html_e( 'Role multipliers', 'foodbank-manager' ); ?></h3>
    <table class="widefat striped" role="presentation">
        <thead><tr><th><?php esc_html_e( 'Role', 'foodbank-manager' ); ?></th><th><?php esc_html_e( 'Multiplier', 'foodbank-manager' ); ?></th></tr></thead>
        <tbody>
        <?php foreach ( $roles as $role => $info ) : $m = $settings['role_multipliers'][ $role ] ?? 1.0; ?>
            <tr>
                <td><?php echo esc_html( $info['name'] ?? $role ); ?></td>
                <td><input type="number" step="0.1" min="0" id="fbm-role-<?php echo esc_attr( $role ); ?>" name="fbm_throttle[role_multipliers][<?php echo esc_attr( $role ); ?>]" value="<?php echo esc_attr( $m ); ?>" /></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php submit_button(); ?>
</form>
<?php echo '</div>'; ?>

