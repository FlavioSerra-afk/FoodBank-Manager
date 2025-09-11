<?php
/**
 * Shortcodes admin template.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);
?>
<div class="wrap fbm-admin">
<?php \FBM\Core\Trace::mark( 'admin:shortcodes' ); ?>
<h1><?php esc_html_e( 'Shortcodes', 'foodbank-manager' ); ?></h1>
<div class="fbm-shortcodes-examples">
		<h2><?php esc_html_e( 'Quick Examples', 'foodbank-manager' ); ?></h2>
		<div class="fbm-example">
				<code><?php echo esc_html( '[fbm_form id="123" preset="basic_intake" mask_sensitive="true"]' ); ?></code>
				<button type="button" class="fbm-copy button" data-snippet="[fbm_form id=&quot;123&quot; preset=&quot;basic_intake&quot; mask_sensitive=&quot;true&quot;]">
						<?php esc_html_e( 'Copy example', 'foodbank-manager' ); ?>
				</button>
		</div>
		<div class="fbm-example">
				<code><?php echo esc_html( '[fbm_dashboard compare="true" range="last_30" preset="manager"]' ); ?></code>
				<button type="button" class="fbm-copy button" data-snippet="[fbm_dashboard compare=&quot;true&quot; range=&quot;last_30&quot; preset=&quot;manager&quot;]">
						<?php esc_html_e( 'Copy example', 'foodbank-manager' ); ?>
				</button>
		</div>
		<p>
				<a href="<?php echo esc_url( FBM_URL . 'Docs/Shortcodes.md' ); ?>" target="_blank" rel="noopener">
						<?php esc_html_e( 'Read the full Shortcodes guide', 'foodbank-manager' ); ?>
				</a>
		</p>
</div>
<form method="post" id="fbm-shortcodes-form">
	<?php wp_nonce_field( 'fbm_shortcodes_preview', '_wpnonce' ); ?>
	<input type="hidden" name="fbm_action" value="shortcode_preview" />
	<p>
		<label for="fbm-tag"><?php esc_html_e( 'Shortcode', 'foodbank-manager' ); ?></label>
		<select name="tag" id="fbm-tag">
			<option value=""><?php esc_html_e( 'Select', 'foodbank-manager' ); ?></option>
			<?php foreach ( $shortcodes as $sc ) : ?>
				<option value="<?php echo esc_attr( $sc['tag'] ); ?>" <?php selected( $current_tag, $sc['tag'] ); ?>>
						<?php echo esc_html( $sc['tag'] ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</p>
	<div id="fbm-attrs"></div>
	<p>
		<button type="button" id="fbm-generate" class="button"><?php esc_html_e( 'Generate', 'foodbank-manager' ); ?></button>
		<button type="submit" id="fbm-preview" class="button button-primary"><?php esc_html_e( 'Preview', 'foodbank-manager' ); ?></button>
	</p>
</form>
<div id="fbm-output" style="display:none;">
	<input type="text" id="fbm-shortcode-string" readonly />
	<button type="button" class="fbm-copy button"><?php esc_html_e( 'Copy', 'foodbank-manager' ); ?></button>
</div>
<?php if ( '' !== $preview_html ) : ?>
								<h2><?php esc_html_e( 'Preview', 'foodbank-manager' ); ?></h2>
								<?php $safe_preview = wp_kses_post( $preview_html ); ?>
								<?php echo '<div class="fbm-preview">' . $safe_preview . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
<?php endif; ?>
<?php
$js  = 'window.FBM_SHORTCODES = ' . wp_json_encode( $shortcodes ) . ';';
$js .= 'window.FBM_CURRENT = {tag:' . ( $current_tag ? '"' . esc_js( $current_tag ) . '"' : 'null' ) . ', atts:' . wp_json_encode( $current_atts ) . '};';
if ( function_exists( 'wp_add_inline_script' ) ) {
		wp_add_inline_script( 'fbm-admin-shortcodes', $js, 'before' );
} else {
		echo '<script>' . $js . '</script>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
?>
</div>
