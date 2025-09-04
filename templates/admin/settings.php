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
		<h1><?php esc_html_e( 'Settings', 'foodbank-manager' ); ?></h1>
        <?php if ( isset( $_GET['notice'] ) && 'saved' === $_GET['notice'] ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- admin screen ?>
                <div class="notice notice-success"><p><?php esc_html_e( 'Settings saved.', 'foodbank-manager' ); ?></p></div> <?php // phpcs:ignore Generic.Files.LineLength ?>
		<?php endif; ?>
		<h2 class="nav-tab-wrapper">
                <a href="<?php echo esc_url( add_query_arg( 'tab', 'branding' ) ); ?>" class="nav-tab <?php echo 'branding' === $current_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Branding', 'foodbank-manager' ); ?></a> <?php // phpcs:ignore Generic.Files.LineLength ?>
                <a href="<?php echo esc_url( add_query_arg( 'tab', 'email' ) ); ?>" class="nav-tab <?php echo 'email' === $current_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Email', 'foodbank-manager' ); ?></a> <?php // phpcs:ignore Generic.Files.LineLength ?>
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
