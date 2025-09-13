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
<?php echo '<div id="fbm-ui" class="fbm-scope fbm-app">'; ?>
<div class="wrap fbm-admin">
<?php \FBM\Core\Trace::mark( 'admin:emails' ); ?>
				<h1><?php esc_html_e( 'Email Templates', 'foodbank-manager' ); ?></h1>
				<?php if ( ! empty( $logs ) ) : ?>
				<h2><?php esc_html_e( 'Email Log', 'foodbank-manager' ); ?></h2>
				<table class="widefat">
						<thead>
								<tr>
										<th><?php esc_html_e( 'Subject', 'foodbank-manager' ); ?></th>
										<th><?php esc_html_e( 'Actions', 'foodbank-manager' ); ?></th>
								</tr>
						</thead>
						<tbody>
								<?php foreach ( $logs as $row ) : ?>
								<tr>
										<td><?php echo esc_html( $row['subject'] ?? '' ); ?></td>
										<td>
												<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
														<input type="hidden" name="action" value="fbm_mail_resend" />
														<input type="hidden" name="id" value="<?php echo esc_attr( (string) ( $row['id'] ?? 0 ) ); ?>" />
														<?php wp_nonce_field( 'fbm_mail_resend' ); ?>
														<button type="submit" class="button"><?php esc_html_e( 'Resend', 'foodbank-manager' ); ?></button>
												</form>
										</td>
								</tr>
								<?php endforeach; ?>
						</tbody>
				</table>
				<?php endif; ?>
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
				<form method="post" class="fbm-email-form">
						<?php wp_nonce_field( 'fbm_emails_save', '_fbm_nonce' ); ?>
						<input type="hidden" name="fbm_action" value="emails_save" />
						<input type="hidden" name="tpl" value="<?php echo esc_attr( $current ); ?>" />
						<p>
								<label for="fbm-subject"><?php esc_html_e( 'Subject', 'foodbank-manager' ); ?></label><br/>
								<input type="text" id="fbm-subject" class="regular-text" name="subject"
										aria-describedby="fbm-subject-desc" aria-controls="fbm-preview-subject"
										value="<?php echo esc_attr( $templates[ $current ]['subject'] ); ?>" />
								<span id="fbm-subject-desc" class="description"><?php esc_html_e( 'Email subject line.', 'foodbank-manager' ); ?></span>
						</p>
						<p>
								<label for="fbm-body"><?php esc_html_e( 'Body', 'foodbank-manager' ); ?></label><br />
								<textarea id="fbm-body" class="large-text" rows="10" name="body_html"
										aria-describedby="fbm-body-desc" aria-controls="fbm-preview-body">
										<?php
										echo esc_textarea( $templates[ $current ]['body_html'] );
										?>
										</textarea>
								<span id="fbm-body-desc" class="description"><?php esc_html_e( 'Email HTML body.', 'foodbank-manager' ); ?></span>
						</p>
						<p>
								<button type="submit" class="button button-primary"><?php esc_html_e( 'Save', 'foodbank-manager' ); ?></button>
						</p>
				</form>
				<form method="post" onsubmit="return confirm('<?php echo esc_js( __( 'Are you sure?', 'foodbank-manager' ) ); ?>');" style="margin-top:1em;">
						<?php wp_nonce_field( 'fbm_emails_reset', '_fbm_nonce' ); ?>
						<input type="hidden" name="fbm_action" value="emails_reset" />
						<input type="hidden" name="tpl" value="<?php echo esc_attr( $current ); ?>" />
						<p>
								<button type="submit" class="button"><?php esc_html_e( 'Reset to defaults', 'foodbank-manager' ); ?></button>
						</p>
				</form>
				<?php if ( ! empty( $allowed_tokens ) ) : ?>
						<h3><?php esc_html_e( 'Tokens', 'foodbank-manager' ); ?></h3>
						<ul class="fbm-token-list">
								<?php foreach ( $allowed_tokens as $token => $desc ) : ?>
										<li>
<code>{<?php echo esc_html( $token ); ?>}</code> — <?php echo esc_html( $desc ); ?>
<button type="button" class="button fbm-copy-token" data-token="<?php echo esc_attr( $token ); ?>">
									<?php esc_html_e( 'Copy', 'foodbank-manager' ); ?>
</button>
										</li>
								<?php endforeach; ?>
						</ul>
				<?php endif; ?>
				<h3><?php esc_html_e( 'Preview', 'foodbank-manager' ); ?></h3>
				<p><strong><?php esc_html_e( 'Subject', 'foodbank-manager' ); ?>:</strong> <span id="fbm-preview-subject"></span></p>
				<div id="fbm-preview-body" class="fbm-preview"></div>
				<script>
				( function() {
						const tpl = <?php echo wp_json_encode( $current ); ?>;
						const nonce = <?php echo wp_json_encode( wp_create_nonce( 'fbm_emails_preview' ) ); ?>;
						const subject = document.getElementById( 'fbm-subject' );
						const body = document.getElementById( 'fbm-body' );
						const prevSub = document.getElementById( 'fbm-preview-subject' );
						const prevBody = document.getElementById( 'fbm-preview-body' );
						function debounce( fn, delay ) { let t; return function() { clearTimeout( t ); t = setTimeout( fn, delay ); }; }
						const update = debounce( function() {
								const data = new FormData();
								data.append( 'fbm_action', 'emails_preview' );
								data.append( '_fbm_nonce', nonce );
								data.append( 'tpl', tpl );
								data.append( 'subject', subject.value );
								data.append( 'body_html', body.value );
								data.append( 'fbm_ajax', '1' );
								fetch( window.location.href, { method: 'POST', credentials: 'same-origin', body: data } )
										.then( ( r ) => r.json() )
										.then( ( res ) => { prevSub.textContent = res.subject; prevBody.innerHTML = res.body_html; } );
						}, 300 );
						subject.addEventListener( 'input', update );
						body.addEventListener( 'input', update );
						document.querySelectorAll( '.fbm-copy-token' ).forEach( function( btn ) {
								btn.addEventListener( 'click', function() { navigator.clipboard.writeText( '{' + btn.dataset.token + '}' ); } );
						} );
						update();
				}() );
				</script>
		<?php endif; ?>
</div>
<?php echo '</div>'; ?>
