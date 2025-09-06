<?php
/**
 * Email templates page.
 *
 * @package FoodBankManager
 */

if ( ! defined( 'ABSPATH' ) ) {
		exit;
}
?>
<div class="wrap fbm-admin">
<?php \FBM\Core\Trace::mark( 'admin:emails' ); ?>
		<h1><?php esc_html_e( 'Email Templates', 'foodbank-manager' ); ?></h1>
	<ul>
		<?php foreach ( $templates as $key => $tpl ) : ?>
			<li>
				<?php echo esc_html( ucwords( str_replace( '_', ' ', $key ) ) ); ?>
								<a href="<?php echo esc_url( add_query_arg( array( 'tpl' => $key ), menu_page_url( 'fbm_emails', false ) ) ); ?>">
					<?php esc_html_e( 'Edit', 'foodbank-manager' ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>

		<?php if ( $current && isset( $templates[ $current ] ) ) : ?>
				<h2><?php esc_html_e( 'Edit Template', 'foodbank-manager' ); ?></h2>
				<form method="post">
						<?php wp_nonce_field( 'fbm_emails_save', '_fbm_nonce' ); ?>
						<input type="hidden" name="fbm_action" value="emails_save" />
						<input type="hidden" name="tpl" value="<?php echo esc_attr( $current ); ?>" />
						<p>
								<label><?php esc_html_e( 'Subject', 'foodbank-manager' ); ?><br />
										<input type="text" class="regular-text" name="subject"
												value="<?php echo esc_attr( $templates[ $current ]['subject'] ); ?>" />
								</label>
						</p>
						<p>
								<label><?php esc_html_e( 'Body', 'foodbank-manager' ); ?><br />
										<textarea class="large-text" rows="10" name="body_html">
												<?php echo esc_textarea( $templates[ $current ]['body_html'] ); ?>
										</textarea>
								</label>
						</p>
						<?php if ( ! empty( $allowed_tokens ) ) : ?>
								<p>
										<strong><?php esc_html_e( 'Allowed tokens:', 'foodbank-manager' ); ?></strong>
										<?php foreach ( $allowed_tokens as $token ) : ?>
												<code><?php echo esc_html( '{' . $token . '}' ); ?></code>
										<?php endforeach; ?>
								</p>
						<?php endif; ?>
						<p>
								<button type="submit" class="button button-primary"><?php esc_html_e( 'Save', 'foodbank-manager' ); ?></button>
						</p>
				</form>
				<form method="post" style="margin-top:1em;">
						<?php wp_nonce_field( 'fbm_emails_preview', '_fbm_nonce' ); ?>
						<input type="hidden" name="fbm_action" value="emails_preview" />
						<input type="hidden" name="tpl" value="<?php echo esc_attr( $current ); ?>" />
						<p>
								<button type="submit" class="button"><?php esc_html_e( 'Preview', 'foodbank-manager' ); ?></button>
						</p>
				</form>
				<form method="post" style="margin-top:1em;">
						<?php wp_nonce_field( 'fbm_emails_reset', '_fbm_nonce' ); ?>
						<input type="hidden" name="fbm_action" value="emails_reset" />
						<input type="hidden" name="tpl" value="<?php echo esc_attr( $current ); ?>" />
						<p>
								<button type="submit" class="button"><?php esc_html_e( 'Reset to defaults', 'foodbank-manager' ); ?></button>
						</p>
				</form>
				<?php if ( $preview['subject'] || $preview['body_html'] ) : ?>
						<h3><?php esc_html_e( 'Preview', 'foodbank-manager' ); ?></h3>
						<p><strong><?php esc_html_e( 'Subject', 'foodbank-manager' ); ?>:</strong> <?php echo esc_html( $preview['subject'] ); ?></p>
						<div class="fbm-preview"><?php echo $preview['body_html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<?php endif; ?>
				<?php endif; ?>
</div>
