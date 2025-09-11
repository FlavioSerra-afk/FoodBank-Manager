<?php // phpcs:ignoreFile
/**
 * Diagnostics PDF panel.
 *
 * @package FoodBankManager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use FoodBankManager\Admin\DiagnosticsPdf;

$brand = $brand ?? array();
$action = admin_url( 'admin-post.php' );
?>
<h2><?php esc_html_e( 'PDF', 'foodbank-manager' ); ?></h2>
<form method="post" action="<?php echo esc_url( $action ); ?>">
    <input type="hidden" name="action" value="<?php echo esc_attr( DiagnosticsPdf::ACTION_PREVIEW ); ?>" />
    <p>
        <label for="fbm-pdf-logo"><?php esc_html_e( 'Logo ID', 'foodbank-manager' ); ?></label>
        <input type="number" id="fbm-pdf-logo" name="logo" value="<?php echo esc_attr( (string) ( $brand['logo'] ?? 0 ) ); ?>" />
    </p>
    <p>
        <label for="fbm-pdf-name"><?php esc_html_e( 'Organisation Name', 'foodbank-manager' ); ?></label>
        <input type="text" id="fbm-pdf-name" name="org_name" value="<?php echo esc_attr( (string) ( $brand['org_name'] ?? '' ) ); ?>" />
    </p>
    <p>
        <label for="fbm-pdf-address"><?php esc_html_e( 'Address', 'foodbank-manager' ); ?></label><br />
        <textarea id="fbm-pdf-address" name="org_address" rows="3" cols="40"><?php echo esc_textarea( (string) ( $brand['org_address'] ?? '' ) ); ?></textarea>
    </p>
    <p>
        <label for="fbm-pdf-color"><?php esc_html_e( 'Primary Color', 'foodbank-manager' ); ?></label>
        <input type="text" id="fbm-pdf-color" name="primary_color" value="<?php echo esc_attr( (string) ( $brand['primary_color'] ?? '#000000' ) ); ?>" />
    </p>
    <p>
        <label for="fbm-pdf-footer"><?php esc_html_e( 'Footer Text', 'foodbank-manager' ); ?></label>
        <input type="text" id="fbm-pdf-footer" name="footer_text" value="<?php echo esc_attr( (string) ( $brand['footer_text'] ?? '' ) ); ?>" />
    </p>
    <p>
        <label for="fbm-pdf-page"><?php esc_html_e( 'Page Size', 'foodbank-manager' ); ?></label>
        <input type="text" id="fbm-pdf-page" name="page_size" value="<?php echo esc_attr( (string) ( $brand['page_size'] ?? 'A4' ) ); ?>" />
    </p>
    <p>
        <label for="fbm-pdf-orient"><?php esc_html_e( 'Orientation', 'foodbank-manager' ); ?></label>
        <select id="fbm-pdf-orient" name="orientation">
            <option value="portrait" <?php selected( ( $brand['orientation'] ?? 'portrait' ), 'portrait' ); ?>><?php esc_html_e( 'Portrait', 'foodbank-manager' ); ?></option>
            <option value="landscape" <?php selected( ( $brand['orientation'] ?? '' ), 'landscape' ); ?>><?php esc_html_e( 'Landscape', 'foodbank-manager' ); ?></option>
        </select>
    </p>
    <?php wp_nonce_field( DiagnosticsPdf::ACTION_PREVIEW ); ?>
    <?php submit_button( __( 'Preview PDF', 'foodbank-manager' ), 'secondary', '', false ); ?>
</form>
