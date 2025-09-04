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
$sodium         = extension_loaded( 'sodium' ) ? 'native' : ( class_exists( '\\ParagonIE_Sodium_Compat' ) ? 'polyfill' : 'missing' );
$kek_defined    = defined( 'FBM_KEK_BASE64' ) && FBM_KEK_BASE64 !== '';
$mail_available = function_exists( 'wp_mail' );
$cron_cleanup   = wp_next_scheduled( 'fbm_cron_cleanup' );
$cron_retry     = wp_next_scheduled( 'fbm_cron_email_retry' );
$notice         = isset( $_GET['notice'] ) ? sanitize_key( wp_unslash( $_GET['notice'] ) ) : '';
?>
<div class="wrap">
    <h1><?php esc_html_e( 'Diagnostics', 'foodbank-manager' ); ?></h1>
    <?php if ( 'sent' === $notice ) : ?>
        <div class="notice notice-success"><p><?php esc_html_e( 'Test email sent.', 'foodbank-manager' ); ?></p></div>
    <?php elseif ( 'repaired' === $notice ) : ?>
        <div class="notice notice-success"><p><?php esc_html_e( 'Capabilities repaired.', 'foodbank-manager' ); ?></p></div>
    <?php elseif ( 'error' === $notice ) : ?>
        <div class="notice notice-error"><p><?php esc_html_e( 'Action failed.', 'foodbank-manager' ); ?></p></div>
    <?php endif; ?>
    <table class="widefat fixed">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Check', 'foodbank-manager' ); ?></th>
                <th><?php esc_html_e( 'Result', 'foodbank-manager' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?php esc_html_e( 'PHP version', 'foodbank-manager' ); ?></td>
                <td><?php echo esc_html( $php_version ); ?></td>
            </tr>
            <tr>
                <td><?php esc_html_e( 'WordPress version', 'foodbank-manager' ); ?></td>
                <td><?php echo esc_html( $wp_version ); ?></td>
            </tr>
            <tr>
                <td><?php esc_html_e( 'Sodium', 'foodbank-manager' ); ?></td>
                <td><?php echo esc_html( $sodium ); ?></td>
            </tr>
            <tr>
                <td><?php esc_html_e( 'Encryption key', 'foodbank-manager' ); ?></td>
                <td><?php echo esc_html( $kek_defined ? __( 'present', 'foodbank-manager' ) : __( 'missing', 'foodbank-manager' ) ); ?></td>
            </tr>
            <tr>
                <td><?php esc_html_e( 'Mail transport', 'foodbank-manager' ); ?></td>
                <td><?php echo esc_html( $mail_available ? __( 'available', 'foodbank-manager' ) : __( 'missing', 'foodbank-manager' ) ); ?></td>
            </tr>
            <tr>
                <td><?php esc_html_e( 'Next fbm_cron_cleanup', 'foodbank-manager' ); ?></td>
                <td><?php echo esc_html( $cron_cleanup ? gmdate( 'Y-m-d H:i:s', $cron_cleanup ) : __( 'not scheduled', 'foodbank-manager' ) ); ?></td>
            </tr>
            <tr>
                <td><?php esc_html_e( 'Next fbm_cron_email_retry', 'foodbank-manager' ); ?></td>
                <td><?php echo esc_html( $cron_retry ? gmdate( 'Y-m-d H:i:s', $cron_retry ) : __( 'not scheduled', 'foodbank-manager' ) ); ?></td>
            </tr>
        </tbody>
    </table>
    <h2><?php esc_html_e( 'Actions', 'foodbank-manager' ); ?></h2>
    <form method="post" action="">
        <?php wp_nonce_field( 'fbm_diagnostics_send_test_email', '_fbm_nonce' ); ?>
        <input type="hidden" name="fbm_action" value="send_test_email" />
        <p><button type="submit" class="button"><?php esc_html_e( 'Send Test Email', 'foodbank-manager' ); ?></button></p>
    </form>
    <form method="post" action="">
        <?php wp_nonce_field( 'fbm_diagnostics_repair_caps', '_fbm_nonce' ); ?>
        <input type="hidden" name="fbm_action" value="repair_caps" />
        <p><button type="submit" class="button"><?php esc_html_e( 'Repair Capabilities', 'foodbank-manager' ); ?></button></p>
    </form>
</div>
