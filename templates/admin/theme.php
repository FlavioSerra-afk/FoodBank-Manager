<?php
// phpcs:ignoreFile
/**
 * Design & Theme settings template.
 *
 * @package FoodBankManager
 */

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

$tab   = isset( $_GET['tab'] ) ? sanitize_key( (string) $_GET['tab'] ) : 'admin';
$theme = isset( $theme ) ? $theme : \FoodBankManager\UI\Theme::get();
$admin = $theme['admin'];
$front = $theme['front'];
$match = ! empty( $theme['match_front_to_admin'] );
?>
<div class="wrap fbm-admin">
        <h1><?php esc_html_e( 'Design & Theme', 'foodbank-manager' ); ?></h1>
        <h2 class="nav-tab-wrapper">
                <a href="<?php echo esc_url( add_query_arg( 'tab', 'admin', menu_page_url( 'fbm_theme', false ) ) ); ?>" class="nav-tab <?php echo 'admin' === $tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Admin UI', 'foodbank-manager' ); ?></a>
                <a href="<?php echo esc_url( add_query_arg( 'tab', 'front', menu_page_url( 'fbm_theme', false ) ) ); ?>" class="nav-tab <?php echo 'front' === $tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Front-end UI', 'foodbank-manager' ); ?></a>
        </h2>
        <?php settings_errors( 'fbm_theme' ); ?>
        <div class="fbm-theme-actions">
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php?action=fbm_theme_import' ) ); ?>" enctype="multipart/form-data" style="display:inline-block;margin-right:1rem">
                        <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'fbm_theme_import' ); } ?>
                        <input type="hidden" name="section" value="<?php echo esc_attr( $tab ); ?>" />
                        <input type="file" name="theme_json" accept="application/json" />
                        <?php submit_button( __( 'Import', 'foodbank-manager' ), 'secondary', 'submit', false ); ?>
                </form>
                <a class="button" href="<?php echo esc_url( function_exists( 'wp_nonce_url' ) ? wp_nonce_url( admin_url( 'admin-post.php?action=fbm_theme_export&section=' . $tab ), 'fbm_theme_export' ) : '#' ); ?>"><?php esc_html_e( 'Export', 'foodbank-manager' ); ?></a>
        </div>
        <form method="post" action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>">
                <?php if ( function_exists( 'settings_fields' ) ) { settings_fields( 'fbm_theme' ); } ?>
                <?php if ( 'admin' === $tab ) : ?>
                        <table class="form-table" role="presentation">
                                <tr>
                                        <th><?php esc_html_e( 'Mode', 'foodbank-manager' ); ?></th>
                                        <td>
                                                <label><input type="radio" name="fbm_settings[theme][admin][style]" value="glass" <?php checked( $admin['style'], 'glass' ); ?> /> <?php esc_html_e( 'Glass', 'foodbank-manager' ); ?></label>
                                                <label><input type="radio" name="fbm_settings[theme][admin][style]" value="basic" <?php checked( $admin['style'], 'basic' ); ?> /> <?php esc_html_e( 'Basic', 'foodbank-manager' ); ?></label>
                                        </td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Preset', 'foodbank-manager' ); ?></th>
                                        <td>
                                                <label><input type="radio" name="fbm_settings[theme][admin][preset]" value="light" <?php checked( $admin['preset'], 'light' ); ?> /> <?php esc_html_e( 'Light', 'foodbank-manager' ); ?></label>
                                                <label><input type="radio" name="fbm_settings[theme][admin][preset]" value="dark" <?php checked( $admin['preset'], 'dark' ); ?> /> <?php esc_html_e( 'Dark', 'foodbank-manager' ); ?></label>
                                                <label><input type="radio" name="fbm_settings[theme][admin][preset]" value="high_contrast" <?php checked( $admin['preset'], 'high_contrast' ); ?> /> <?php esc_html_e( 'High-Contrast', 'foodbank-manager' ); ?></label>
                                        </td>
                                </tr>
                                <tr>
                                        <th><label for="fbm_admin_accent"><?php esc_html_e( 'Accent colour', 'foodbank-manager' ); ?></label></th>
                                        <td><input type="text" id="fbm_admin_accent" name="fbm_settings[theme][admin][accent]" value="<?php echo esc_attr( $admin['accent'] ); ?>" class="regular-text" /></td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Glass alpha', 'foodbank-manager' ); ?></th>
                                        <td><input type="number" min="0" max="1" step="0.01" name="fbm_settings[theme][admin][glass][alpha]" value="<?php echo esc_attr( $admin['glass']['alpha'] ); ?>" /></td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Glass blur', 'foodbank-manager' ); ?></th>
                                        <td><input type="number" min="0" max="20" step="1" name="fbm_settings[theme][admin][glass][blur]" value="<?php echo esc_attr( $admin['glass']['blur'] ); ?>" /> px</td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Elevation', 'foodbank-manager' ); ?></th>
                                        <td><input type="number" min="0" max="24" step="1" name="fbm_settings[theme][admin][glass][elev]" value="<?php echo esc_attr( $admin['glass']['elev'] ); ?>" /></td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Radius', 'foodbank-manager' ); ?></th>
                                        <td><input type="number" min="6" max="20" step="1" name="fbm_settings[theme][admin][glass][radius]" value="<?php echo esc_attr( $admin['glass']['radius'] ); ?>" /> px</td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Border', 'foodbank-manager' ); ?></th>
                                        <td><input type="number" min="1" max="2" step="1" name="fbm_settings[theme][admin][glass][border]" value="<?php echo esc_attr( $admin['glass']['border'] ); ?>" /> px</td>
                                </tr>
                        </table>
                <?php else : ?>
                        <table class="form-table" role="presentation">
                                <tr>
                                        <th><label><input type="checkbox" name="fbm_settings[theme][front][enabled]" value="1" <?php checked( $front['enabled'] ); ?> /> <?php esc_html_e( 'Apply theme to front-end shortcodes/pages', 'foodbank-manager' ); ?></label></th>
                                        <td></td>
                                </tr>
                                <tr>
                                        <th><label><input type="checkbox" name="fbm_settings[theme][match_front_to_admin]" value="1" <?php checked( $match ); ?> /> <?php esc_html_e( 'Match front-end to admin theme', 'foodbank-manager' ); ?></label></th>
                                        <td></td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Mode', 'foodbank-manager' ); ?></th>
                                        <td>
                                                <label><input type="radio" name="fbm_settings[theme][front][style]" value="glass" <?php checked( $front['style'], 'glass' ); ?> <?php echo $match ? 'disabled="disabled"' : ''; ?> /> <?php esc_html_e( 'Glass', 'foodbank-manager' ); ?></label>
                                                <label><input type="radio" name="fbm_settings[theme][front][style]" value="basic" <?php checked( $front['style'], 'basic' ); ?> <?php echo $match ? 'disabled="disabled"' : ''; ?> /> <?php esc_html_e( 'Basic', 'foodbank-manager' ); ?></label>
                                        </td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Preset', 'foodbank-manager' ); ?></th>
                                        <td>
                                                <label><input type="radio" name="fbm_settings[theme][front][preset]" value="light" <?php checked( $front['preset'], 'light' ); ?> <?php echo $match ? 'disabled="disabled"' : ''; ?> /> <?php esc_html_e( 'Light', 'foodbank-manager' ); ?></label>
                                                <label><input type="radio" name="fbm_settings[theme][front][preset]" value="dark" <?php checked( $front['preset'], 'dark' ); ?> <?php echo $match ? 'disabled="disabled"' : ''; ?> /> <?php esc_html_e( 'Dark', 'foodbank-manager' ); ?></label>
                                                <label><input type="radio" name="fbm_settings[theme][front][preset]" value="high_contrast" <?php checked( $front['preset'], 'high_contrast' ); ?> <?php echo $match ? 'disabled="disabled"' : ''; ?> /> <?php esc_html_e( 'High-Contrast', 'foodbank-manager' ); ?></label>
                                        </td>
                                </tr>
                                <tr>
                                        <th><label for="fbm_front_accent"><?php esc_html_e( 'Accent colour', 'foodbank-manager' ); ?></label></th>
                                        <td><input type="text" id="fbm_front_accent" name="fbm_settings[theme][front][accent]" value="<?php echo esc_attr( $front['accent'] ); ?>" class="regular-text" <?php echo $match ? 'disabled="disabled"' : ''; ?> /></td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Glass alpha', 'foodbank-manager' ); ?></th>
                                        <td><input type="number" min="0" max="1" step="0.01" name="fbm_settings[theme][front][glass][alpha]" value="<?php echo esc_attr( $front['glass']['alpha'] ); ?>" <?php echo $match ? 'disabled="disabled"' : ''; ?> /></td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Glass blur', 'foodbank-manager' ); ?></th>
                                        <td><input type="number" min="0" max="20" step="1" name="fbm_settings[theme][front][glass][blur]" value="<?php echo esc_attr( $front['glass']['blur'] ); ?>" <?php echo $match ? 'disabled="disabled"' : ''; ?> /> px</td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Elevation', 'foodbank-manager' ); ?></th>
                                        <td><input type="number" min="0" max="24" step="1" name="fbm_settings[theme][front][glass][elev]" value="<?php echo esc_attr( $front['glass']['elev'] ); ?>" <?php echo $match ? 'disabled="disabled"' : ''; ?> /></td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Radius', 'foodbank-manager' ); ?></th>
                                        <td><input type="number" min="6" max="20" step="1" name="fbm_settings[theme][front][glass][radius]" value="<?php echo esc_attr( $front['glass']['radius'] ); ?>" <?php echo $match ? 'disabled="disabled"' : ''; ?> /> px</td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Border', 'foodbank-manager' ); ?></th>
                                        <td><input type="number" min="1" max="2" step="1" name="fbm_settings[theme][front][glass][border]" value="<?php echo esc_attr( $front['glass']['border'] ); ?>" <?php echo $match ? 'disabled="disabled"' : ''; ?> /> px</td>
                                </tr>
                        </table>
                <?php endif; ?>
                <?php submit_button(); ?>
        </form>
</div>
<?php
// End of template.
?>
