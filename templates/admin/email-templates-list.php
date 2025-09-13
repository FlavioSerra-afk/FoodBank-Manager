<?php
/**
 * Email Templates list view.
 *
 * @var array<string,array<string,mixed>> $templates
 * @var string                            $current
 */
?>
<?php echo '<div id="fbm-ui" class="fbm-scope fbm-app">'; ?>
<div class="wrap fbm-admin">
<h1><?php echo esc_html__( 'Email Templates', 'foodbank-manager' ); ?></h1>
<ul>
<?php foreach ( $templates as $slug => $tpl ) : ?>
	<li><a href="<?php echo esc_url( add_query_arg( 'slug', $slug ) ); ?>"><?php echo esc_html( $slug ); ?></a></li>
<?php endforeach; ?>
</ul>
<?php
if ( $current ) {
	$tpl  = $templates[ $current ] ?? array();
	$slug = $current;
	require FBM_PATH . 'templates/admin/email-templates-edit.php';
}
?>
</div>
<?php echo '</div>'; ?>
