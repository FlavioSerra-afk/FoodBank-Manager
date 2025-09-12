<?php // phpcs:ignoreFile
/**
 * Diagnostics system report template.
 *
 * @package FoodBankManager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<h2><?php esc_html_e( 'System Report', 'foodbank-manager' ); ?></h2>
<pre id="fbm-diagnostics-report"><?php echo esc_html( implode( "\n", $lines ?? array() ) ); ?></pre>
<p>
    <button type="button" class="button" id="fbm-diagnostics-copy" data-report="<?php echo esc_attr( wp_json_encode( $data ) ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>">
        <?php esc_html_e( 'Copy report', 'foodbank-manager' ); ?>
    </button>
    <button type="button" class="button" id="fbm-diagnostics-download" data-report="<?php echo esc_attr( wp_json_encode( $data ) ); ?>">
        <?php esc_html_e( 'Download JSON', 'foodbank-manager' ); ?>
    </button>
</p>
