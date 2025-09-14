<?php
// phpcs:ignoreFile
/**
 * FBM Theme admin template.
 *
 * @package FoodBankManager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$opts = get_option( 'fbm_theme', [] );
?>
<div class="wrap fbm-theme">
    <h1><?php esc_html_e( 'FBM Theme', 'foodbank-manager' ); ?></h1>
    <div class="fbm-grid">
        <aside class="fbm-controls">
            <form method="post" action="options.php" id="fbm-theme-form">
                <?php if ( function_exists( 'settings_fields' ) ) { settings_fields( 'fbm_theme' ); } ?>
                <?php if ( function_exists( 'submit_button' ) ) { submit_button( __( 'Save Theme', 'foodbank-manager' ) ); } ?>
            </form>
            <div class="fbm-utilities">
                <button type="button" class="button fbm-import"><?php esc_html_e( 'Import', 'foodbank-manager' ); ?></button>
                <button type="button" class="button fbm-export"><?php esc_html_e( 'Export', 'foodbank-manager' ); ?></button>
                <button type="button" class="button fbm-defaults"><?php esc_html_e( 'Defaults', 'foodbank-manager' ); ?></button>
                <input type="file" class="fbm-utils-file" accept="application/json" hidden />
            </div>
        </aside>
        <main class="fbm-preview">
            <style id="fbm-preview-vars"></style>
        </main>
    </div>
</div>
