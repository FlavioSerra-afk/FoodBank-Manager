<?php
/**
 * Staff dashboard template.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

use function esc_html_e;

?>
<div class="fbm-staff-dashboard" data-fbm-staff-dashboard="1">
	<h2 class="fbm-staff-dashboard__heading">
		<?php esc_html_e( 'FoodBank Manager — Staff Dashboard', 'foodbank-manager' ); ?>
	</h2>
	<p class="fbm-staff-dashboard__summary">
		<?php esc_html_e( 'Scan member QR codes or record a manual collection.', 'foodbank-manager' ); ?>
	</p>
	<label class="fbm-staff-dashboard__field" for="fbm-staff-dashboard-reference">
		<span class="fbm-staff-dashboard__label">
			<?php esc_html_e( 'Member reference', 'foodbank-manager' ); ?>
		</span>
		<input
			type="text"
			id="fbm-staff-dashboard-reference"
			name="fbm-staff-dashboard-reference"
			class="fbm-staff-dashboard__input"
			data-fbm-reference
			autocomplete="off"
		/>
	</label>
	<button type="button" class="fbm-staff-dashboard__action" data-fbm-checkin="manual">
		<?php esc_html_e( 'Record manual collection', 'foodbank-manager' ); ?>
	</button>
	<div class="fbm-staff-dashboard__status" data-fbm-status aria-live="polite"></div>
</div>
