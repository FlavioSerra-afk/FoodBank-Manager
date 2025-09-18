<?php
/**
 * Staff dashboard template.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

use FoodBankManager\Core\Schedule;

$schedule = new Schedule();
$window   = $schedule->current_window();
$labels   = Schedule::window_labels( $window );
?>
<div class="fbm-staff-dashboard" data-fbm-staff-dashboard="1">
<h2 class="fbm-staff-dashboard__heading">
<?php esc_html_e( 'FoodBank Manager — Staff Dashboard', 'foodbank-manager' ); ?>
</h2>
<p class="fbm-staff-dashboard__summary">
<?php
printf(
/* translators: %s: weekly collection window sentence. */
	esc_html__( 'Collections run on %s. Scan member QR codes or record a manual collection during this window, and record a manager override with a justification if the member collected within the last week.', 'foodbank-manager' ),
	esc_html( $labels['sentence'] )
);
?>
</p>
<div class="fbm-staff-dashboard__today" data-fbm-today>
<div class="fbm-staff-dashboard__today-item">
<span class="fbm-staff-dashboard__today-label"><?php esc_html_e( 'Collections today', 'foodbank-manager' ); ?></span>
<span class="fbm-staff-dashboard__today-value" data-fbm-today-success>0</span>
</div>
<div class="fbm-staff-dashboard__today-item">
<span class="fbm-staff-dashboard__today-label"><?php esc_html_e( 'Duplicate attempts', 'foodbank-manager' ); ?></span>
<span class="fbm-staff-dashboard__today-value" data-fbm-today-duplicate>0</span>
</div>
<div class="fbm-staff-dashboard__today-item">
<span class="fbm-staff-dashboard__today-label"><?php esc_html_e( 'Overrides recorded', 'foodbank-manager' ); ?></span>
<span class="fbm-staff-dashboard__today-value" data-fbm-today-override>0</span>
</div>
</div>
<div class="fbm-staff-dashboard__section" data-fbm-scanner-module>
<h3 class="fbm-staff-dashboard__subheading">
<?php esc_html_e( 'Camera scanning', 'foodbank-manager' ); ?>
</h3>
<p class="fbm-staff-dashboard__helper">
<?php esc_html_e( 'Use the device camera to scan a member QR token. Manual entry is always available as a fallback.', 'foodbank-manager' ); ?>
</p>
<div class="fbm-staff-dashboard__scanner-controls" data-fbm-scanner-controls hidden>
<label class="fbm-staff-dashboard__field fbm-staff-dashboard__field--inline" data-fbm-scanner-select-wrapper hidden>
<span class="fbm-staff-dashboard__label"><?php esc_html_e( 'Camera source', 'foodbank-manager' ); ?></span>
<select class="fbm-staff-dashboard__input" data-fbm-scanner-select>
<option value=""><?php esc_html_e( 'Default camera', 'foodbank-manager' ); ?></option>
</select>
</label>
<button type="button" class="fbm-staff-dashboard__action fbm-staff-dashboard__action--secondary" data-fbm-scanner-torch hidden>
<?php esc_html_e( 'Turn torch on', 'foodbank-manager' ); ?>
</button>
</div>
<div class="fbm-staff-dashboard__camera-wrapper" data-fbm-scanner-wrapper hidden>
<div class="fbm-staff-dashboard__camera-frame" data-fbm-scanner-frame aria-hidden="true">
<video class="fbm-staff-dashboard__camera" data-fbm-scanner-video playsinline muted aria-label="<?php echo esc_attr( esc_html__( 'QR scanner preview', 'foodbank-manager' ) ); ?>"></video>
<div class="fbm-staff-dashboard__camera-overlay" data-fbm-scanner-overlay aria-hidden="true"></div>
</div>
<div class="fbm-staff-dashboard__scanner-actions">
<button type="button" class="fbm-staff-dashboard__action fbm-staff-dashboard__action--secondary" data-fbm-scanner-stop>
<?php esc_html_e( 'Stop scanning', 'foodbank-manager' ); ?>
</button>
</div>
</div>
<button type="button" class="fbm-staff-dashboard__action" data-fbm-scanner-start>
<?php esc_html_e( 'Start camera scan', 'foodbank-manager' ); ?>
</button>
<p class="fbm-staff-dashboard__helper" data-fbm-scanner-feedback role="status" aria-live="polite"></p>
<p class="fbm-staff-dashboard__helper fbm-staff-dashboard__helper--muted" data-fbm-scanner-fallback hidden>
<?php esc_html_e( 'Camera scanning is not available on this device. Use manual entry below.', 'foodbank-manager' ); ?>
</p>
</div>

<form class="fbm-staff-dashboard__section" data-fbm-manual method="post">
<h3 class="fbm-staff-dashboard__subheading">
<?php esc_html_e( 'Manual entry', 'foodbank-manager' ); ?>
</h3>
<label class="fbm-staff-dashboard__field" for="fbm-staff-dashboard-reference">
<span class="fbm-staff-dashboard__label">
<?php esc_html_e( 'Member reference', 'foodbank-manager' ); ?>
</span>
<input
type="text"
id="fbm-staff-dashboard-reference"
name="code"
class="fbm-staff-dashboard__input"
data-fbm-reference
autocomplete="off"
/>
</label>
<?php wp_nonce_field( 'fbm_staff_manual_entry', 'fbm_staff_manual_nonce' ); ?>
<button type="submit" class="fbm-staff-dashboard__action" data-fbm-checkin="manual">
<?php esc_html_e( 'Record manual collection', 'foodbank-manager' ); ?>
</button>
<?php if ( isset( $manual_entry ) && is_array( $manual_entry ) && isset( $manual_entry['message'] ) && '' !== $manual_entry['message'] ) : ?>
	<?php
	$manual_status       = isset( $manual_entry['status'] ) && is_string( $manual_entry['status'] ) && '' !== $manual_entry['status'] ? $manual_entry['status'] : 'info';
	$manual_status_class = 'fbm-staff-dashboard__manual-status--' . $manual_status;
	?>
<div class="fbm-staff-dashboard__manual-status <?php echo esc_attr( $manual_status_class ); ?>" role="status" aria-live="polite">
	<?php echo esc_html( $manual_entry['message'] ); ?>
</div>
<?php endif; ?>
</form>

<div class="fbm-staff-dashboard__override" data-fbm-override hidden>
<p class="fbm-staff-dashboard__helper" data-fbm-override-message>
<?php esc_html_e( 'This member collected within the last week. Only managers can continue by recording an override with a justification.', 'foodbank-manager' ); ?>
</p>
<label class="fbm-staff-dashboard__field" for="fbm-staff-dashboard-override-note">
<span class="fbm-staff-dashboard__label">
<?php esc_html_e( 'Override note', 'foodbank-manager' ); ?>
</span>
<textarea
id="fbm-staff-dashboard-override-note"
class="fbm-staff-dashboard__input fbm-staff-dashboard__input--textarea"
data-fbm-override-note
rows="3"
></textarea>
</label>
<div class="fbm-staff-dashboard__override-actions">
<button type="button" class="fbm-staff-dashboard__action" data-fbm-confirm-override>
<?php esc_html_e( 'Record override', 'foodbank-manager' ); ?>
</button>
<button type="button" class="fbm-staff-dashboard__action fbm-staff-dashboard__action--secondary" data-fbm-cancel-override>
<?php esc_html_e( 'Cancel override', 'foodbank-manager' ); ?>
</button>
</div>
</div>

<div class="fbm-staff-dashboard__status" data-fbm-status role="status" aria-live="polite" aria-atomic="true"></div>
</div>
