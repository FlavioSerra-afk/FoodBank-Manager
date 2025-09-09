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
$tokens = \FoodBankManager\UI\Theme::admin();
$fonts = array(
        'system' => array(
                'label' => __( 'System', 'foodbank-manager' ),
                'css'   => 'system-ui, sans-serif',
        ),
        'inter'  => array(
                'label' => __( 'Inter', 'foodbank-manager' ),
                'css'   => '"Inter", system-ui, sans-serif',
        ),
        'roboto' => array(
                'label' => __( 'Roboto', 'foodbank-manager' ),
                'css'   => '"Roboto", system-ui, sans-serif',
        ),
);
$densities = array(
        'compact'     => __( 'Compact', 'foodbank-manager' ),
        'comfortable' => __( 'Comfortable', 'foodbank-manager' ),
);
?>
<div class="wrap fbm-admin">
<?php \FBM\Core\Trace::mark( 'admin:theme' ); ?>
        <style><?php echo \FoodBankManager\UI\Theme::to_css_vars( $tokens, '.fbm-admin' ); ?></style>
        <h1><?php esc_html_e( 'Design & Theme', 'foodbank-manager' ); ?></h1>
        <div class="fbm-theme-preview<?php echo $tokens['dark_mode'] ? ' is-dark' : ''; ?>" id="fbm-theme-preview">
                <div class="fbm-theme-preview__swatch" id="fbm-theme-swatch"></div>
                <span class="fbm-theme-preview__font" id="fbm-theme-font">Aa</span>
                <span class="fbm-theme-preview__density" id="fbm-theme-density"><?php echo esc_html( $tokens['density'] ); ?></span>
                <button type="button" class="button" id="fbm-theme-dark-toggle"><?php esc_html_e( 'Toggle dark', 'foodbank-manager' ); ?></button>
        </div>
        <?php if ( isset( $_GET['notice'] ) && 'saved' === $_GET['notice'] ) : ?>
                <div class="notice notice-success"><p><?php esc_html_e( 'Settings saved.', 'foodbank-manager' ); ?></p></div>
        <?php endif; ?>
        <form method="post">
                <?php wp_nonce_field( 'fbm_theme_save' ); ?>
                <table class="form-table" role="presentation">
                        <tr>
                                <th scope="row"><label for="fbm_primary_color"><?php esc_html_e( 'Primary colour', 'foodbank-manager' ); ?></label></th>
                                <td>
                                        <input type="text" id="fbm_primary_color" name="fbm_theme[primary_color]" value="<?php echo esc_attr( $tokens['primary_color'] ); ?>" class="regular-text" />
                                </td>
                        </tr>
                        <tr>
                                <th scope="row"><label for="fbm_density"><?php esc_html_e( 'Density', 'foodbank-manager' ); ?></label></th>
                                <td>
                                        <select id="fbm_density" name="fbm_theme[density]">
                                                <?php foreach ( $densities as $val => $label ) : ?>
                                                        <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $tokens['density'], $val ); ?>><?php echo esc_html( $label ); ?></option>
                                                <?php endforeach; ?>
                                        </select>
                                </td>
                        </tr>
                        <tr>
                                <th scope="row"><label for="fbm_font"><?php esc_html_e( 'Font', 'foodbank-manager' ); ?></label></th>
                                <td>
                                        <select id="fbm_font" name="fbm_theme[font_family]">
                                                <?php foreach ( $fonts as $val => $info ) : ?>
                                                        <option value="<?php echo esc_attr( $val ); ?>" data-css="<?php echo esc_attr( $info['css'] ); ?>" <?php selected( $tokens['font'], $val ); ?>><?php echo esc_html( $info['label'] ); ?></option>
                                                <?php endforeach; ?>
                                        </select>
                                </td>
                        </tr>
                        <tr>
                                <th scope="row"><?php esc_html_e( 'Dark mode default', 'foodbank-manager' ); ?></th>
                                <td><label><input type="checkbox" id="fbm_dark_mode" name="fbm_theme[dark_mode_default]" value="1" <?php checked( $tokens['dark_mode'] ); ?> /> <?php esc_html_e( 'Enable', 'foodbank-manager' ); ?></label></td>
                        </tr>
                        <tr>
                                <th scope="row"><label for="fbm_custom_css"><?php esc_html_e( 'Custom CSS', 'foodbank-manager' ); ?></label></th>
                                <td>
                                        <textarea id="fbm_custom_css" name="fbm_theme[custom_css]" rows="8" class="large-text code"><?php echo esc_textarea( $theme['custom_css'] ?? '' ); ?></textarea>
                                        <p class="description"><?php printf( esc_html__(
                                                /* translators: %d: length of custom CSS after sanitisation in bytes */
                                                '%d bytes after sanitisation.',
                                                'foodbank-manager'
                                        ), strlen( $theme['custom_css'] ?? '' ) ); ?></p>
                                </td>
                        </tr>
                </table>
                <?php submit_button(); ?>
        </form>
<script>
( function() {
        var root = document.querySelector( '.fbm-admin' );
        var color = document.getElementById( 'fbm_primary_color' );
        var swatch = document.getElementById( 'fbm-theme-swatch' );
        var density = document.getElementById( 'fbm_density' );
        var densityOut = document.getElementById( 'fbm-theme-density' );
        var font = document.getElementById( 'fbm_font' );
        var fontOut = document.getElementById( 'fbm-theme-font' );
        var darkDefault = document.getElementById( 'fbm_dark_mode' );
        var darkToggle = document.getElementById( 'fbm-theme-dark-toggle' );
        var preview = document.getElementById( 'fbm-theme-preview' );
        if ( color && root && swatch ) {
                swatch.style.background = color.value;
                root.style.setProperty( '--fbm-primary', color.value );
                color.addEventListener( 'input', function() {
                        swatch.style.background = color.value;
                        root.style.setProperty( '--fbm-primary', color.value );
                } );
        }
        if ( density && root && densityOut ) {
                root.style.setProperty( '--fbm-density', density.value );
                densityOut.textContent = density.value;
                density.addEventListener( 'change', function() {
                        root.style.setProperty( '--fbm-density', density.value );
                        densityOut.textContent = density.value;
                } );
        }
        if ( font && root && fontOut ) {
                var applyFont = function() {
                        var css = font.options[ font.selectedIndex ].dataset.css;
                        root.style.setProperty( '--fbm-font', css );
                        fontOut.style.fontFamily = css;
                };
                applyFont();
                font.addEventListener( 'change', applyFont );
        }
        if ( darkDefault && root ) {
                root.style.setProperty( '--fbm-dark', darkDefault.checked ? '1' : '0' );
                darkDefault.addEventListener( 'change', function() {
                        root.style.setProperty( '--fbm-dark', darkDefault.checked ? '1' : '0' );
                } );
        }
        if ( darkToggle && preview ) {
                darkToggle.addEventListener( 'click', function() {
                        preview.classList.toggle( 'is-dark' );
                } );
        }
} )();
</script>
</div>
