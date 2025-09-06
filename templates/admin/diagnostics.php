<?php // phpcs:ignoreFile
/**
 * Diagnostics page template.
 *
 * @package FoodBankManager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! current_user_can( 'fb_manage_diagnostics' ) ) {
    wp_die( esc_html__( 'You do not have permission to access this page.', 'foodbank-manager' ) );
}

$php_version    = PHP_VERSION;
$wp_version     = get_bloginfo( 'version' );
$fbm_version    = defined( 'FBM_VERSION' ) ? FBM_VERSION : 'dev';
$sodium         = extension_loaded( 'sodium' ) ? 'native' : ( class_exists( '\\ParagonIE_Sodium_Compat' ) ? 'polyfill' : 'missing' );
$kek_defined    = defined( 'FBM_KEK_BASE64' ) && FBM_KEK_BASE64 !== '';
    $transport      = function_exists( 'wp_mail' ) ? 'wp_mail' : 'none';
    $notice        = isset( $_GET['notice'] ) ? sanitize_key( wp_unslash( $_GET['notice'] ) ) : '';
$missing_slugs = array();
$found_slugs   = array();
foreach ( \FoodBankManager\Admin\Menu::slugs() as $slug ) {
    if ( menu_page_url( $slug, false ) === false ) {
        $missing_slugs[] = $slug;
    } else {
        $found_slugs[] = $slug;
    }
}
$slugs_ok   = empty( $missing_slugs );
$screen     = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
$gating_ok  = \FoodBankManager\Core\Screen::is_fbm_screen();
$rows       = $rows ?? array();
?>
<div class="wrap fbm-admin">
<?php \FBM\Core\Trace::mark( 'admin:diagnostics' ); ?>
    <h1><?php esc_html_e( 'Diagnostics', 'foodbank-manager' ); ?></h1>
      <?php if ( ! empty( $render_ok ) ) : ?>
          <span class="fbm-badge fbm-badge--ok">RenderOnce OK</span>
      <?php else : ?>
          <span class="fbm-badge fbm-badge--warn">RenderOnce duplicates</span>
      <?php endif; ?>
      <?php settings_errors( 'fbm_diagnostics' ); ?>
      <?php if ( 'sent' === $notice ) : ?>
          <div class="notice notice-success"><p><?php esc_html_e( 'Test email sent.', 'foodbank-manager' ); ?></p></div>
      <?php elseif ( 'error' === $notice ) : ?>
          <div class="notice notice-error"><p><?php esc_html_e( 'Action failed.', 'foodbank-manager' ); ?></p></div>
      <?php endif; ?>
    <h2><?php esc_html_e( 'Crypto', 'foodbank-manager' ); ?></h2>
    <ul>
        <li><?php echo esc_html( $kek_defined ? '✅ key' : '⚠️ key' ); ?></li>
        <li><?php echo esc_html( 'sodium: ' . $sodium ); ?></li>
    </ul>
    <h2><?php esc_html_e( 'SMTP', 'foodbank-manager' ); ?></h2>
    <p><?php echo esc_html( sprintf( __( 'Transport: %s', 'foodbank-manager' ), $transport ) ); ?></p>
    <form method="post" action="">
        <?php wp_nonce_field( 'fbm_diag_mail_test', '_fbm_nonce' ); ?>
        <input type="hidden" name="fbm_action" value="mail_test" />
        <p><button type="submit" class="button"><?php esc_html_e( 'Send test email', 'foodbank-manager' ); ?></button></p>
    </form>
    <h2><?php esc_html_e( 'Environment', 'foodbank-manager' ); ?></h2>
    <ul>
        <li><?php echo esc_html( 'PHP ' . $php_version ); ?></li>
        <li><?php echo esc_html( 'WP ' . $wp_version ); ?></li>
        <li><?php echo esc_html( 'FBM ' . $fbm_version ); ?></li>
        <li><?php echo esc_html( 'Last successful FBM boot (plugins_loaded): ' . $boot_status ); ?></li>
        <li><?php echo esc_html( 'Admin notices rendered this request: ' . $notices_render_count ); ?></li>
    </ul>
    <h2><?php esc_html_e( 'Install Health', 'foodbank-manager' ); ?></h2>
    <p><?php echo esc_html( sprintf( __( 'Canonical slug: %s', 'foodbank-manager' ), $canonical_slug ) ); ?></p>
    <p><?php echo esc_html( sprintf( __( 'Other copies: %d', 'foodbank-manager' ), count( $dup_plugins ) ) ); ?></p>
    <p><?php echo esc_html( sprintf( __( 'Last consolidation: %s (deactivated %d, deleted %d)', 'foodbank-manager' ), $last_consolidation['ts'] ? gmdate( 'Y-m-d H:i', $last_consolidation['ts'] ) : __( 'never', 'foodbank-manager' ), $last_consolidation['deactivated'], $last_consolidation['deleted'] ) ); ?></p>
    <?php if ( ! empty( $dup_plugins ) ) : ?>
    <ul>
        <?php foreach ( $dup_plugins as $b ) : ?>
        <li><?php echo esc_html( $b['dir'] . ' (' . $b['version'] . ')' ); ?></li>
        <?php endforeach; ?>
    </ul>
    <?php $action_url = add_query_arg( 'action', 'fbm_consolidate_plugins', admin_url( 'admin-post.php' ) ); ?>
    <form method="post" action="<?php echo esc_url( $action_url ); ?>">
        <?php wp_nonce_field( 'fbm_consolidate' ); ?>
        <p><button type="submit" class="button button-primary"><?php esc_html_e( 'Consolidate duplicates', 'foodbank-manager' ); ?></button></p>
    </form>
    <?php $deactivate_url = add_query_arg( 'action', 'fbm_deactivate_duplicates', admin_url( 'admin-post.php' ) ); ?>
    <form method="post" action="<?php echo esc_url( $deactivate_url ); ?>">
        <?php wp_nonce_field( 'fbm_deactivate' ); ?>
        <p><button type="submit" class="button"><?php esc_html_e( 'Deactivate duplicates', 'foodbank-manager' ); ?></button></p>
    </form>
    <?php endif; ?>
    <h2><?php esc_html_e( 'Quick Checks', 'foodbank-manager' ); ?></h2>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Check', 'foodbank-manager' ); ?></th>
                <th><?php esc_html_e( 'Value', 'foodbank-manager' ); ?></th>
                <th><?php esc_html_e( 'Status', 'foodbank-manager' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $rows as $row ) : ?>
            <tr>
                <td><?php echo esc_html( $row[0] ); ?></td>
                <td><?php echo esc_html( $row[1] ); ?></td>
                <td><?php echo 'ok' === $row[2] ? '✅' : '⚠️'; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if ( ! empty( $counts ) ) : ?>
    <h2><?php esc_html_e( 'Render counts (this request)', 'foodbank-manager' ); ?></h2>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Screen', 'foodbank-manager' ); ?></th>
                <th><?php esc_html_e( 'Count', 'foodbank-manager' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $counts as $k => $v ) : ?>
            <tr>
                <td><?php echo esc_html( $k ); ?></td>
                <td><?php echo esc_html( (string) $v ); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if ( ! empty( $dupes ) ) : ?>
    <p><?php esc_html_e( 'Duplicates:', 'foodbank-manager' ); ?> <?php echo esc_html( implode( ', ', array_keys( $dupes ) ) ); ?></p>
    <?php endif; ?>
    <?php endif; ?>
      <h2><?php esc_html_e( 'Menu Visibility', 'foodbank-manager' ); ?></h2>
      <p><?php esc_html_e( 'FBM caps held by current user:', 'foodbank-manager' ); ?> <strong><?php echo esc_html( $caps_count ); ?></strong></p>
      <form method="post" action="">
          <input type="hidden" name="fbm_action" value="fbm_repair_caps" />
          <?php wp_nonce_field( 'fbm_repair_caps' ); ?>
          <?php submit_button( __( 'Repair caps', 'foodbank-manager' ), 'secondary', '', false ); ?>
      </form>
    <h2><?php esc_html_e( 'Cron', 'foodbank-manager' ); ?></h2>
    <?php $cron = \FoodBankManager\Admin\DiagnosticsPage::cron_status(); ?>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Event', 'foodbank-manager' ); ?></th>
                <th><?php esc_html_e( 'Schedule', 'foodbank-manager' ); ?></th>
                <th><?php esc_html_e( 'Last run', 'foodbank-manager' ); ?></th>
                <th><?php esc_html_e( 'Next run', 'foodbank-manager' ); ?></th>
                <th><?php esc_html_e( 'Status', 'foodbank-manager' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $cron as $row ) : ?>
            <tr>
                <td><?php echo esc_html( $row['hook'] ); ?></td>
                <td><?php echo esc_html( $row['schedule'] ); ?></td>
                <td><?php echo $row['last_run'] ? esc_html( gmdate( 'Y-m-d H:i', $row['last_run'] ) ) : '&mdash;'; ?></td>
                <td><?php echo $row['next_run'] ? esc_html( gmdate( 'Y-m-d H:i', $row['next_run'] ) ) : '&mdash;'; ?></td>
                <td><?php echo $row['overdue'] ? '⚠️' : '✅'; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
