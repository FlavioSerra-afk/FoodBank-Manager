<?php
// phpcs:ignoreFile
/**
 * Theme page template.
 *
 * @package FoodBankManager
 * @since 0.1.1
 */

// phpcs:disable Generic.Files.LineLength.TooLong
if ( ! defined( 'ABSPATH' ) ) {
	exit; }
use FoodBankManager\Core\Options;
$theme   = Options::get( 'theme' );
$front   = $theme['frontend'];
$admin   = $theme['admin'];
$density = Options::get( 'theme.admin.density', 'comfortable' );
$dark    = Options::get( 'theme.admin.dark_mode', 'auto' );
$dark_cl = $dark === 'on' ? ' fbm-dark' : ( $dark === 'off' ? ' fbm-light' : '' );
$presets = array(
        'clean'    => __( 'clean', 'foodbank-manager' ),
        'classic'  => __( 'classic', 'foodbank-manager' ),
        'contrast' => __( 'contrast', 'foodbank-manager' ),
        'compact'  => __( 'compact', 'foodbank-manager' ),
        'large'    => __( 'large', 'foodbank-manager' ),
);
$shadows = array(
        'none' => __( 'none', 'foodbank-manager' ),
        'sm'   => __( 'sm', 'foodbank-manager' ),
        'md'   => __( 'md', 'foodbank-manager' ),
        'lg'   => __( 'lg', 'foodbank-manager' ),
);
$fonts   = array(
        'system'  => __( 'system', 'foodbank-manager' ),
        'inter'   => __( 'Inter', 'foodbank-manager' ),
        'roboto'  => __( 'Roboto', 'foodbank-manager' ),
        'georgia' => __( 'Georgia', 'foodbank-manager' ),
);
$dens    = array(
        'compact'     => __( 'compact', 'foodbank-manager' ),
        'comfortable' => __( 'comfortable', 'foodbank-manager' ),
        'spacious'    => __( 'spacious', 'foodbank-manager' ),
);
$dark    = array(
        'off'  => __( 'off', 'foodbank-manager' ),
        'on'   => __( 'on', 'foodbank-manager' ),
        'auto' => __( 'auto', 'foodbank-manager' ),
);
?>
<div class="wrap fbm-scope fbm-density-<?php echo esc_attr( $density ); ?><?php echo esc_attr( $dark_cl ); ?>">
	<h1><?php esc_html_e( 'Design & Theme', 'foodbank-manager' ); ?></h1>
	<?php settings_errors( 'fbm-theme' ); ?>
	<h2 class="nav-tab-wrapper">
	<a href="#frontend" class="nav-tab nav-tab-active"><?php esc_html_e( 'Front-end', 'foodbank-manager' ); ?></a>
	<a href="#admin" class="nav-tab"><?php esc_html_e( 'Admin', 'foodbank-manager' ); ?></a>
	</h2>
	<form method="post">
	<?php wp_nonce_field( 'fbm_theme_save', 'fbm_theme_nonce' ); ?>
	<div id="frontend" class="tab-section" style="display:block;">
		<table class="form-table">
                <tr><th><?php esc_html_e( 'Preset', 'foodbank-manager' ); ?></th><td><select name="fbm_theme[frontend][preset]">
                        <?php foreach ( $presets as $p => $label ) : ?>
                        <option value="<?php echo esc_attr( $p ); ?>" <?php selected( $front['preset'], $p ); ?>><?php echo esc_html( $label ); ?></option>
                        <?php endforeach; ?>
                </select></td></tr>
		<tr><th><?php esc_html_e( 'Accent color', 'foodbank-manager' ); ?></th><td><input type="color" name="fbm_theme[frontend][accent]" value="<?php echo esc_attr( $front['accent'] ); ?>" /></td></tr>
		<tr><th><?php esc_html_e( 'Radius', 'foodbank-manager' ); ?></th><td><input type="number" min="0" max="20" name="fbm_theme[frontend][radius]" value="<?php echo esc_attr( $front['radius'] ); ?>" class="small-text" /></td></tr>
                <tr><th><?php esc_html_e( 'Shadow', 'foodbank-manager' ); ?></th><td><select name="fbm_theme[frontend][shadow]">
                                                <?php foreach ( $shadows as $shadow => $label ) : ?>
                                                <option value="<?php echo esc_attr( $shadow ); ?>" <?php selected( $front['shadow'], $shadow ); ?>><?php echo esc_html( $label ); ?></option>
                                                <?php endforeach; ?>
                </select></td></tr>
                <tr><th><?php esc_html_e( 'Font family', 'foodbank-manager' ); ?></th><td><select name="fbm_theme[frontend][font_family]">
                        <?php foreach ( $fonts as $f => $label ) : ?>
                        <option value="<?php echo esc_attr( $f ); ?>" <?php selected( $front['font_family'], $f ); ?>><?php echo esc_html( $label ); ?></option>
                        <?php endforeach; ?>
                </select> <label><input type="checkbox" name="fbm_theme[frontend][load_font]" value="1" <?php checked( $front['load_font'] ); ?> /> <?php esc_html_e( 'Load web font', 'foodbank-manager' ); ?></label></td></tr>
                <tr><th><?php esc_html_e( 'Density', 'foodbank-manager' ); ?></th><td><select name="fbm_theme[frontend][density]">
                        <?php foreach ( $dens as $d => $label ) : ?>
                        <option value="<?php echo esc_attr( $d ); ?>" <?php selected( $front['density'], $d ); ?>><?php echo esc_html( $label ); ?></option>
                        <?php endforeach; ?>
                </select></td></tr>
                <tr><th><?php esc_html_e( 'Dark mode', 'foodbank-manager' ); ?></th><td><select name="fbm_theme[frontend][dark_mode]">
                        <?php foreach ( $dark as $d => $label ) : ?>
                        <option value="<?php echo esc_attr( $d ); ?>" <?php selected( $front['dark_mode'], $d ); ?>><?php echo esc_html( $label ); ?></option>
                        <?php endforeach; ?>
                </select></td></tr>
		<tr><th><?php esc_html_e( 'Custom CSS', 'foodbank-manager' ); ?></th><td><textarea name="fbm_theme[frontend][custom_css]" rows="5" class="large-text"><?php echo esc_textarea( $front['custom_css'] ); ?></textarea></td></tr>
		</table>
	</div>
	<div id="admin" class="tab-section" style="display:none;">
		<table class="form-table">
                <tr><th><?php esc_html_e( 'Preset', 'foodbank-manager' ); ?></th><td><select name="fbm_theme[admin][preset]">
                        <?php foreach ( $presets as $p => $label ) : ?>
                        <option value="<?php echo esc_attr( $p ); ?>" <?php selected( $admin['preset'], $p ); ?>><?php echo esc_html( $label ); ?></option>
                        <?php endforeach; ?>
                </select></td></tr>
		<tr><th><?php esc_html_e( 'Accent color', 'foodbank-manager' ); ?></th><td><input type="color" name="fbm_theme[admin][accent]" value="<?php echo esc_attr( $admin['accent'] ); ?>" /></td></tr>
		<tr><th><?php esc_html_e( 'Radius', 'foodbank-manager' ); ?></th><td><input type="number" min="0" max="20" name="fbm_theme[admin][radius]" value="<?php echo esc_attr( $admin['radius'] ); ?>" class="small-text" /></td></tr>
                <tr><th><?php esc_html_e( 'Shadow', 'foodbank-manager' ); ?></th><td><select name="fbm_theme[admin][shadow]">
                                                <?php foreach ( $shadows as $shadow => $label ) : ?>
                                                <option value="<?php echo esc_attr( $shadow ); ?>" <?php selected( $admin['shadow'], $shadow ); ?>><?php echo esc_html( $label ); ?></option>
                                                <?php endforeach; ?>
                </select></td></tr>
                <tr><th><?php esc_html_e( 'Font family', 'foodbank-manager' ); ?></th><td><select name="fbm_theme[admin][font_family]">
                        <?php foreach ( $fonts as $f => $label ) : ?>
                        <option value="<?php echo esc_attr( $f ); ?>" <?php selected( $admin['font_family'], $f ); ?>><?php echo esc_html( $label ); ?></option>
                        <?php endforeach; ?>
                </select> <label><input type="checkbox" name="fbm_theme[admin][load_font]" value="1" <?php checked( $admin['load_font'] ); ?> /> <?php esc_html_e( 'Load web font', 'foodbank-manager' ); ?></label></td></tr>
                <tr><th><?php esc_html_e( 'Density', 'foodbank-manager' ); ?></th><td><select name="fbm_theme[admin][density]">
                        <?php foreach ( $dens as $d => $label ) : ?>
                        <option value="<?php echo esc_attr( $d ); ?>" <?php selected( $admin['density'], $d ); ?>><?php echo esc_html( $label ); ?></option>
                        <?php endforeach; ?>
                </select></td></tr>
                <tr><th><?php esc_html_e( 'Dark mode', 'foodbank-manager' ); ?></th><td><select name="fbm_theme[admin][dark_mode]">
                        <?php foreach ( $dark as $d => $label ) : ?>
                        <option value="<?php echo esc_attr( $d ); ?>" <?php selected( $admin['dark_mode'], $d ); ?>><?php echo esc_html( $label ); ?></option>
                        <?php endforeach; ?>
                </select></td></tr>
		<tr><th><?php esc_html_e( 'Custom CSS', 'foodbank-manager' ); ?></th><td><textarea name="fbm_theme[admin][custom_css]" rows="5" class="large-text"><?php echo esc_textarea( $admin['custom_css'] ); ?></textarea></td></tr>
		</table>
	</div>
	<?php submit_button(); ?>
	</form>
	<h2><?php esc_html_e( 'Export/Import', 'foodbank-manager' ); ?></h2>
	<form method="post">
	<?php wp_nonce_field( 'fbm_theme_export', 'fbm_theme_export_nonce' ); ?>
	<p><button type="submit" name="fbm_theme_export" class="button"><?php esc_html_e( 'Export Theme', 'foodbank-manager' ); ?></button></p>
	</form>
	<form method="post" enctype="multipart/form-data">
	<?php wp_nonce_field( 'fbm_theme_import', 'fbm_theme_import_nonce' ); ?>
	<p><input type="file" name="theme_file" accept="application/json" /> <button type="submit" name="fbm_theme_import" class="button"><?php esc_html_e( 'Import Theme', 'foodbank-manager' ); ?></button></p>
	</form>
</div>
<script>
(function(){
	const tabs=document.querySelectorAll('.nav-tab');
	tabs.forEach(tab=>tab.addEventListener('click',function(e){e.preventDefault();tabs.forEach(t=>t.classList.remove('nav-tab-active'));tab.classList.add('nav-tab-active');document.querySelectorAll('.tab-section').forEach(sec=>sec.style.display='none');document.querySelector(tab.getAttribute('href')).style.display='block';}));
})();
</script>
