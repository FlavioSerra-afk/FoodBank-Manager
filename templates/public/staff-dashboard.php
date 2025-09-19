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

$dashboard_settings = isset( $settings ) && is_array( $settings ) ? $settings : array();
$show_counters      = isset( $dashboard_settings['show_counters'] ) ? (bool) $dashboard_settings['show_counters'] : true;
$allow_override     = isset( $dashboard_settings['allow_override'] ) ? (bool) $dashboard_settings['allow_override'] : true;
$scanner_settings   = isset( $dashboard_settings['scanner'] ) && is_array( $dashboard_settings['scanner'] ) ? $dashboard_settings['scanner'] : array();
$scanner_roi        = isset( $scanner_settings['roi'] ) ? (int) $scanner_settings['roi'] : 80;
$scanner_debounce   = isset( $scanner_settings['decode_debounce'] ) ? (int) $scanner_settings['decode_debounce'] : 1200;
$scanner_torch      = isset( $scanner_settings['prefer_torch'] ) ? (bool) $scanner_settings['prefer_torch'] : false;

$scanner_roi      = max( 30, min( 100, $scanner_roi ) );
$scanner_debounce = max( 0, min( 5000, $scanner_debounce ) );
$roi_variable     = $scanner_roi . '%';
?>
<div
		class="fbm-staff-dashboard"
		data-fbm-staff-dashboard="1"
		data-fbm-scanner-roi="<?php echo esc_attr( (string) $scanner_roi ); ?>"
		data-fbm-scanner-debounce="<?php echo esc_attr( (string) $scanner_debounce ); ?>"
		data-fbm-scanner-torch="<?php echo esc_attr( $scanner_torch ? '1' : '0' ); ?>"
		data-fbm-show-counters="<?php echo esc_attr( $show_counters ? '1' : '0' ); ?>"
		data-fbm-allow-override="<?php echo esc_attr( $allow_override ? '1' : '0' ); ?>"
