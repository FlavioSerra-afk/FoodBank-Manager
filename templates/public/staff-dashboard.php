<?php
/**
 * Staff dashboard template.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

use FoodBankManager\Core\Schedule;
use function esc_attr;
use function esc_html;
use function esc_html__;
use function esc_html_e;

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
		<div class="fbm-staff-dashboard__section" data-fbm-scanner>
				<h3 class="fbm-staff-dashboard__subheading">
						<?php esc_html_e( 'Camera scanning', 'foodbank-manager' ); ?>
				</h3>
				<p class="fbm-staff-dashboard__helper">
						<?php esc_html_e( 'Use the device camera to scan a member QR token. Manual entry is always available as a fallback.', 'foodbank-manager' ); ?>
				</p>
				<div class="fbm-staff-dashboard__camera-wrapper" data-fbm-camera-wrapper hidden>
						<video class="fbm-staff-dashboard__camera" data-fbm-camera playsinline muted aria-label="<?php echo esc_attr( esc_html__( 'QR scanner preview', 'foodbank-manager' ) ); ?>"></video>
						<button type="button" class="fbm-staff-dashboard__action fbm-staff-dashboard__action--secondary" data-fbm-stop-scan>
								<?php esc_html_e( 'Stop scanning', 'foodbank-manager' ); ?>
						</button>
				</div>
				<button type="button" class="fbm-staff-dashboard__action" data-fbm-start-scan>
						<?php esc_html_e( 'Start camera scan', 'foodbank-manager' ); ?>
				</button>
				<p class="fbm-staff-dashboard__helper fbm-staff-dashboard__helper--muted" data-fbm-camera-fallback hidden>
						<?php esc_html_e( 'Camera scanning is not available on this device. Use manual entry below.', 'foodbank-manager' ); ?>
				</p>
		</div>
		<form class="fbm-staff-dashboard__section" data-fbm-manual>
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
								name="fbm-staff-dashboard-reference"
								class="fbm-staff-dashboard__input"
								data-fbm-reference
								autocomplete="off"
						/>
				</label>
				<button type="submit" class="fbm-staff-dashboard__action" data-fbm-checkin="manual">
						<?php esc_html_e( 'Record manual collection', 'foodbank-manager' ); ?>
				</button>
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
