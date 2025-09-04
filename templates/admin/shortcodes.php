<?php
/**
 * Shortcodes admin template.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);
?>
<div class="wrap">
<h1><?php esc_html_e( 'Shortcodes', 'foodbank-manager' ); ?></h1>
<table class="widefat striped">
<thead>
<tr>
<th><?php esc_html_e( 'Shortcode', 'foodbank-manager' ); ?></th>
<th><?php esc_html_e( 'Attributes', 'foodbank-manager' ); ?></th>
<th><?php esc_html_e( 'Example', 'foodbank-manager' ); ?></th>
<th><?php esc_html_e( 'Docs', 'foodbank-manager' ); ?></th>
</tr>
</thead>
<tbody>
<?php
foreach ( $shortcodes as $sc ) :
	$sc_tag   = $sc['tag'];
	$atts     = $sc['atts'];
	$lines    = array();
	$examples = array();
	foreach ( $atts as $name => $default ) {
			$attr_type = gettype( $default );
			$lines[]   = esc_html( $name ) . ' (' . esc_html( $attr_type ) . ', '
					. esc_html__( 'default', 'foodbank-manager' ) . ': "'
					. esc_html( (string) $default ) . '")';
			$value     = (string) $default;
		if ( strpos( $name, 'email' ) !== false ) {
			$value = '***';
		}
		$examples[] = $name . '="' . $value . '"';
	}
	$attr_html = $lines ? implode( '<br>', $lines ) : '<em>' . esc_html__( 'None', 'foodbank-manager' ) . '</em>';
	$example   = '[' . $sc_tag . ( $examples ? ' ' . implode( ' ', $examples ) : '' ) . ']';
	?>
<tr>
<td><code><?php echo esc_html( '[' . $sc_tag . ']' ); ?></code></td>
<td><?php echo wp_kses_post( $attr_html ); ?></td>
<td>
<code><?php echo esc_html( $example ); ?></code>
<button type="button" class="fbm-copy" data-clipboard="<?php echo esc_attr( $example ); ?>">
	<?php esc_html_e( 'Copy', 'foodbank-manager' ); ?></button>
</td>
<td><a href="<?php echo esc_url( plugins_url( 'Docs/README.md#shortcodes', FBM_FILE ) ); ?>"
target="_blank"><?php esc_html_e( 'Docs', 'foodbank-manager' ); ?></a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<script>
document.addEventListener(
'click',
function ( e ) {
if ( e.target.classList.contains( 'fbm-copy' ) ) {
navigator.clipboard.writeText( e.target.dataset.clipboard );
}
}
);
</script>
