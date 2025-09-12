<?php // phpcs:ignoreFile
/**
 * Diagnostics privacy panel template.
 *
 * @package FoodBankManager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use FoodBankManager\Admin\DiagnosticsPrivacy;

$preview = DiagnosticsPrivacy::preview_summary();
$erasure = DiagnosticsPrivacy::erasure_summary();
?>
<h2><?php esc_html_e( 'Privacy', 'foodbank-manager' ); ?></h2>
<form method="post" action="">
    <p>
        <label for="fbm-privacy-email"><?php esc_html_e( 'Email', 'foodbank-manager' ); ?></label>
        <input type="email" id="fbm-privacy-email" name="email" required />
    </p>
    <p>
        <label for="fbm-privacy-page"><?php esc_html_e( 'Page size', 'foodbank-manager' ); ?></label>
        <input type="number" id="fbm-privacy-page" name="page_size" min="1" max="100" value="1" />
    </p>
    <input type="hidden" name="fbm_privacy_action" value="fbm_privacy_preview" />
    <?php wp_nonce_field( 'fbm_privacy_preview' ); ?>
    <?php submit_button( __( 'Preview SAR', 'foodbank-manager' ), 'secondary', '', false ); ?>
</form>
<form method="post" action="" style="margin-top:1em;">
    <p>
        <label for="fbm-privacy-email-dry" class="screen-reader-text"><?php esc_html_e( 'Email', 'foodbank-manager' ); ?></label>
        <input type="email" id="fbm-privacy-email-dry" name="email" required />
    </p>
    <input type="hidden" name="fbm_privacy_action" value="fbm_privacy_erase_dry" />
    <?php wp_nonce_field( 'fbm_privacy_erase_dry' ); ?>
    <?php submit_button( __( 'Run erasure (dry-run)', 'foodbank-manager' ), 'secondary', '', false ); ?>
</form>
<form method="post" action="" style="margin-top:1em;">
    <p>
        <label for="fbm-privacy-email-real" class="screen-reader-text"><?php esc_html_e( 'Email', 'foodbank-manager' ); ?></label>
        <input type="email" id="fbm-privacy-email-real" name="email" required />
    </p>
    <input type="hidden" name="fbm_privacy_action" value="fbm_privacy_erase" />
    <?php wp_nonce_field( 'fbm_privacy_erase' ); ?>
    <?php submit_button( __( 'Run erasure now', 'foodbank-manager' ), 'delete', '', false ); ?>
</form>
<?php if ( ! empty( $preview['data'] ) ) : ?>
    <ul>
        <?php foreach ( $preview['data'] as $item ) : ?>
            <li><?php echo esc_html( $item['group_label'] . ' #' . $item['item_id'] ); ?>
                <ul>
                    <?php foreach ( $item['data'] as $field ) : ?>
                        <li><?php echo esc_html( $field['name'] . ': ' . $field['value'] ); ?></li>
                    <?php endforeach; ?>
                </ul>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php if ( ! $preview['done'] ) : ?>
        <p><?php esc_html_e( 'More data available…', 'foodbank-manager' ); ?></p>
    <?php endif; ?>
<?php endif; ?>
<?php if ( ! empty( $erasure ) ) : ?>
    <p><code><?php echo esc_html( wp_json_encode( $erasure ) ); ?></code></p>
<?php endif; ?>
