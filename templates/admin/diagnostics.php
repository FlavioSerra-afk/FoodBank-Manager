<?php // phpcs:ignoreFile
/**
 * Diagnostics page template.
 *
 * @package FoodBankManager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$php_version    = PHP_VERSION;
$wp_version     = get_bloginfo( 'version' );
$fbm_version    = defined( 'FBM_VERSION' ) ? FBM_VERSION : 'dev';
$sodium         = extension_loaded( 'sodium' ) ? 'native' : ( class_exists( '\\ParagonIE_Sodium_Compat' ) ? 'polyfill' : 'missing' );
$kek_defined    = defined( 'FBM_KEK_BASE64' ) && FBM_KEK_BASE64 !== '';
$notice         = $notice ?? '';
$notices_render_count = $notices_render_count ?? 0;
$boot_status          = $boot_status ?? '';
$caps_count           = $caps_count ?? '';
$install_scan         = $install_scan ?? array( 'canonical' => '', 'duplicates' => array() );
$last_consolidation   = $last_consolidation ?? array();
$last_activation_consolidation = $last_activation_consolidation ?? array();
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
      <?php elseif ( 'retried' === $notice ) : ?>
          <div class="notice notice-success"><p><?php esc_html_e( 'Email retried.', 'foodbank-manager' ); ?></p></div>
      <?php endif; ?>
    <h2><?php esc_html_e( 'Crypto', 'foodbank-manager' ); ?></h2>
    <ul>
        <li><?php echo esc_html( $kek_defined ? '✅ key' : '⚠️ key' ); ?></li>
        <li><?php echo esc_html( 'sodium: ' . $sodium ); ?></li>
    </ul>
    <h2><?php esc_html_e( 'SMTP', 'foodbank-manager' ); ?></h2>
    <ul>
        <li><?php echo esc_html( sprintf(
            /* translators: %s: mailer name */
            __( 'Mailer: %s', 'foodbank-manager' ),
            $smtp['mailer'] ?? ''
        ) ); ?></li>
        <li><?php echo esc_html( sprintf(
            /* translators: %s: SMTP host */
            __( 'Host: %s', 'foodbank-manager' ),
            $smtp['host'] ?? ''
        ) ); ?></li>
        <li><?php echo esc_html( sprintf(
            /* translators: %s: SMTP port */
            __( 'Port: %s', 'foodbank-manager' ),
            $smtp['port'] ?? ''
        ) ); ?></li>
        <li><?php echo esc_html( sprintf(
            /* translators: %s: SMTP encryption */
            __( 'Encryption: %s', 'foodbank-manager' ),
            $smtp['encryption'] ?? ''
        ) ); ?></li>
        <li><?php echo esc_html( sprintf(
            /* translators: %s: SMTP authentication method */
            __( 'Auth: %s', 'foodbank-manager' ),
            $smtp['auth'] ?? ''
        ) ); ?></li>
        <li><?php echo esc_html( sprintf(
            /* translators: %s: email address */
            __( 'Test recipient: %s', 'foodbank-manager' ),
            $test_to
        ) ); ?></li>
    </ul>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="fbm_diag_mail_test" />
        <?php wp_nonce_field( 'fbm_diag_mail_test', '_fbm_nonce' ); ?>
        <p><button type="submit" class="button"><?php esc_html_e( 'Send test email', 'foodbank-manager' ); ?></button></p>
    </form>
    <h2><?php esc_html_e( 'Recent Mail Failures', 'foodbank-manager' ); ?></h2>
    <?php if ( ! empty( $failures ) ) : ?>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php esc_html_e( 'To', 'foodbank-manager' ); ?></th>
                <th><?php esc_html_e( 'Subject', 'foodbank-manager' ); ?></th>
                <th><?php esc_html_e( 'Error', 'foodbank-manager' ); ?></th>
                <th><?php esc_html_e( 'Actions', 'foodbank-manager' ); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ( $failures as $i => $f ) : ?>
            <tr>
                <td><?php echo esc_html( is_array( $f['to'] ) ? implode( ', ', array_map( 'sanitize_email', $f['to'] ) ) : sanitize_email( (string) $f['to'] ) ); ?></td>
                <td><?php echo esc_html( $f['subject'] ); ?></td>
                <td><?php echo esc_html( $f['error'] ); ?></td>
                <td>
                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                        <input type="hidden" name="action" value="fbm_diag_mail_retry" />
                        <input type="hidden" name="index" value="<?php echo esc_attr( (string) $i ); ?>" />
                        <?php wp_nonce_field( 'fbm_diag_mail_retry', '_fbm_nonce' ); ?>
                        <button type="submit" class="button"><?php esc_html_e( 'Retry', 'foodbank-manager' ); ?></button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else : ?>
        <p><?php esc_html_e( 'No mail failures logged.', 'foodbank-manager' ); ?></p>
    <?php endif; ?>
    <h2><?php esc_html_e( 'Environment', 'foodbank-manager' ); ?></h2>
    <ul>
        <li><?php echo esc_html( 'PHP ' . $php_version ); ?></li>
        <li><?php echo esc_html( 'WP ' . $wp_version ); ?></li>
        <li><?php echo esc_html( 'FBM ' . $fbm_version ); ?></li>
        <li><?php echo esc_html( 'Last successful FBM boot (plugins_loaded): ' . $boot_status ); ?></li>
        <li><?php echo esc_html( 'Admin notices rendered this request: ' . $notices_render_count ); ?></li>
    </ul>
    <h2><?php esc_html_e( 'Install Health', 'foodbank-manager' ); ?></h2>
    <p><?php esc_html_e( 'Only one copy of the plugin should remain installed under foodbank-manager/.', 'foodbank-manager' ); ?></p>
    <p><?php echo esc_html( sprintf(
        /* translators: %s: canonical plugin slug */
        __( 'Canonical slug: %s', 'foodbank-manager' ),
        $install_scan['canonical']
    ) ); ?></p>
    <p><?php echo esc_html( sprintf(
        /* translators: %d: number of duplicate plugin copies */
        __( 'Duplicate copies: %d', 'foodbank-manager' ),
        count( $install_scan['duplicates'] )
    ) ); ?></p>
    <?php if ( ! empty( $last_consolidation ) ) : ?>
    <p><?php echo esc_html( sprintf(
        /* translators: 1: timestamp of last consolidation, 2: deactivated count, 3: deleted count */
        __( 'Last consolidation: %1$s (deactivated %2$d, deleted %3$d)', 'foodbank-manager' ),
        isset( $last_consolidation['timestamp'] ) ? gmdate( 'Y-m-d H:i', (int) $last_consolidation['timestamp'] ) : __( 'never', 'foodbank-manager' ),
        (int) ( $last_consolidation['deactivated'] ?? 0 ),
        (int) ( $last_consolidation['deleted'] ?? 0 )
    ) ); ?></p>
    <?php endif; ?>
    <?php if ( ! empty( $last_activation_consolidation ) ) : ?>
    <p><?php echo esc_html( sprintf(
        /* translators: 1: timestamp of last activation cleanup, 2: deactivated count, 3: deleted count */
        __( 'Last activation cleanup: %1$s (deactivated %2$d, deleted %3$d)', 'foodbank-manager' ),
        isset( $last_activation_consolidation['timestamp'] ) ? gmdate( 'Y-m-d H:i', (int) $last_activation_consolidation['timestamp'] ) : __( 'never', 'foodbank-manager' ),
        (int) ( $last_activation_consolidation['deactivated'] ?? 0 ),
        (int) ( $last_activation_consolidation['deleted'] ?? 0 )
    ) ); ?></p>
    <?php endif; ?>
    <?php if ( ! empty( $install_scan['duplicates'] ) ) : ?>
    <table class="widefat">
        <thead>
            <tr><th><?php esc_html_e( 'Directory', 'foodbank-manager' ); ?></th><th><?php esc_html_e( 'Version', 'foodbank-manager' ); ?></th></tr>
        </thead>
        <tbody>
        <?php foreach ( $install_scan['duplicates'] as $d ) : ?>
            <tr><td><?php echo esc_html( $d['dir'] ); ?></td><td><?php echo esc_html( $d['version'] ); ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php $post_url = admin_url( 'admin-post.php' ); ?>
    <form method="post" action="<?php echo esc_url( $post_url ); ?>">
        <input type="hidden" name="action" value="fbm_consolidate_plugins" />
        <?php wp_nonce_field( 'fbm_consolidate_plugins' ); ?>
        <p><button type="submit" class="button button-primary"><?php esc_html_e( 'Consolidate (deactivate & delete)', 'foodbank-manager' ); ?></button></p>
    </form>
    <form method="post" action="<?php echo esc_url( $post_url ); ?>">
        <input type="hidden" name="action" value="fbm_deactivate_duplicates" />
        <?php wp_nonce_field( 'fbm_deactivate_duplicates' ); ?>
        <p><button type="submit" class="button"><?php esc_html_e( 'Deactivate only', 'foodbank-manager' ); ?></button></p>
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
    <h2><?php esc_html_e( 'Cron Health', 'foodbank-manager' ); ?></h2>
    <?php $cron = \FoodBankManager\Admin\DiagnosticsPage::cron_status(); ?>
    <form method="post" action="" style="display:inline-block;margin-right:1em;">
        <input type="hidden" name="fbm_action" value="fbm_retention_run" />
        <?php wp_nonce_field( 'fbm_retention_run' ); ?>
        <button type="submit" class="button"><?php esc_html_e( 'Run now', 'foodbank-manager' ); ?></button>
    </form>
    <form method="post" action="" style="display:inline-block;">
        <input type="hidden" name="fbm_action" value="fbm_retention_dry_run" />
        <?php wp_nonce_field( 'fbm_retention_dry_run' ); ?>
        <button type="submit" class="button"><?php esc_html_e( 'Dry-run', 'foodbank-manager' ); ?></button>
    </form>
    <?php $retention_summary = \FoodBankManager\Admin\DiagnosticsPage::retention_summary(); ?>
    <?php if ( ! empty( $retention_summary ) ) : ?>
        <p><code><?php echo esc_html( wp_json_encode( $retention_summary ) ); ?></code></p>
    <?php endif; ?>
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
    <?php \FoodBankManager\Admin\DiagnosticsPdf::render_panel(); ?>
    <?php \FoodBankManager\Admin\DiagnosticsPrivacy::render_panel(); ?>
    <?php /* @psalm-suppress UnresolvableInclude */ require FBM_PATH . 'templates/admin/jobs.php'; ?>
</div>
