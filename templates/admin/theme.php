<?php
// phpcs:ignoreFile
/**
 * Theme settings template.
 *
 * @package FoodBankManager
 */

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}
$fonts = array(
        'system'    => __( 'System', 'foodbank-manager' ),
        'inter'     => __( 'Inter', 'foodbank-manager' ),
        'roboto'    => __( 'Roboto', 'foodbank-manager' ),
        'open-sans' => __( 'Open Sans', 'foodbank-manager' ),
);
$densities = array(
        'compact'     => __( 'Compact', 'foodbank-manager' ),
        'comfortable' => __( 'Comfortable', 'foodbank-manager' ),
);
?>
<div class="fbm-admin"><div class="wrap">
        <h1><?php esc_html_e( 'Design & Theme', 'foodbank-manager' ); ?></h1>
        <?php if ( isset( $_GET['notice'] ) && 'saved' === $_GET['notice'] ) : ?>
                <div class="notice notice-success"><p><?php esc_html_e( 'Settings saved.', 'foodbank-manager' ); ?></p></div>
        <?php endif; ?>
        <form method="post">
                <?php wp_nonce_field( 'fbm_theme_save' ); ?>
                <table class="form-table" role="presentation">
                        <tr>
                                <th scope="row"><label for="fbm_primary_color"><?php esc_html_e( 'Primary colour', 'foodbank-manager' ); ?></label></th>
                                <td>
                                        <input type="text" id="fbm_primary_color" name="fbm_theme[primary_color]" value="<?php echo esc_attr( $theme['primary_color'] ); ?>" class="regular-text" />
                                        <span class="color-swatch" style="display:inline-block;width:24px;height:24px;margin-left:8px;border:1px solid #ccc;background:<?php echo esc_attr( $theme['primary_color'] ); ?>;"></span>
                                </td>
                        </tr>
                        <tr>
                                <th scope="row"><label for="fbm_density"><?php esc_html_e( 'Density', 'foodbank-manager' ); ?></label></th>
                                <td>
                                        <select id="fbm_density" name="fbm_theme[density]">
                                                <?php foreach ( $densities as $val => $label ) : ?>
                                                        <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $theme['density'], $val ); ?>><?php echo esc_html( $label ); ?></option>
                                                <?php endforeach; ?>
                                        </select>
                                </td>
                        </tr>
                        <tr>
                                <th scope="row"><label for="fbm_font"><?php esc_html_e( 'Font', 'foodbank-manager' ); ?></label></th>
                                <td>
                                        <select id="fbm_font" name="fbm_theme[font_family]">
                                                <?php foreach ( $fonts as $val => $label ) : ?>
                                                        <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $theme['font_family'], $val ); ?>><?php echo esc_html( $label ); ?></option>
                                                <?php endforeach; ?>
                                        </select>
                                </td>
                        </tr>
                        <tr>
                                <th scope="row"><?php esc_html_e( 'Dark mode default', 'foodbank-manager' ); ?></th>
                                <td><label><input type="checkbox" name="fbm_theme[dark_mode_default]" value="1" <?php checked( $theme['dark_mode_default'] ); ?> /> <?php esc_html_e( 'Enable', 'foodbank-manager' ); ?></label></td>
                        </tr>
                        <tr>
                                <th scope="row"><label for="fbm_custom_css"><?php esc_html_e( 'Custom CSS', 'foodbank-manager' ); ?></label></th>
                                <td>
                                        <textarea id="fbm_custom_css" name="fbm_theme[custom_css]" rows="8" class="large-text code"><?php echo esc_textarea( $theme['custom_css'] ); ?></textarea>
                                        <p class="description"><?php printf( esc_html__( '%d bytes after sanitisation.', 'foodbank-manager' ), strlen( $theme['custom_css'] ) ); ?></p>
                                </td>
                        </tr>
                </table>
                <?php submit_button(); ?>
        </form>
<script>
( function() {
        var field = document.getElementById( 'fbm_primary_color' );
        if ( field ) {
                field.addEventListener( 'input', function() {
                        document.querySelector( '.color-swatch' ).style.background = field.value;
                } );
        }
} )();
</script>
</div></div>
