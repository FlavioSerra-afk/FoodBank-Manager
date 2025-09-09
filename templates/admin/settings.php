<?php
// phpcs:ignoreFile
/**
 * Settings page template.
 *
 * @package FoodBankManager
 */

use FoodBankManager\Core\Options;

if ( ! defined( 'ABSPATH' ) ) {
                exit;
}

$current_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'branding'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only
$settings    = Options::all();
?>
<div class="wrap fbm-admin">
<?php \FBM\Core\Trace::mark( 'admin:settings' ); ?>
                <h1><?php esc_html_e( 'Settings', 'foodbank-manager' ); ?></h1>
        <?php if ( isset( $_GET['notice'] ) && 'saved' === $_GET['notice'] ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- admin screen ?>
                <div class="notice notice-success"><p><?php esc_html_e( 'Settings saved.', 'foodbank-manager' ); ?></p></div> <?php // phpcs:ignore Generic.Files.LineLength ?>
		<?php endif; ?>
                <h2 class="nav-tab-wrapper">
                <a href="<?php echo esc_url( add_query_arg( 'tab', 'branding' ) ); ?>" class="nav-tab <?php echo 'branding' === $current_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Branding', 'foodbank-manager' ); ?></a> <?php // phpcs:ignore Generic.Files.LineLength ?>
                <a href="<?php echo esc_url( add_query_arg( 'tab', 'email' ) ); ?>" class="nav-tab <?php echo 'email' === $current_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Email', 'foodbank-manager' ); ?></a> <?php // phpcs:ignore Generic.Files.LineLength ?>
                <a href="<?php echo esc_url( add_query_arg( 'tab', 'appearance' ) ); ?>" class="nav-tab <?php echo 'appearance' === $current_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Appearance', 'foodbank-manager' ); ?></a> <?php // phpcs:ignore Generic.Files.LineLength ?>
                <a href="<?php echo esc_url( add_query_arg( 'tab', 'privacy' ) ); ?>" class="nav-tab <?php echo 'privacy' === $current_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Privacy', 'foodbank-manager' ); ?></a> <?php // phpcs:ignore Generic.Files.LineLength ?>
                </h2>
                <?php if ( 'email' === $current_tab ) : ?>
		<form method="post" action="">
				<?php wp_nonce_field( 'fbm_email_save', '_fbm_nonce' ); ?>
				<input type="hidden" name="fbm_action" value="email_save" />
				<table class="form-table">
						<tr>
								<th><label for="from_name"><?php esc_html_e( 'From name', 'foodbank-manager' ); ?></label></th>
                                <td><input type="text" name="emails[from_name]" id="from_name" value="<?php echo esc_attr( $settings['emails']['from_name'] ); ?>" class="regular-text" /></td><?php // phpcs:ignore Generic.Files.LineLength ?>
						</tr>
						<tr>
								<th><label for="from_email"><?php esc_html_e( 'From email', 'foodbank-manager' ); ?></label></th>
                                <td><input type="email" name="emails[from_email]" id="from_email" value="<?php echo esc_attr( $settings['emails']['from_email'] ); ?>" class="regular-text" /></td><?php // phpcs:ignore Generic.Files.LineLength ?>
						</tr>
						<tr>
								<th><label for="reply_to"><?php esc_html_e( 'Reply-to', 'foodbank-manager' ); ?></label></th>
                                <td><input type="email" name="emails[reply_to]" id="reply_to" value="<?php echo esc_attr( $settings['emails']['reply_to'] ); ?>" class="regular-text" /></td><?php // phpcs:ignore Generic.Files.LineLength ?>
						</tr>
				</table>
				<?php submit_button(); ?>
		</form>
                <?php elseif ( 'appearance' === $current_tab ) : ?>
                <form method="post" action="">
                                <?php wp_nonce_field( 'fbm_theme_save', '_fbm_nonce' ); ?>
                                <input type="hidden" name="fbm_action" value="theme_save" />
                                <table class="form-table">
                                                <tr>
                                                                <th><label for="theme_preset"><?php esc_html_e( 'Theme preset', 'foodbank-manager' ); ?></label></th>
                                                                <td>
                                                                        <select name="theme[preset]" id="theme_preset">
                                                                                <?php
                                                                                $presets = array( 'system', 'light', 'dark', 'high_contrast' );
                                                                                $cur     = $settings['theme']['preset'] ?? 'system';
                                                                                foreach ( $presets as $p ) {
                                                                                        printf( '<option value="%1$s" %2$s>%1$s</option>', esc_attr( $p ), selected( $cur, $p, false ) );
                                                                                }
                                                                                ?>
                                                                        </select>
                                                                </td>
                                                </tr>
                                                <tr>
                                                                <th><label for="theme_rtl"><?php esc_html_e( 'RTL mode', 'foodbank-manager' ); ?></label></th>
                                                                <td>
                                                                        <select name="theme[rtl]" id="theme_rtl">
                                                                                <?php
                                                                                $rtl_cur = $settings['theme']['rtl'] ?? 'auto';
                                                                                foreach ( array( 'auto', 'force_on', 'force_off' ) as $r ) {
                                                                                        printf( '<option value="%1$s" %2$s>%1$s</option>', esc_attr( $r ), selected( $rtl_cur, $r, false ) );
                                                                                }
                                                                                ?>
                                                                        </select>
                                                                </td>
                                                </tr>
                                </table>
                                <?php submit_button(); ?>
                </form>
                <?php elseif ( 'privacy' === $current_tab ) : ?>
                <form method="post" action="">
                                <?php wp_nonce_field( 'fbm_retention_save', '_fbm_nonce' ); ?>
                                <input type="hidden" name="fbm_action" value="retention_save" />
                                <table class="form-table">
                                                <?php
                                                $cats = array(
                                                        'applications' => __( 'Applications', 'foodbank-manager' ),
                                                        'attendance'   => __( 'Attendance', 'foodbank-manager' ),
                                                        'mail_log'     => __( 'Email Log', 'foodbank-manager' ),
                                                );
                                                $ret = $settings['privacy']['retention'] ?? array();
                                                foreach ( $cats as $key => $label ) :
                                                        $val = $ret[ $key ] ?? array( 'days' => 0, 'policy' => 'delete' );
                                                ?>
                                                <tr>
                                                                <th><label for="ret_<?php echo esc_attr( $key ); ?>_days"><?php echo esc_html( $label ); ?></label></th>
                                                                <td>
                                                                        <input type="number" min="0" id="ret_<?php echo esc_attr( $key ); ?>_days" name="retention[<?php echo esc_attr( $key ); ?>][days]" value="<?php echo esc_attr( (string) ( $val['days'] ?? 0 ) ); ?>" />
                                                                        <select name="retention[<?php echo esc_attr( $key ); ?>][policy]">
                                                                                <option value="delete" <?php selected( $val['policy'] ?? '', 'delete' ); ?>><?php esc_html_e( 'Delete', 'foodbank-manager' ); ?></option>
                                                                                <option value="anonymise" <?php selected( $val['policy'] ?? '', 'anonymise' ); ?>><?php esc_html_e( 'Anonymise', 'foodbank-manager' ); ?></option>
                                                                        </select>
                                                                </td>
                                                </tr>
                                                <?php endforeach; ?>
                                </table>
                                <?php submit_button(); ?>
                </form>
                <form method="post" action="" style="margin-top:1em;">
                                <?php wp_nonce_field( 'fbm_retention_dryrun', '_fbm_nonce' ); ?>
                                <input type="hidden" name="fbm_action" value="retention_dryrun" />
                                <p><button type="submit" class="button"><?php esc_html_e( 'Dry-run now', 'foodbank-manager' ); ?></button></p>
                </form>
                <form method="post" action="">
                                <?php wp_nonce_field( 'fbm_retention_run', '_fbm_nonce' ); ?>
                                <input type="hidden" name="fbm_action" value="retention_run" />
                                <p><button type="submit" class="button button-primary"><?php esc_html_e( 'Run now', 'foodbank-manager' ); ?></button></p>
                </form>
                <?php else : ?>
                <form method="post" action="">
                                <?php wp_nonce_field( 'fbm_branding_save', '_fbm_nonce' ); ?>
                                <input type="hidden" name="fbm_action" value="branding_save" />
				<table class="form-table">
						<tr>
								<th><label for="site_name"><?php esc_html_e( 'Site name', 'foodbank-manager' ); ?></label></th>
                                <td><input type="text" name="branding[site_name]" id="site_name" value="<?php echo esc_attr( $settings['branding']['site_name'] ); ?>" class="regular-text" /></td><?php // phpcs:ignore Generic.Files.LineLength ?>
						</tr>
						<tr>
								<th><label for="logo_url"><?php esc_html_e( 'Logo URL', 'foodbank-manager' ); ?></label></th>
                                <td><input type="url" name="branding[logo_url]" id="logo_url" value="<?php echo esc_attr( $settings['branding']['logo_url'] ); ?>" class="regular-text" /></td><?php // phpcs:ignore Generic.Files.LineLength ?>
						</tr>
						<tr>
								<th><label for="color"><?php esc_html_e( 'Color', 'foodbank-manager' ); ?></label></th>
								<td>
                                        <select name="branding[color]" id="color">
                                                <?php
                                                $colors = array( 'default', 'blue', 'green', 'red', 'orange', 'purple' );
                                                foreach ( $colors as $c ) {
                                                        printf( '<option value="%1$s" %2$s>%1$s</option>', esc_attr( $c ), selected( $settings['branding']['color'], $c, false ) );
                                                }
                                                ?>
                                        </select> <?php // phpcs:ignore Generic.Files.LineLength ?>
								</td>
						</tr>
				</table>
				<?php submit_button(); ?>
		</form>
                <?php endif; ?>
</div>
