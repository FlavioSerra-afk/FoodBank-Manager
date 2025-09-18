<?php
/**
 * Diagnostics admin template.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

use function esc_attr;
use function esc_html;
use function esc_html__;
use function esc_html_e;
use function esc_url;
use function is_array;
use function is_readable;
use function floor;
use function max;
use function number_format_i18n;

$entries            = array();
$notices            = array();
$rate_limit_seconds = 0;
$health_badges      = array();
$token_probe        = array();
$token_failures     = array();

if ( isset( $data['entries'] ) && is_array( $data['entries'] ) ) {
				$entries = $data['entries'];
}

if ( isset( $data['notices'] ) && is_array( $data['notices'] ) ) {
				$notices = $data['notices'];
}

if ( isset( $data['rate_limit_seconds'] ) ) {
				$rate_limit_seconds = (int) $data['rate_limit_seconds'];
}

if ( isset( $data['health_badges'] ) && is_array( $data['health_badges'] ) ) {
                                $health_badges = $data['health_badges'];
}

if ( isset( $data['token_probe'] ) && is_array( $data['token_probe'] ) ) {
                                $token_probe = $data['token_probe'];
}

if ( isset( $data['token_failures'] ) && is_array( $data['token_failures'] ) ) {
                                $token_failures = $data['token_failures'];
}

$rate_limit_minutes = max( 1, (int) floor( $rate_limit_seconds / 60 ) );
?>
<div class="wrap">
				<h1 class="wp-heading-inline"><?php esc_html_e( 'Food Bank Diagnostics', 'foodbank-manager' ); ?></h1>

<?php
foreach ( $notices as $notice ) :
	$notice_type    = isset( $notice['type'] ) ? (string) $notice['type'] : 'info';
	$notice_message = isset( $notice['message'] ) ? (string) $notice['message'] : '';
	?>
<div class="notice notice-<?php echo esc_attr( $notice_type ); ?>">
<p><?php echo esc_html( $notice_message ); ?></p>
</div>
<?php endforeach; ?>

<p class="description">
<?php
$rate_limit_display = number_format_i18n( $rate_limit_minutes );

printf(
/* translators: %s: Number of minutes between resend attempts. */
	esc_html__( 'Mail resend attempts are limited to once every %s minutes per member.', 'foodbank-manager' ),
	esc_html( $rate_limit_display )
);
?>
</p>

<?php if ( ! empty( $health_badges ) ) : ?>
<section class="fbm-status-panel" aria-labelledby="fbm-status-heading">
				<h2 id="fbm-status-heading"><?php esc_html_e( 'System health', 'foodbank-manager' ); ?></h2>
				<ul class="fbm-status-badges" role="list">
	<?php
	foreach ( $health_badges as $badge ) :
		$badge_label   = isset( $badge['label'] ) ? (string) $badge['label'] : '';
		$badge_status  = isset( $badge['status'] ) ? (string) $badge['status'] : '';
		$badge_message = isset( $badge['message'] ) ? (string) $badge['message'] : '';
		$status_class  = 'fbm-status-badge--neutral';
		$status_phrase = esc_html__( 'Status unknown', 'foodbank-manager' );

		switch ( $badge_status ) {
			case \FoodBankManager\Diagnostics\HealthStatus::STATUS_HEALTHY:
					$status_class  = 'fbm-status-badge--healthy';
					$status_phrase = esc_html__( 'Healthy', 'foodbank-manager' );
				break;
			case \FoodBankManager\Diagnostics\HealthStatus::STATUS_DEGRADED:
					$status_class  = 'fbm-status-badge--degraded';
					$status_phrase = esc_html__( 'Needs attention', 'foodbank-manager' );
				break;
		}
		?>
								<li class="fbm-status-badge <?php echo esc_attr( $status_class ); ?>">
												<span class="fbm-status-badge__label"><?php echo esc_html( $badge_label ); ?></span>
												<span class="fbm-status-badge__state"><?php echo esc_html( $status_phrase ); ?></span>
												<span class="fbm-status-badge__message"><?php echo esc_html( $badge_message ); ?></span>
								</li>
	<?php endforeach; ?>
				</ul>
</section>
<?php endif; ?>

<?php
$token_template = FBM_PATH . 'templates/admin/diagnostics-token.php';

if ( is_readable( $token_template ) ) {
        include $token_template;
}
?>

                                <table class="widefat striped">
								<thead>
												<tr>
								<th scope="col"><?php esc_html_e( 'Recorded', 'foodbank-manager' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Member reference', 'foodbank-manager' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Email', 'foodbank-manager' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Context', 'foodbank-manager' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Error', 'foodbank-manager' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Attempts', 'foodbank-manager' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Action', 'foodbank-manager' ); ?></th>
						</tr>
				</thead>
				<tbody>
						<?php if ( empty( $entries ) ) : ?>
								<tr>
										<td colspan="7">
												<?php esc_html_e( 'No mail failures have been recorded.', 'foodbank-manager' ); ?>
										</td>
								</tr>
						<?php else : ?>
								<?php
								foreach ( $entries as $entry ) :
									$recorded_at       = isset( $entry['recorded_at'] ) ? (string) $entry['recorded_at'] : '';
									$member_ref        = isset( $entry['member_reference'] ) ? (string) $entry['member_reference'] : '';
									$email             = isset( $entry['email'] ) ? (string) $entry['email'] : '';
									$context           = isset( $entry['context'] ) ? (string) $entry['context'] : '';
									$error_label       = isset( $entry['error'] ) ? (string) $entry['error'] : '';
									$attempts          = isset( $entry['attempts'] ) ? (int) $entry['attempts'] : 0;
										$can_resend    = ! empty( $entry['can_resend'] );
										$resend_url    = isset( $entry['resend_url'] ) ? (string) $entry['resend_url'] : '';
										$blocked_until = isset( $entry['blocked_until'] ) ? (string) $entry['blocked_until'] : '';
									?>
										<tr>
												<td><?php echo esc_html( $recorded_at ); ?></td>
												<td><?php echo esc_html( $member_ref ); ?></td>
												<td><?php echo esc_html( $email ); ?></td>
												<td><?php echo esc_html( $context ); ?></td>
<td><?php echo esc_html( $error_label ); ?></td>
												<td><?php echo esc_html( number_format_i18n( $attempts ) ); ?></td>
												<td>
														<?php if ( $can_resend && '' !== $resend_url ) : ?>
																<a class="button button-small" href="<?php echo esc_url( $resend_url ); ?>">
																		<?php esc_html_e( 'Resend', 'foodbank-manager' ); ?>
																</a>
														<?php elseif ( '' !== $blocked_until ) : ?>
																<span class="description">
																		<?php
																		printf(
																				/* translators: %s: Timestamp when the resend is available. */
																			esc_html__( 'Available after %s', 'foodbank-manager' ),
																			esc_html( $blocked_until )
																		);
																		?>
																</span>
														<?php else : ?>
																<span class="description"><?php esc_html_e( 'Resend unavailable.', 'foodbank-manager' ); ?></span>
														<?php endif; ?>
												</td>
										</tr>
								<?php endforeach; ?>
						<?php endif; ?>
				</tbody>
		</table>
</div>
