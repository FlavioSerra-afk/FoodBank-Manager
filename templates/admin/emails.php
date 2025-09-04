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
<div class="wrap">
	<h1><?php esc_html_e( 'Email Templates', 'foodbank-manager' ); ?></h1>
	<ul>
		<?php foreach ( $templates as $key => $tpl ) : ?>
			<li>
				<?php echo esc_html( ucwords( str_replace( '_', ' ', $key ) ) ); ?>
				<a href="<?php echo esc_url( add_query_arg( array( 'tpl' => $key ), menu_page_url( 'fbm-emails', false ) ) ); ?>">
					<?php esc_html_e( 'Edit', 'foodbank-manager' ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>

	<?php if ( $current && isset( $templates[ $current ] ) ) : ?>
		<h2><?php esc_html_e( 'Edit Template', 'foodbank-manager' ); ?></h2>
		<form method="post">
			<p>
				<label><?php esc_html_e( 'Subject', 'foodbank-manager' ); ?><br />
					<input type="text" class="regular-text" value="<?php echo esc_attr( $templates[ $current ]['subject'] ); ?>" disabled />
				</label>
			</p>
			<p>
				<label><?php esc_html_e( 'Body', 'foodbank-manager' ); ?><br />
					<textarea class="large-text" rows="10" disabled><?php echo esc_html( $templates[ $current ]['body'] ); ?></textarea>
				</label>
			</p>
		</form>
	<?php endif; ?>
</div>
