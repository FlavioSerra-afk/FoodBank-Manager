<?php
/**
 * Settings uninstall and privacy management section.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

use function admin_url;
use function checked;
use function disabled;
use function esc_attr;
use function esc_html;
use function esc_html__;
use function esc_html_e;
use function esc_url;
use function is_array;
use function submit_button;
use function wp_nonce_field;

$uninstall = isset( $uninstall_context ) && is_array( $uninstall_context ) ? $uninstall_context : array();

$enabled          = ! empty( $uninstall['enabled'] );
$constant_enabled = ! empty( $uninstall['constant'] );
$summary          = isset( $uninstall['summary'] ) && is_array( $uninstall['summary'] ) ? $uninstall['summary'] : array();
$tables           = isset( $summary['tables'] ) && is_array( $summary['tables'] ) ? $summary['tables'] : array();
$options          = isset( $summary['options'] ) && is_array( $summary['options'] ) ? $summary['options'] : array();
$transients       = isset( $summary['transients'] ) && is_array( $summary['transients'] ) ? $summary['transients'] : array();
$events           = isset( $summary['events'] ) && is_array( $summary['events'] ) ? $summary['events'] : array();

$uninstall_form_action = $uninstall['form_action'] ?? '';
$uninstall_nonce       = $uninstall['nonce_action'] ?? '';
$uninstall_nonce_name  = $uninstall['nonce_name'] ?? '';

$eraser            = isset( $uninstall['eraser'] ) && is_array( $uninstall['eraser'] ) ? $uninstall['eraser'] : array();
$eraser_action     = $eraser['form_action'] ?? '';
$eraser_nonce      = $eraser['nonce_action'] ?? '';
$eraser_nonce_name = $eraser['nonce_name'] ?? '';
?>
<div class="card">
		<h2><?php esc_html_e( 'Uninstall & Privacy', 'foodbank-manager' ); ?></h2>
		<p><?php esc_html_e( 'Control whether FoodBank Manager removes all data during uninstall and trigger WordPress privacy tools for an individual member.', 'foodbank-manager' ); ?></p>

		<h3><?php esc_html_e( 'Destructive uninstall', 'foodbank-manager' ); ?></h3>
		<p><?php esc_html_e( 'When enabled, uninstall will drop the following data. Leave unchecked to retain operational history for reinstalls or audits.', 'foodbank-manager' ); ?></p>

		<div class="fbm-uninstall-summary">
				<strong><?php esc_html_e( 'Database tables', 'foodbank-manager' ); ?></strong>
				<ul>
						<?php foreach ( $tables as $table_name ) : ?>
								<li><?php echo esc_html( (string) $table_name ); ?></li>
						<?php endforeach; ?>
				</ul>

				<strong><?php esc_html_e( 'Options & logs', 'foodbank-manager' ); ?></strong>
				<ul>
						<?php foreach ( $options as $option_name ) : ?>
								<li><?php echo esc_html( (string) $option_name ); ?></li>
						<?php endforeach; ?>
				</ul>

				<strong><?php esc_html_e( 'Transients & caches', 'foodbank-manager' ); ?></strong>
				<ul>
						<?php foreach ( $transients as $descriptor ) : ?>
								<li><?php echo esc_html( (string) $descriptor ); ?></li>
						<?php endforeach; ?>
				</ul>

				<strong><?php esc_html_e( 'Scheduled events', 'foodbank-manager' ); ?></strong>
				<ul>
						<?php foreach ( $events as $event ) : ?>
								<li><?php echo esc_html( (string) $event ); ?></li>
						<?php endforeach; ?>
				</ul>
		</div>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( (string) $uninstall_nonce, (string) $uninstall_nonce_name ); ?>
				<input type="hidden" name="action" value="<?php echo esc_attr( (string) $uninstall_form_action ); ?>" />

				<label>
						<input type="checkbox" name="fbm_allow_destructive_uninstall" value="1" <?php checked( $enabled ); ?> <?php disabled( $constant_enabled ); ?> />
						<?php esc_html_e( 'Allow FoodBank Manager to remove all data on uninstall', 'foodbank-manager' ); ?>
				</label>
				<p class="description">
						<?php
						if ( $constant_enabled ) {
								esc_html_e( 'The FBM_ALLOW_DESTRUCTIVE_UNINSTALL constant currently enforces destructive uninstall.', 'foodbank-manager' );
						} else {
								esc_html_e( 'You can also enforce this via the FBM_ALLOW_DESTRUCTIVE_UNINSTALL constant in wp-config.php.', 'foodbank-manager' );
						}
						?>
				</p>

				<?php submit_button( esc_html__( 'Save uninstall preference', 'foodbank-manager' ), 'secondary', 'submit', false, array( 'disabled' => $constant_enabled ? 'disabled' : false ) ); ?>
		</form>

		<hr />

		<h3><?php esc_html_e( 'Erase a member\'s FoodBank Manager data', 'foodbank-manager' ); ?></h3>
		<p><?php esc_html_e( 'Queue the WordPress privacy eraser for a specific email address or member reference. The request will run via Tools → Erase Personal Data.', 'foodbank-manager' ); ?></p>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="fbm-privacy-eraser">
				<?php wp_nonce_field( (string) $eraser_nonce, (string) $eraser_nonce_name ); ?>
				<input type="hidden" name="action" value="<?php echo esc_attr( (string) $eraser_action ); ?>" />

				<label for="fbm-privacy-identifier"><?php esc_html_e( 'Email or member reference', 'foodbank-manager' ); ?></label>
				<input type="text" id="fbm-privacy-identifier" name="fbm_privacy_identifier" class="regular-text" required />
				<?php submit_button( esc_html__( 'Start eraser', 'foodbank-manager' ), 'secondary', 'submit', false ); ?>
		</form>

		<p class="description"><?php esc_html_e( 'The eraser removes personal data while retaining anonymized attendance counts for operational reporting.', 'foodbank-manager' ); ?></p>
</div>
