<?php
/**
 * Staff dashboard template.
 *
 * @package FoodBankManager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="fbm-scope fbm-staff-dashboard" data-fbm-staff-dashboard>
    <header class="fbm-staff-dashboard__header">
        <h2 class="fbm-staff-dashboard__title"><?php esc_html_e( 'Weekly collection window', 'foodbank-manager' ); ?></h2>
        <p class="fbm-staff-dashboard__window"><?php esc_html_e( 'Thursday 11:00–14:30', 'foodbank-manager' ); ?></p>
    </header>

    <section class="fbm-staff-dashboard__meta" aria-live="polite">
        <div class="fbm-staff-dashboard__stat">
            <span class="fbm-staff-dashboard__stat-label"><?php esc_html_e( 'Collections today', 'foodbank-manager' ); ?></span>
            <strong class="fbm-staff-dashboard__stat-value" data-fbm-today-count>0</strong>
        </div>
        <div class="fbm-staff-dashboard__stat">
            <span class="fbm-staff-dashboard__stat-label"><?php esc_html_e( 'Last scan', 'foodbank-manager' ); ?></span>
            <strong class="fbm-staff-dashboard__stat-value" data-fbm-last-result>—</strong>
        </div>
    </section>

    <div class="fbm-staff-dashboard__notice" role="status" aria-live="assertive" data-fbm-status>
        <?php esc_html_e( 'Ready for the next collection.', 'foodbank-manager' ); ?>
    </div>

    <form class="fbm-staff-dashboard__manual" data-fbm-manual>
        <fieldset>
            <legend class="screen-reader-text"><?php esc_html_e( 'Manual entry', 'foodbank-manager' ); ?></legend>
            <label for="fbm-manual-token" class="fbm-staff-dashboard__label"><?php esc_html_e( 'Member token or code', 'foodbank-manager' ); ?></label>
            <input type="text" id="fbm-manual-token" name="token" autocomplete="off" required />
        </fieldset>
        <button type="submit" class="fbm-button fbm-button--primary"><?php esc_html_e( 'Record collection', 'foodbank-manager' ); ?></button>
    </form>

    <section class="fbm-staff-dashboard__scanner" data-fbm-scanner>
        <h3 class="fbm-staff-dashboard__section-title"><?php esc_html_e( 'QR scanner', 'foodbank-manager' ); ?></h3>
        <p class="fbm-staff-dashboard__hint"><?php esc_html_e( 'Allow camera access to scan member QR codes automatically.', 'foodbank-manager' ); ?></p>
        <button type="button" class="fbm-button" data-fbm-start-scan>
            <?php esc_html_e( 'Start scanning', 'foodbank-manager' ); ?>
        </button>
        <div class="fbm-staff-dashboard__preview" data-fbm-preview hidden></div>
        <p class="fbm-staff-dashboard__error" role="alert" hidden data-fbm-scan-error></p>
    </section>
</div>
