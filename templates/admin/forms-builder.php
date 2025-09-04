<?php
/**
 * Forms builder template.
 *
 * @package FoodBankManager
 */

/**
 * Presets list.
 *
 * @var array<int,array{name:string,slug:string,updated_at:int}> $presets Presets.
 */
/**
 * Currently edited preset.
 *
 * @var array|null $current Current preset.
 */
/**
 * Save nonce.
 *
 * @var string $nonce_save Save nonce.
 */
/**
 * Delete nonce.
 *
 * @var string $nonce_delete Delete nonce.
 */
?>
<div class="wrap fbm-admin">
	<h1><?php esc_html_e( 'Forms', 'foodbank-manager' ); ?></h1>
	<div class="fbm-forms-list">
		<ul>
			<?php foreach ( $presets as $p ) : ?>
			<li><?php echo esc_html( $p['name'] ); ?> (<?php echo esc_html( $p['slug'] ); ?>)</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php if ( $current ) : ?>
	<form method="post">
		<input type="hidden" name="fbm_action" value="save" />
		<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( $nonce_save ); ?>" />
		<p><label><?php esc_html_e( 'Schema JSON', 'foodbank-manager' ); ?><br />
		<textarea name="schema" rows="10" cols="80"><?php echo esc_textarea( wp_json_encode( $current ) ); ?></textarea></label></p>
		<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Save', 'foodbank-manager' ); ?></button></p>
	</form>
	<form method="post" onsubmit="return confirm('Are you sure?');">
		<input type="hidden" name="fbm_action" value="delete" />
		<input type="hidden" name="slug" value="<?php echo esc_attr( $current['meta']['slug'] ?? '' ); ?>" />
		<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( $nonce_delete ); ?>" />
		<p><button type="submit" class="button button-secondary"><?php esc_html_e( 'Delete', 'foodbank-manager' ); ?></button></p>
	</form>
	<?php endif; ?>
</div>
