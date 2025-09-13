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
$theme               = isset( $theme ) ? $theme : \FoodBankManager\UI\Theme::get();
$admin               = $theme['admin'];
$front               = $theme['front'];
$menu                = $theme['menu'];
$match               = ! empty( $theme['match_front_to_admin'] );
$apply_admin  = ! empty( $theme['apply_admin'] ?? $theme['apply_admin_chrome'] );
$apply_front_menus   = ! empty( $theme['apply_front_menus'] );
?>
<?php echo '<div id="fbm-ui" class="fbm-scope fbm-app">'; ?>
<div class="wrap fbm-admin">
        <style>.fbm-theme-screen{display:flex;gap:1rem}.fbm-theme-controls{flex:1}.fbm-theme-preview-wrap{flex:1;border-left:1px solid var(--fbm-color-border);padding-left:1rem}</style>
        <h1><?php esc_html_e( 'Design & Theme', 'foodbank-manager' ); ?></h1>
        <h2 class="nav-tab-wrapper">
                <a href="<?php echo esc_url( add_query_arg( 'tab', 'admin', menu_page_url( 'fbm_theme', false ) ) ); ?>" class="nav-tab <?php echo 'admin' === $tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Admin UI', 'foodbank-manager' ); ?></a>
                <a href="<?php echo esc_url( add_query_arg( 'tab', 'front', menu_page_url( 'fbm_theme', false ) ) ); ?>" class="nav-tab <?php echo 'front' === $tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Front-end UI', 'foodbank-manager' ); ?></a>
        </h2>
        <?php settings_errors( 'fbm_theme' ); ?>
        <div class="fbm-theme-screen">
        <div class="fbm-theme-controls">
        <div class="fbm-theme-actions">
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php?action=fbm_theme_import' ) ); ?>" enctype="multipart/form-data" style="display:inline-block;margin-right:1rem">
                        <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'fbm_theme_import' ); } ?>
                        <input type="hidden" name="section" value="<?php echo esc_attr( $tab ); ?>" />
                        <input type="file" name="theme_json" accept="application/json" />
                        <?php submit_button( __( 'Import', 'foodbank-manager' ), 'secondary', 'submit', false ); ?>
                </form>
                <a class="button" href="<?php echo esc_url( function_exists( 'wp_nonce_url' ) ? wp_nonce_url( admin_url( 'admin-post.php?action=fbm_theme_export&section=' . $tab ), 'fbm_theme_export' ) : '#' ); ?>"><?php esc_html_e( 'Export', 'foodbank-manager' ); ?></a>
                <button type="button" class="button fbm-reset-all"><?php esc_html_e( 'Restore defaults', 'foodbank-manager' ); ?></button>
        </div>
        <form method="post" action="options.php">
                <?php if ( function_exists( 'settings_fields' ) ) { settings_fields( 'fbm_theme' ); } ?>
                <?php if ( function_exists( 'do_settings_sections' ) ) { do_settings_sections( 'fbm_theme' ); } ?>
                <?php if ( 'admin' === $tab ) : ?>
                        <table class="form-table" role="presentation">
                                <tr>
