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
?>
<div class="wrap fbm-admin">
    <h1><?php esc_html_e( 'Diagnostics', 'foodbank-manager' ); ?></h1>
    <?php if ( 'sent' === $notice ) : ?>
        <div class="notice notice-success"><p><?php esc_html_e( 'Test email sent.', 'foodbank-manager' ); ?></p></div>
    <?php elseif ( 'repaired' === $notice ) : ?>
        <div class="notice notice-success"><p><?php esc_html_e( 'Capabilities repaired.', 'foodbank-manager' ); ?></p></div>
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
    </ul>
    <h2><?php esc_html_e( 'Actions', 'foodbank-manager' ); ?></h2>
    <form method="post" action="">
        <?php wp_nonce_field( 'fbm_diagnostics_repair_caps', '_fbm_nonce' ); ?>
        <input type="hidden" name="fbm_action" value="repair_caps" />
        <p><button type="submit" class="button"><?php esc_html_e( 'Repair Capabilities', 'foodbank-manager' ); ?></button></p>
    </form>
</div>
