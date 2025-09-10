<?php
/**
 * Edit template form.
 *
 * @var string $slug
 * @var array<string,mixed> $tpl
 */
?>
<form method="post">
	<input type="hidden" name="fbm_action" value="save">
	<input type="hidden" name="slug" value="<?php echo esc_attr( $slug ); ?>">
	<?php wp_nonce_field( 'fbm_email_templates_save', '_fbm_nonce' ); ?>
	<p>
		<label><?php esc_html_e( 'Subject', 'foodbank-manager' ); ?><br>
		<input type="text" name="subject" value="<?php echo esc_attr( $tpl['subject'] ?? '' ); ?>" class="regular-text"></label>
	</p>
	<p>
		<label><?php esc_html_e( 'Body', 'foodbank-manager' ); ?><br>
		<textarea name="body" rows="10" cols="50" class="large-text code"><?php echo esc_textarea( $tpl['body'] ?? '' ); ?></textarea></label>
	</p>
	<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Save', 'foodbank-manager' ); ?></button></p>
</form>