>
<h1 class="fbm-staff-dashboard__heading">
<?php esc_html_e( 'FoodBank Manager — Staff Dashboard', 'foodbank-manager' ); ?>
</h1>
<p class="fbm-staff-dashboard__summary">
<?php
printf(
/* translators: %s: weekly collection window sentence. */
	esc_html__( 'Collections run on %s. Scan member QR codes or record a manual collection during this window, and record a manager override with a justification if the member collected within the last week.', 'foodbank-manager' ),
	esc_html( $labels['sentence'] )
);
?>
</p>
<?php if ( $show_counters ) : ?>
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
<?php endif; ?>
<div class="fbm-staff-dashboard__section" data-fbm-scanner-module>
<h2 class="fbm-staff-dashboard__subheading" id="fbm-staff-dashboard-scanner-heading">
<?php esc_html_e( 'Camera scanning', 'foodbank-manager' ); ?>
</h2>
<p class="fbm-staff-dashboard__helper">
<?php esc_html_e( 'Use the device camera to scan a member QR token. Manual entry is always available as a fallback.', 'foodbank-manager' ); ?>
</p>
<div class="fbm-staff-dashboard__scanner-controls" data-fbm-scanner-controls hidden>
<fieldset class="fbm-staff-dashboard__fieldset" aria-labelledby="fbm-staff-dashboard-scanner-heading">
<legend class="screen-reader-text" id="fbm-staff-dashboard-scanner-controls-legend"><?php esc_html_e( 'Camera controls', 'foodbank-manager' ); ?></legend>
<label class="fbm-staff-dashboard__field fbm-staff-dashboard__field--inline" for="fbm-staff-dashboard-camera-select" data-fbm-scanner-select-wrapper hidden>
<span class="fbm-staff-dashboard__label"><?php esc_html_e( 'Camera source', 'foodbank-manager' ); ?></span>
<select class="fbm-staff-dashboard__input" id="fbm-staff-dashboard-camera-select" data-fbm-scanner-select aria-describedby="fbm-staff-dashboard-status">
<option value=""><?php esc_html_e( 'Default camera', 'foodbank-manager' ); ?></option>
</select>
</label>
<button type="button" class="fbm-staff-dashboard__action fbm-staff-dashboard__action--secondary" data-fbm-scanner-torch hidden aria-controls="fbm-staff-dashboard-camera-wrapper" aria-pressed="false">
<?php esc_html_e( 'Turn torch on', 'foodbank-manager' ); ?>
</button>
</fieldset>
</div>
<div class="fbm-staff-dashboard__camera-wrapper" id="fbm-staff-dashboard-camera-wrapper" data-fbm-scanner-wrapper hidden>
<div class="fbm-staff-dashboard__camera-frame" data-fbm-scanner-frame aria-hidden="true" style="--fbm-scanner-roi: <?php echo esc_attr( $roi_variable ); ?>;">
<video class="fbm-staff-dashboard__camera" data-fbm-scanner-video playsinline muted aria-label="<?php echo esc_attr( esc_html__( 'QR scanner preview', 'foodbank-manager' ) ); ?>"></video>
<div class="fbm-staff-dashboard__camera-overlay" data-fbm-scanner-overlay aria-hidden="true"></div>
</div>
<div class="fbm-staff-dashboard__scanner-actions">
<button type="button" class="fbm-staff-dashboard__action fbm-staff-dashboard__action--secondary" data-fbm-scanner-stop aria-controls="fbm-staff-dashboard-camera-wrapper">
<?php esc_html_e( 'Stop scanning', 'foodbank-manager' ); ?>
</button>
</div>
</div>
<button type="button" class="fbm-staff-dashboard__action" data-fbm-scanner-start aria-controls="fbm-staff-dashboard-camera-wrapper">
<?php esc_html_e( 'Start camera scan', 'foodbank-manager' ); ?>
</button>
<p class="fbm-staff-dashboard__helper" data-fbm-scanner-feedback role="status" aria-live="polite"></p>
<p class="fbm-staff-dashboard__helper fbm-staff-dashboard__helper--muted" data-fbm-scanner-fallback role="status" aria-live="polite" hidden>
<?php esc_html_e( 'Camera scanning is not available on this device. Use manual entry below.', 'foodbank-manager' ); ?>
</p>
</div>

<form class="fbm-staff-dashboard__section" data-fbm-manual method="post">
<h2 class="fbm-staff-dashboard__subheading" id="fbm-staff-dashboard-manual-heading">
<?php esc_html_e( 'Manual entry', 'foodbank-manager' ); ?>
</h2>
<fieldset class="fbm-staff-dashboard__fieldset">
<legend class="screen-reader-text"><?php esc_html_e( 'Manual entry controls', 'foodbank-manager' ); ?></legend>
<label class="fbm-staff-dashboard__field" for="fbm-staff-dashboard-reference">
<span class="fbm-staff-dashboard__label">
<?php esc_html_e( 'Member reference', 'foodbank-manager' ); ?>
</span>
<?php
$manual_value = '';
if ( isset( $manual_entry ) && is_array( $manual_entry ) && isset( $manual_entry['code'] ) && is_string( $manual_entry['code'] ) ) {
				$manual_value = $manual_entry['code'];
}
?>
<input
type="text"
id="fbm-staff-dashboard-reference"
name="code"
class="fbm-staff-dashboard__input"
data-fbm-reference
autocomplete="off"
value="<?php echo esc_attr( $manual_value ); ?>"
aria-describedby="fbm-staff-dashboard-status"
/>
</label>
<?php wp_nonce_field( 'fbm_staff_manual_entry', 'fbm_staff_manual_nonce' ); ?>
<button type="submit" class="fbm-staff-dashboard__action" data-fbm-checkin="manual">
<?php esc_html_e( 'Record manual collection', 'foodbank-manager' ); ?>
</button>
</fieldset>
<?php if ( isset( $manual_entry ) && is_array( $manual_entry ) && isset( $manual_entry['message'] ) && '' !== $manual_entry['message'] ) : ?>
	<?php
	$manual_status       = isset( $manual_entry['status'] ) && is_string( $manual_entry['status'] ) && '' !== $manual_entry['status'] ? $manual_entry['status'] : 'info';
	$manual_status_class = 'fbm-staff-dashboard__manual-status--' . $manual_status;
	if ( ! empty( $manual_entry['requires_override'] ) ) {
		$manual_status_class .= ' fbm-staff-dashboard__manual-status--recent_warning';
	}
	?>
