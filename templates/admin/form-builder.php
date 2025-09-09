<?php
/**
 * Form builder admin template.
 *
 * @package FoodBankManager
 * @var array $forms
 * @var array|null $current
 */

declare(strict_types=1);

?>
<div class="wrap fbm-admin">
<h1><?php esc_html_e( 'Form Builder', 'foodbank-manager' ); ?></h1>
<?php if ( $current ) : ?>
<form method="post">
	<?php wp_nonce_field( 'fbm_form_save', '_fbm_nonce' ); ?>
	<input type="hidden" name="form_id" value="<?php echo (int) $current['id']; ?>" />
		<p><label><?php esc_html_e( 'Title', 'foodbank-manager' ); ?>
				<input type="text" name="title" value="<?php echo esc_attr( $current['title'] ); ?>" /></label></p>
		<textarea id="schema_json" name="schema_json" hidden>
				<?php echo esc_html( wp_json_encode( $current['schema'] ) ); ?>
		</textarea>
		<p><label><input type="checkbox" name="mask_sensitive" value="1" <?php checked( $current['mask_sensitive'] ); ?> />
				<?php esc_html_e( 'Mask sensitive fields', 'foodbank-manager' ); ?></label></p>
	<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Save', 'foodbank-manager' ); ?></button></p>
</form>
<form method="post" style="margin-top:1em;">
	<?php wp_nonce_field( 'fbm_form_delete', '_fbm_nonce' ); ?>
	<input type="hidden" name="form_id" value="<?php echo (int) $current['id']; ?>" />
	<input type="hidden" name="delete_form" value="1" />
	<button type="submit" class="button"><?php esc_html_e( 'Delete', 'foodbank-manager' ); ?></button>
</form>
<div class="fbm-preview">
	<?php echo do_shortcode( '[fbm_form id="' . (int) $current['id'] . '"]' ); ?>
</div>
<?php else : ?>
<p><a class="button" href="
	<?php
	echo esc_attr(
		add_query_arg(
			array(
				'page' => 'fbm_form_builder',
				'new'  => 1,
			),
			admin_url( 'admin.php' )
		)
	);
	?>
							"><?php esc_html_e( 'New Form', 'foodbank-manager' ); ?></a></p>
<ul>
	<?php foreach ( $forms as $f ) : ?>
	<li><a href="
		<?php
		echo esc_attr(
			add_query_arg(
				array(
					'page'    => 'fbm_form_builder',
					'form_id' => $f['id'],
				),
				admin_url( 'admin.php' )
			)
		);
		?>
					"><?php echo esc_html( $f['title'] ); ?></a></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
</div>
