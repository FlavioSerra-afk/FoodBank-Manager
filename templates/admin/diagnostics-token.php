<?php
/**
 * Token diagnostics panel template.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

use function esc_attr;
use function esc_html;
use function esc_html__;
use function esc_html_e;
use function esc_textarea;
use function is_array;
use function wp_json_encode;
use function wp_nonce_field;

$token_probe    = array();
$token_failures = array();

if ( isset( $data['token_probe'] ) && is_array( $data['token_probe'] ) ) {
		$token_probe = $data['token_probe'];
}

if ( isset( $data['token_failures'] ) && is_array( $data['token_failures'] ) ) {
		$token_failures = $data['token_failures'];
}

$field        = isset( $token_probe['field'] ) ? (string) $token_probe['field'] : '';
$nonce_field  = isset( $token_probe['nonce_field'] ) ? (string) $token_probe['nonce_field'] : '';
$nonce_action = isset( $token_probe['nonce_action'] ) ? (string) $token_probe['nonce_action'] : '';
$payload      = isset( $token_probe['payload'] ) ? (string) $token_probe['payload'] : '';
$submitted    = ! empty( $token_probe['submitted'] );
$result       = isset( $token_probe['result'] ) && is_array( $token_probe['result'] ) ? $token_probe['result'] : null;
$probe_error  = isset( $token_probe['error'] ) ? (string) $token_probe['error'] : null;
?>
<section class="fbm-token-probe" aria-labelledby="fbm-token-probe-heading">
		<h2 id="fbm-token-probe-heading"><?php esc_html_e( 'Token Probe (redacted)', 'foodbank-manager' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Submit a token payload to check signature validity and revocation status without exposing the raw value.', 'foodbank-manager' ); ?></p>
		<form method="post" action="">
				<?php if ( '' !== $nonce_action && '' !== $nonce_field ) : ?>
						<?php wp_nonce_field( $nonce_action, $nonce_field ); ?>
				<?php endif; ?>
				<p>
						<label for="fbm-token-probe-payload" class="screen-reader-text"><?php esc_html_e( 'Token payload', 'foodbank-manager' ); ?></label>
						<textarea id="fbm-token-probe-payload" name="<?php echo esc_attr( $field ); ?>" rows="3" class="large-text" placeholder="FBM1:XXXX..."><?php echo esc_textarea( $payload ); ?></textarea>
				</p>
				<p>
						<button type="submit" class="button button-primary"><?php esc_html_e( 'Probe token', 'foodbank-manager' ); ?></button>
				</p>
		</form>
				<?php if ( $submitted ) : ?>
								<div class="fbm-token-probe__result">
												<?php if ( null !== $probe_error && '' !== $probe_error ) : ?>
																<div class="notice notice-error"><p><?php echo esc_html( $probe_error ); ?></p></div>
						<?php elseif ( null !== $result ) : ?>
								<pre class="fbm-token-probe__output"><code><?php echo esc_html( wp_json_encode( $result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) ); ?></code></pre>
						<?php else : ?>
								<p class="description"><?php esc_html_e( 'No diagnostics available for the provided payload.', 'foodbank-manager' ); ?></p>
						<?php endif; ?>
				</div>
		<?php endif; ?>
</section>

<section class="fbm-token-failures" aria-labelledby="fbm-token-failures-heading">
		<h3 id="fbm-token-failures-heading"><?php esc_html_e( 'Token Resend Failures', 'foodbank-manager' ); ?></h3>
		<?php if ( empty( $token_failures ) ) : ?>
				<p class="description"><?php esc_html_e( 'No token resend failures have been recorded.', 'foodbank-manager' ); ?></p>
		<?php else : ?>
				<ul class="fbm-token-failures__list" role="list">
						<?php
						foreach ( $token_failures as $failure ) :
								$recorded_at                              = isset( $failure['recorded_at'] ) ? (string) $failure['recorded_at'] : '';
								$reference                                = isset( $failure['member_reference'] ) ? (string) $failure['member_reference'] : '';
								$context                                  = isset( $failure['context'] ) ? (string) $failure['context'] : '';
																$attempts = isset( $failure['attempts'] ) ? absint( $failure['attempts'] ) : 0;
							?>
								<li class="fbm-token-failures__item">
										<span class="fbm-token-failures__timestamp"><?php echo esc_html( $recorded_at ); ?></span>
										<span class="fbm-token-failures__reference"><?php echo esc_html( $reference ); ?></span>
										<span class="fbm-token-failures__context"><?php echo esc_html( $context ); ?></span>
																				<span class="fbm-token-failures__attempts">
																								<?php
																								$attempts_label = sprintf(
																										/* translators: %d: Number of resend attempts. */
																									esc_html__( '%d attempts', 'foodbank-manager' ),
																									$attempts
																								);
																								echo esc_html( $attempts_label );
																								?>
																				</span>
								</li>
						<?php endforeach; ?>
				</ul>
		<?php endif; ?>
</section>