<div class="fbm-staff-dashboard__manual-status <?php echo esc_attr( $manual_status_class ); ?>" role="status" aria-live="polite">
	<?php echo esc_html( $manual_entry['message'] ); ?>
</div>
<?php endif; ?>
</form>

<?php if ( isset( $manual_entry ) && is_array( $manual_entry ) && ! empty( $manual_entry['requires_override'] ) ) : ?>
		<?php
		$override_code = '';
		if ( isset( $manual_entry['code'] ) && is_string( $manual_entry['code'] ) ) {
				$override_code = $manual_entry['code'];
		}

		$override_note_value = '';
		if ( isset( $manual_entry['override_note'] ) && is_string( $manual_entry['override_note'] ) ) {
				$override_note_value = $manual_entry['override_note'];
		}
		?>
		<?php if ( $allow_override ) : ?>
<form class="fbm-staff-dashboard__manual-override" method="post">
<input type="hidden" name="code" value="<?php echo esc_attr( $override_code ); ?>" />
<input type="hidden" name="override" value="1" />
				<?php wp_nonce_field( 'fbm_staff_manual_entry', 'fbm_staff_manual_nonce' ); ?>
<fieldset class="fbm-staff-dashboard__fieldset">
<legend class="screen-reader-text"><?php esc_html_e( 'Confirm manual override', 'foodbank-manager' ); ?></legend>
<label class="fbm-staff-dashboard__field" for="fbm-staff-dashboard-manual-override-note">
<span class="fbm-staff-dashboard__label"><?php esc_html_e( 'Override note', 'foodbank-manager' ); ?></span>
<textarea
id="fbm-staff-dashboard-manual-override-note"
name="override_note"
class="fbm-staff-dashboard__input fbm-staff-dashboard__input--textarea"
rows="3"
aria-describedby="fbm-staff-dashboard-status"
>
				<?php
				if ( function_exists( 'esc_textarea' ) ) {
								echo esc_textarea( $override_note_value );
				} else {
								echo esc_html( $override_note_value );
				}
				?>
</textarea>
</label>
<button type="submit" class="fbm-staff-dashboard__action"><?php esc_html_e( 'Confirm override', 'foodbank-manager' ); ?></button>
</fieldset>
</form>
		<?php else : ?>
<div class="fbm-staff-dashboard__manual-status fbm-staff-dashboard__manual-status--warning" role="status" aria-live="polite">
			<?php esc_html_e( 'Override prompts are disabled. Please contact a manager to record this visit.', 'foodbank-manager' ); ?>
</div>
		<?php endif; ?>
<?php endif; ?>

<?php if ( $allow_override ) : ?>
<div class="fbm-staff-dashboard__override" data-fbm-override hidden>
<fieldset class="fbm-staff-dashboard__fieldset" aria-describedby="fbm-staff-dashboard-status">
<legend class="screen-reader-text"><?php esc_html_e( 'Manager override confirmation', 'foodbank-manager' ); ?></legend>
<p class="fbm-staff-dashboard__helper" data-fbm-override-message role="status" aria-live="polite">
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
aria-describedby="fbm-staff-dashboard-status"
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
</fieldset>
</div>
<?php endif; ?>

<div class="fbm-staff-dashboard__status" id="fbm-staff-dashboard-status" data-fbm-status role="status" aria-live="polite" aria-atomic="true"></div>
</div>