<th><label><input type="checkbox" name="fbm_theme[apply_admin]" value="1" <?php checked( $apply_admin ); ?> /> <?php esc_html_e( 'Apply theme to FBM interface', 'foodbank-manager' ); ?></label></th>
                                        <td><p class="description"><?php esc_html_e( 'Affects only FoodBank Manager pages/tabs; will not change the WordPress sidebar or other plugins.', 'foodbank-manager' ); ?></p></td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Mode', 'foodbank-manager' ); ?></th>
                                        <td>
                                                <label><input type="radio" name="fbm_theme[admin][style]" value="glass" <?php checked( $admin['style'], 'glass' ); ?> /> <?php esc_html_e( 'Glass', 'foodbank-manager' ); ?></label>
                                                <label><input type="radio" name="fbm_theme[admin][style]" value="basic" <?php checked( $admin['style'], 'basic' ); ?> /> <?php esc_html_e( 'Basic', 'foodbank-manager' ); ?></label>
                                        </td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Preset', 'foodbank-manager' ); ?></th>
                                        <td>
                                                <label><input type="radio" name="fbm_theme[admin][preset]" value="light" <?php checked( $admin['preset'], 'light' ); ?> /> <?php esc_html_e( 'Light', 'foodbank-manager' ); ?></label>
                                                <label><input type="radio" name="fbm_theme[admin][preset]" value="dark" <?php checked( $admin['preset'], 'dark' ); ?> /> <?php esc_html_e( 'Dark', 'foodbank-manager' ); ?></label>
                                                <label><input type="radio" name="fbm_theme[admin][preset]" value="high_contrast" <?php checked( $admin['preset'], 'high_contrast' ); ?> /> <?php esc_html_e( 'High-Contrast', 'foodbank-manager' ); ?></label>
                                        </td>
                                </tr>
                                <tr>
                                        <th><label for="fbm_admin_accent"><?php esc_html_e( 'Accent colour', 'foodbank-manager' ); ?></label></th>
                                        <td><input data-token="--fbm-accent" data-default="<?php echo esc_attr( $admin['accent'] ); ?>" type="text" id="fbm_admin_accent" name="fbm_theme[admin][accent]" value="<?php echo esc_attr( $admin['accent'] ); ?>" class="regular-text fbm-color" data-default-color="<?php echo esc_attr( \FoodBankManager\UI\Theme::DEFAULT_ACCENT ); ?>" /></td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Glass alpha', 'foodbank-manager' ); ?></th>
                                        <td><input type="number" min="0" max="1" step="0.01" name="fbm_theme[admin][glass][alpha]" value="<?php echo esc_attr( $admin['glass']['alpha'] ); ?>" /></td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Glass blur', 'foodbank-manager' ); ?></th>
                                        <td><input type="number" min="0" max="20" step="1" name="fbm_theme[admin][glass][blur]" value="<?php echo esc_attr( $admin['glass']['blur'] ); ?>" /> px</td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Elevation', 'foodbank-manager' ); ?></th>
                                        <td><input type="number" min="0" max="24" step="1" name="fbm_theme[admin][glass][elev]" value="<?php echo esc_attr( $admin['glass']['elev'] ); ?>" /></td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Radius', 'foodbank-manager' ); ?></th>
                                        <td><input type="number" min="6" max="20" step="1" name="fbm_theme[admin][glass][radius]" value="<?php echo esc_attr( $admin['glass']['radius'] ); ?>" /> px</td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Border', 'foodbank-manager' ); ?></th>
                                        <td><input type="number" min="1" max="2" step="1" name="fbm_theme[admin][glass][border]" value="<?php echo esc_attr( $admin['glass']['border'] ); ?>" /> px</td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Button background', 'foodbank-manager' ); ?></th>
                                        <td><input type="text" name="fbm_theme[admin][aliases][button_bg]" value="<?php echo esc_attr( $admin['aliases']['button_bg'] ?? '' ); ?>" class="regular-text fbm-color" data-default-color="<?php echo esc_attr( $admin['accent'] ); ?>" /></td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Link colour', 'foodbank-manager' ); ?></th>
                                        <td><input type="text" name="fbm_theme[admin][aliases][link_fg]" value="<?php echo esc_attr( $admin['aliases']['link_fg'] ?? '' ); ?>" class="regular-text fbm-color" data-default-color="<?php echo esc_attr( $admin['accent'] ); ?>" /></td>
                                </tr>
                                <tr><th colspan="2"><h2><?php esc_html_e( 'Menu', 'foodbank-manager' ); ?></h2></th></tr>
                                <tr>
                                        <th><label for="fbm_menu_item_height"><?php esc_html_e( 'Item height', 'foodbank-manager' ); ?></label></th>
                                        <td><input data-token="--fbm-menu-item-h" data-unit="px" data-default="<?php echo esc_attr( $menu['item_height'] ); ?>" type="range" min="40" max="64" step="1" id="fbm_menu_item_height" name="fbm_theme[menu][item_height]" value="<?php echo esc_attr( $menu['item_height'] ); ?>" /></td>
                                </tr>
                                <tr>
                                        <th><label for="fbm_menu_item_px"><?php esc_html_e( 'Padding X', 'foodbank-manager' ); ?></label></th>
                                        <td><input type="range" min="8" max="24" step="1" id="fbm_menu_item_px" name="fbm_theme[menu][item_px]" value="<?php echo esc_attr( $menu['item_px'] ); ?>" /></td>
                                </tr>
                                <tr>
                                        <th><label for="fbm_menu_item_py"><?php esc_html_e( 'Padding Y', 'foodbank-manager' ); ?></label></th>
                                        <td><input type="range" min="6" max="16" step="1" id="fbm_menu_item_py" name="fbm_theme[menu][item_py]" value="<?php echo esc_attr( $menu['item_py'] ); ?>" /></td>
                                </tr>
                                <tr>
                                        <th><label for="fbm_menu_icon_size"><?php esc_html_e( 'Icon size', 'foodbank-manager' ); ?></label></th>
                                        <td><input type="range" min="16" max="24" step="1" id="fbm_menu_icon_size" name="fbm_theme[menu][icon_size]" value="<?php echo esc_attr( $menu['icon_size'] ); ?>" /></td>
                                </tr>
                                <tr>
                                        <th><label for="fbm_menu_icon_opacity"><?php esc_html_e( 'Icon opacity', 'foodbank-manager' ); ?></label></th>
                                        <td><input type="range" min="0.6" max="1" step="0.01" id="fbm_menu_icon_opacity" name="fbm_theme[menu][icon_opacity]" value="<?php echo esc_attr( $menu['icon_opacity'] ); ?>" /></td>
                                </tr>
                                <tr>
                                        <th><label for="fbm_menu_gap"><?php esc_html_e( 'Gap', 'foodbank-manager' ); ?></label></th>
                                        <td><input type="number" min="8" max="16" step="1" id="fbm_menu_gap" name="fbm_theme[menu][gap]" value="<?php echo esc_attr( $menu['gap'] ); ?>" /></td>
                                </tr>
                                <tr>
                                        <th><label for="fbm_menu_radius"><?php esc_html_e( 'Radius', 'foodbank-manager' ); ?></label></th>
                                        <td><input type="number" min="8" max="16" step="1" id="fbm_menu_radius" name="fbm_theme[menu][radius]" value="<?php echo esc_attr( $menu['radius'] ); ?>" /></td>
                                </tr>
                                <tr>
                                        <th><label for="fbm_menu_hover_bg"><?php esc_html_e( 'Hover background', 'foodbank-manager' ); ?></label></th>
                                        <td><input data-token="--fbm-menu-hover-bg" data-default="<?php echo esc_attr( $menu['hover_bg'] ); ?>" type="text" id="fbm_menu_hover_bg" name="fbm_theme[menu][hover_bg]" value="<?php echo esc_attr( $menu['hover_bg'] ); ?>" class="regular-text fbm-color" /></td>
                                </tr>
                                <tr>
                                        <th><label for="fbm_menu_hover_color"><?php esc_html_e( 'Hover colour', 'foodbank-manager' ); ?></label></th>
                                        <td><input data-token="--fbm-menu-hover-color" data-default="<?php echo esc_attr( $menu['hover_color'] ); ?>" type="text" id="fbm_menu_hover_color" name="fbm_theme[menu][hover_color]" value="<?php echo esc_attr( $menu['hover_color'] ); ?>" class="regular-text fbm-color" /></td>
                                </tr>
                                <tr>
                                        <th><label for="fbm_menu_active_bg"><?php esc_html_e( 'Active background', 'foodbank-manager' ); ?></label></th>
                                        <td><input data-token="--fbm-menu-active-bg" data-default="<?php echo esc_attr( $menu['active_bg'] ); ?>" type="text" id="fbm_menu_active_bg" name="fbm_theme[menu][active_bg]" value="<?php echo esc_attr( $menu['active_bg'] ); ?>" class="regular-text fbm-color" /></td>
                                </tr>
                                <tr>
                                        <th><label for="fbm_menu_active_color"><?php esc_html_e( 'Active colour', 'foodbank-manager' ); ?></label></th>
                                        <td><input data-token="--fbm-menu-active-color" data-default="<?php echo esc_attr( $menu['active_color'] ); ?>" type="text" id="fbm_menu_active_color" name="fbm_theme[menu][active_color]" value="<?php echo esc_attr( $menu['active_color'] ); ?>" class="regular-text fbm-color" /></td>
                                </tr>
                                <tr>
                                        <th><label for="fbm_menu_divider"><?php esc_html_e( 'Divider', 'foodbank-manager' ); ?></label></th>
                                        <td><input data-token="--fbm-menu-divider" data-default="<?php echo esc_attr( $menu['divider'] ); ?>" type="text" id="fbm_menu_divider" name="fbm_theme[menu][divider]" value="<?php echo esc_attr( $menu['divider'] ); ?>" class="regular-text fbm-color" /></td>
                                </tr>
                                <?php $typo = $theme['typography']; ?>
                                <tr><th colspan="2"><strong><?php esc_html_e( 'Typography', 'foodbank-manager' ); ?></strong></th></tr>
                                <?php foreach ( array( 'h1','h2','h3','h4','h5','h6' ) as $tag ) : ?>
                                <tr>
                                        <th><?php echo esc_html( strtoupper( $tag ) ); ?></th>
                                        <td>
                                                <input data-token="--fbm-<?php echo $tag; ?>" data-unit="px" type="number" min="10" max="64" name="fbm_theme[typography][<?php echo $tag; ?>][size]" value="<?php echo esc_attr( $typo[ $tag ]['size'] ); ?>" />
                                                <input data-token="--fbm-<?php echo $tag; ?>-lh" type="number" step="0.01" min="1" max="2.2" name="fbm_theme[typography][<?php echo $tag; ?>][lh]" value="<?php echo esc_attr( $typo[ $tag ]['lh'] ); ?>" />
                                                <input data-token="--fbm-<?php echo $tag; ?>-w" type="number" step="100" min="100" max="900" name="fbm_theme[typography][<?php echo $tag; ?>][weight]" value="<?php echo esc_attr( $typo[ $tag ]['weight'] ); ?>" />
                                                <input data-token="--fbm-<?php echo $tag; ?>-trk" type="number" step="0.01" min="-1" max="2" name="fbm_theme[typography][<?php echo $tag; ?>][track]" value="<?php echo esc_attr( $typo[ $tag ]['track'] ); ?>" />
                                        </td>
                                </tr>
                                <?php endforeach; ?>
                                <tr>
                                        <th><?php esc_html_e( 'Body', 'foodbank-manager' ); ?></th>
                                        <td>
                                                <input data-token="--fbm-body" data-unit="px" type="number" min="10" max="64" name="fbm_theme[typography][body][size]" value="<?php echo esc_attr( $typo['body']['size'] ); ?>" />
                                                <input data-token="--fbm-body-lh" type="number" step="0.01" min="1" max="2.2" name="fbm_theme[typography][body][lh]" value="<?php echo esc_attr( $typo['body']['lh'] ); ?>" />
                                                <input data-token="--fbm-body-w" type="number" step="100" min="100" max="900" name="fbm_theme[typography][body][weight]" value="<?php echo esc_attr( $typo['body']['weight'] ); ?>" />
                                                <input data-token="--fbm-body-trk" type="number" step="0.01" min="-1" max="2" name="fbm_theme[typography][body][track]" value="<?php echo esc_attr( $typo['body']['track'] ); ?>" />
                                        </td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Small', 'foodbank-manager' ); ?></th>
                                        <td>
                                                <input data-token="--fbm-small" data-unit="px" type="number" min="10" max="64" name="fbm_theme[typography][small][size]" value="<?php echo esc_attr( $typo['small']['size'] ); ?>" />
                                                <input data-token="--fbm-small-lh" type="number" step="0.01" min="1" max="2.2" name="fbm_theme[typography][small][lh]" value="<?php echo esc_attr( $typo['small']['lh'] ); ?>" />
                                        </td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Text colour', 'foodbank-manager' ); ?></th>
                                        <td><input data-token="--fbm-color-text" data-default="<?php echo esc_attr( $typo['color']['text'] ); ?>" type="text" name="fbm_theme[typography][color][text]" value="<?php echo esc_attr( $typo['color']['text'] ); ?>" class="regular-text fbm-color" /></td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Headings colour', 'foodbank-manager' ); ?></th>
                                        <td><input data-token="--fbm-color-headings" data-default="<?php echo esc_attr( $typo['color']['headings'] ); ?>" type="text" name="fbm_theme[typography][color][headings]" value="<?php echo esc_attr( $typo['color']['headings'] ); ?>" class="regular-text fbm-color" /></td>
                                </tr>
                <tr>
                                        <th><?php esc_html_e( 'Muted colour', 'foodbank-manager' ); ?></th>
                                        <td><input data-token="--fbm-color-muted" data-default="<?php echo esc_attr( $typo['color']['muted'] ); ?>" type="text" name="fbm_theme[typography][color][muted]" value="<?php echo esc_attr( $typo['color']['muted'] ); ?>" class="regular-text fbm-color" /></td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Link colour', 'foodbank-manager' ); ?></th>
                                        <td><input data-token="--fbm-link" data-default="<?php echo esc_attr( $typo['link']['normal'] ); ?>" type="text" name="fbm_theme[typography][link][normal]" value="<?php echo esc_attr( $typo['link']['normal'] ); ?>" class="regular-text fbm-color" /></td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Link hover', 'foodbank-manager' ); ?></th>
                                        <td><input data-token="--fbm-link-hover" data-default="<?php echo esc_attr( $typo['link']['hover'] ); ?>" type="text" name="fbm_theme[typography][link][hover]" value="<?php echo esc_attr( $typo['link']['hover'] ); ?>" class="regular-text fbm-color" /></td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Link active', 'foodbank-manager' ); ?></th>
                                        <td><input data-token="--fbm-link-active" data-default="<?php echo esc_attr( $typo['link']['active'] ); ?>" type="text" name="fbm_theme[typography][link][active]" value="<?php echo esc_attr( $typo['link']['active'] ); ?>" class="regular-text fbm-color" /></td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Link visited', 'foodbank-manager' ); ?></th>
                                        <td><input data-token="--fbm-link-visited" data-default="<?php echo esc_attr( $typo['link']['visited'] ); ?>" type="text" name="fbm_theme[typography][link][visited]" value="<?php echo esc_attr( $typo['link']['visited'] ); ?>" class="regular-text fbm-color" /></td>
                                </tr>
                                <?php $tabs = $theme['tabs']; ?>
                                <tr><th colspan="2"><strong><?php esc_html_e( 'Tabs', 'foodbank-manager' ); ?></strong></th></tr>
                                <tr>
                                        <th><?php esc_html_e( 'Height', 'foodbank-manager' ); ?></th>
                                        <td><input data-token="--fbm-tabs-h" data-unit="px" type="number" min="0" max="32" name="fbm_theme[tabs][height]" value="<?php echo esc_attr( $tabs['height'] ); ?>" /></td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Horizontal padding', 'foodbank-manager' ); ?></th>
                                        <td><input data-token="--fbm-tabs-px" data-unit="px" type="number" min="0" max="32" name="fbm_theme[tabs][px]" value="<?php echo esc_attr( $tabs['px'] ); ?>" /></td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Vertical padding', 'foodbank-manager' ); ?></th>
                                        <td><input data-token="--fbm-tabs-py" data-unit="px" type="number" min="0" max="32" name="fbm_theme[tabs][py]" value="<?php echo esc_attr( $tabs['py'] ); ?>" /></td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Gap', 'foodbank-manager' ); ?></th>
                                        <td><input data-token="--fbm-tabs-gap" data-unit="px" type="number" min="0" max="32" name="fbm_theme[tabs][gap]" value="<?php echo esc_attr( $tabs['gap'] ); ?>" /></td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Radius', 'foodbank-manager' ); ?></th>
                                        <td><input data-token="--fbm-tabs-radius" data-unit="px" type="number" min="0" max="32" name="fbm_theme[tabs][radius]" value="<?php echo esc_attr( $tabs['radius'] ); ?>" /></td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Text colour', 'foodbank-manager' ); ?></th>
                                        <td><input data-token="--fbm-tabs-color" data-default="<?php echo esc_attr( $tabs['color'] ); ?>" type="text" name="fbm_theme[tabs][color]" value="<?php echo esc_attr( $tabs['color'] ); ?>" class="regular-text fbm-color" /></td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Hover colour', 'foodbank-manager' ); ?></th>
                                        <td><input data-token="--fbm-tabs-hover-color" data-default="<?php echo esc_attr( $tabs['hover_color'] ); ?>" type="text" name="fbm_theme[tabs][hover_color]" value="<?php echo esc_attr( $tabs['hover_color'] ); ?>" class="regular-text fbm-color" /></td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Active colour', 'foodbank-manager' ); ?></th>
                                        <td><input data-token="--fbm-tabs-active-color" data-default="<?php echo esc_attr( $tabs['active_color'] ); ?>" type="text" name="fbm_theme[tabs][active_color]" value="<?php echo esc_attr( $tabs['active_color'] ); ?>" class="regular-text fbm-color" /></td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Hover background', 'foodbank-manager' ); ?></th>
                                        <td><input data-token="--fbm-tabs-hover-bg" data-default="<?php echo esc_attr( $tabs['hover_bg'] ); ?>" type="text" name="fbm_theme[tabs][hover_bg]" value="<?php echo esc_attr( $tabs['hover_bg'] ); ?>" class="regular-text fbm-color" /></td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Active background', 'foodbank-manager' ); ?></th>
                                        <td><input data-token="--fbm-tabs-active-bg" data-default="<?php echo esc_attr( $tabs['active_bg'] ); ?>" type="text" name="fbm_theme[tabs][active_bg]" value="<?php echo esc_attr( $tabs['active_bg'] ); ?>" class="regular-text fbm-color" /></td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Indicator height', 'foodbank-manager' ); ?></th>
                                        <td><input data-token="--fbm-tabs-indicator-h" data-unit="px" type="number" min="1" max="6" name="fbm_theme[tabs][indicator_h]" value="<?php echo esc_attr( $tabs['indicator_h'] ); ?>" /></td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Indicator offset', 'foodbank-manager' ); ?></th>
                                        <td><input data-token="--fbm-tabs-indicator-offset" data-unit="px" type="number" min="0" max="8" name="fbm_theme[tabs][indicator_offset]" value="<?php echo esc_attr( $tabs['indicator_offset'] ); ?>" /></td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Indicator colour', 'foodbank-manager' ); ?></th>
                                        <td><input data-token="--fbm-tabs-indicator-color" data-default="<?php echo esc_attr( $tabs['indicator_color'] ); ?>" type="text" name="fbm_theme[tabs][indicator_color]" value="<?php echo esc_attr( $tabs['indicator_color'] ); ?>" class="regular-text fbm-color" /></td>
                                </tr>
                        </table>
                <?php else : ?>
                        <table class="form-table" role="presentation">
                                <tr>
                                        <th><label><input type="checkbox" name="fbm_theme[front][enabled]" value="1" <?php checked( $front['enabled'] ); ?> /> <?php esc_html_e( 'Apply theme to front-end shortcodes/pages', 'foodbank-manager' ); ?></label></th>
                                        <td></td>
                                </tr>
                                <tr>
                                        <th><label><input type="checkbox" name="fbm_theme[apply_front_menus]" value="1" <?php checked( $apply_front_menus ); ?> /> <?php esc_html_e( 'Apply theme to site menus', 'foodbank-manager' ); ?></label></th>
                                        <td></td>
                                </tr>
                                <tr>
                                        <th><label><input type="checkbox" name="fbm_theme[match_front_to_admin]" value="1" <?php checked( $match ); ?> /> <?php esc_html_e( 'Match front-end to admin theme', 'foodbank-manager' ); ?></label></th>
                                        <td></td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Mode', 'foodbank-manager' ); ?></th>
                                        <td>
                                                <label><input type="radio" name="fbm_theme[front][style]" value="glass" <?php checked( $front['style'], 'glass' ); ?> <?php echo $match ? 'disabled="disabled"' : ''; ?> /> <?php esc_html_e( 'Glass', 'foodbank-manager' ); ?></label>
                                                <label><input type="radio" name="fbm_theme[front][style]" value="basic" <?php checked( $front['style'], 'basic' ); ?> <?php echo $match ? 'disabled="disabled"' : ''; ?> /> <?php esc_html_e( 'Basic', 'foodbank-manager' ); ?></label>
                                        </td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Preset', 'foodbank-manager' ); ?></th>
                                        <td>
                                                <label><input type="radio" name="fbm_theme[front][preset]" value="light" <?php checked( $front['preset'], 'light' ); ?> <?php echo $match ? 'disabled="disabled"' : ''; ?> /> <?php esc_html_e( 'Light', 'foodbank-manager' ); ?></label>
                                                <label><input type="radio" name="fbm_theme[front][preset]" value="dark" <?php checked( $front['preset'], 'dark' ); ?> <?php echo $match ? 'disabled="disabled"' : ''; ?> /> <?php esc_html_e( 'Dark', 'foodbank-manager' ); ?></label>
                                                <label><input type="radio" name="fbm_theme[front][preset]" value="high_contrast" <?php checked( $front['preset'], 'high_contrast' ); ?> <?php echo $match ? 'disabled="disabled"' : ''; ?> /> <?php esc_html_e( 'High-Contrast', 'foodbank-manager' ); ?></label>
                                        </td>
                                </tr>
                                <tr>
                                        <th><label for="fbm_front_accent"><?php esc_html_e( 'Accent colour', 'foodbank-manager' ); ?></label></th>
                                        <td><input type="text" id="fbm_front_accent" name="fbm_theme[front][accent]" value="<?php echo esc_attr( $front['accent'] ); ?>" class="regular-text fbm-color" data-default-color="<?php echo esc_attr( \FoodBankManager\UI\Theme::DEFAULT_ACCENT ); ?>" <?php echo $match ? 'disabled="disabled"' : ''; ?> /></td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Glass alpha', 'foodbank-manager' ); ?></th>
                                        <td><input type="number" min="0" max="1" step="0.01" name="fbm_theme[front][glass][alpha]" value="<?php echo esc_attr( $front['glass']['alpha'] ); ?>" <?php echo $match ? 'disabled="disabled"' : ''; ?> /></td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Glass blur', 'foodbank-manager' ); ?></th>
                                        <td><input type="number" min="0" max="20" step="1" name="fbm_theme[front][glass][blur]" value="<?php echo esc_attr( $front['glass']['blur'] ); ?>" <?php echo $match ? 'disabled="disabled"' : ''; ?> /> px</td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Elevation', 'foodbank-manager' ); ?></th>
                                        <td><input type="number" min="0" max="24" step="1" name="fbm_theme[front][glass][elev]" value="<?php echo esc_attr( $front['glass']['elev'] ); ?>" <?php echo $match ? 'disabled="disabled"' : ''; ?> /></td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Radius', 'foodbank-manager' ); ?></th>
                                        <td><input type="number" min="6" max="20" step="1" name="fbm_theme[front][glass][radius]" value="<?php echo esc_attr( $front['glass']['radius'] ); ?>" <?php echo $match ? 'disabled="disabled"' : ''; ?> /> px</td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Border', 'foodbank-manager' ); ?></th>
                                        <td><input type="number" min="1" max="2" step="1" name="fbm_theme[front][glass][border]" value="<?php echo esc_attr( $front['glass']['border'] ); ?>" <?php echo $match ? 'disabled="disabled"' : ''; ?> /> px</td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Button background', 'foodbank-manager' ); ?></th>
                                        <td><input type="text" name="fbm_theme[front][aliases][button_bg]" value="<?php echo esc_attr( $front['aliases']['button_bg'] ?? '' ); ?>" class="regular-text fbm-color" data-default-color="<?php echo esc_attr( $front['accent'] ); ?>" <?php echo $match ? 'disabled="disabled"' : ''; ?> /></td>
                                </tr>
                                <tr>
                                        <th><?php esc_html_e( 'Link colour', 'foodbank-manager' ); ?></th>
                                        <td><input type="text" name="fbm_theme[front][aliases][link_fg]" value="<?php echo esc_attr( $front['aliases']['link_fg'] ?? '' ); ?>" class="regular-text fbm-color" data-default-color="<?php echo esc_attr( $front['accent'] ); ?>" <?php echo $match ? 'disabled="disabled"' : ''; ?> /></td>
                                </tr>
                        </table>
                <?php endif; ?>
                <?php submit_button(); ?>
        </form>
    </div>
    <div class="fbm-theme-preview-wrap">
        <div class="fbm-scope fbm-app fbm-preview" data-fbm-preview>
            <?php include FBM_PATH . 'templates/admin/_preview.php'; ?>
        </div>
    </div>
</div>
</div>
<?php
// End of template.
?>
<?php echo '</div>'; ?>
