<?php
/**
 * Scan page template.
 *
 * @package FBM\Admin
 */

declare(strict_types=1);
?>
<div class="wrap fbm-admin">
    <h1><?php echo esc_html__( 'Scan', 'foodbank-manager' ); ?></h1>
    <form method="post">
        <?php wp_nonce_field( 'fbm_scan_verify', 'fbm_nonce' ); ?>
        <p>
            <label for="fbm-scan-token">Token</label>
            <input type="text" id="fbm-scan-token" name="token" />
        </p>
        <p><button type="submit"><?php echo esc_html__( 'Verify', 'foodbank-manager' ); ?></button></p>
    </form>
    <div data-testid="fbm-scan-status"><?php echo esc_html( $status ?? '' ); ?></div>
    <div data-testid="fbm-scan-recipient"><?php echo esc_html( $recipient ?? '' ); ?></div>
</div>
