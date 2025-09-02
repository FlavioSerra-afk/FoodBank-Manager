<?php
/**
 * Settings page template.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use FoodBankManager\Core\Options;
$settings = Options::all();
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Settings', 'foodbank-manager' ); ?></h1>
	<?php settings_errors( 'fbm-settings' ); ?>
	<h2 class="nav-tab-wrapper">
		<a href="#general" class="nav-tab nav-tab-active"><?php esc_html_e( 'General', 'foodbank-manager' ); ?></a>
		<a href="#forms" class="nav-tab"><?php esc_html_e( 'Forms & Anti-spam', 'foodbank-manager' ); ?></a>
		<a href="#files" class="nav-tab"><?php esc_html_e( 'Files', 'foodbank-manager' ); ?></a>
		<a href="#emails" class="nav-tab"><?php esc_html_e( 'Emails', 'foodbank-manager' ); ?></a>
		<a href="#attendance" class="nav-tab"><?php esc_html_e( 'Attendance Policy', 'foodbank-manager' ); ?></a>
		<a href="#privacy" class="nav-tab"><?php esc_html_e( 'Privacy & Retention', 'foodbank-manager' ); ?></a>
		<a href="#encryption" class="nav-tab"><?php esc_html_e( 'Encryption', 'foodbank-manager' ); ?></a>
	</h2>
	<form method="post" action="">
		<?php wp_nonce_field( 'fbm_settings_save', 'fbm_settings_nonce' ); ?>
		<div id="general" class="tab-section" style="display:block;">
			<table class="form-table">
				<tr>
					<th><label for="org_name"><?php esc_html_e( 'Organisation Name', 'foodbank-manager' ); ?></label></th>
					<td><input name="fbm_settings[general][org_name]" id="org_name" type="text" value="<?php echo esc_attr( $settings['general']['org_name'] ); ?>" class="regular-text" /></td>
				</tr>
				<tr>
					<th><label for="date_format"><?php esc_html_e( 'Date Format', 'foodbank-manager' ); ?></label></th>
					<td><input name="fbm_settings[general][date_format]" id="date_format" type="text" value="<?php echo esc_attr( $settings['general']['date_format'] ); ?>" class="regular-text" /></td>
				</tr>
			</table>
		</div>
		<div id="forms" class="tab-section" style="display:none;">
			<table class="form-table">
				<tr>
					<th><?php esc_html_e( 'CAPTCHA provider', 'foodbank-manager' ); ?></th>
					<td>
						<select name="fbm_settings[forms][captcha_provider]">
							<?php foreach ( array( 'off', 'recaptcha', 'turnstile' ) as $p ) : ?>
								<option value="<?php echo esc_attr( $p ); ?>" <?php selected( $settings['forms']['captcha_provider'], $p ); ?>><?php echo esc_html( $p ); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th><label for="captcha_site_key"><?php esc_html_e( 'CAPTCHA Site Key', 'foodbank-manager' ); ?></label></th>
					<td><input type="text" name="fbm_settings[forms][captcha_site_key]" id="captcha_site_key" value="<?php echo esc_attr( $settings['forms']['captcha_site_key'] ); ?>" class="regular-text" /></td>
				</tr>
				<tr>
					<th><label for="captcha_secret"><?php esc_html_e( 'CAPTCHA Secret', 'foodbank-manager' ); ?></label></th>
					<td><input type="text" name="fbm_settings[forms][captcha_secret]" id="captcha_secret" value="<?php echo esc_attr( $settings['forms']['captcha_secret'] ); ?>" class="regular-text" /></td>
				</tr>
				<tr>
					<th><label for="honeypot"><?php esc_html_e( 'Honeypot', 'foodbank-manager' ); ?></label></th>
					<td><input type="checkbox" name="fbm_settings[forms][honeypot]" id="honeypot" value="1" <?php checked( $settings['forms']['honeypot'] ); ?> /></td>
				</tr>
				<tr>
					<th><label for="rate_limit_per_ip"><?php esc_html_e( 'Rate limit per IP (seconds)', 'foodbank-manager' ); ?></label></th>
					<td><input type="number" name="fbm_settings[forms][rate_limit_per_ip]" id="rate_limit_per_ip" value="<?php echo esc_attr( $settings['forms']['rate_limit_per_ip'] ); ?>" class="small-text" /></td>
				</tr>
				<tr>
					<th><label for="consent_text"><?php esc_html_e( 'Consent text', 'foodbank-manager' ); ?></label></th>
					<td><textarea name="fbm_settings[forms][consent_text]" id="consent_text" rows="4" class="large-text"><?php echo esc_textarea( $settings['forms']['consent_text'] ); ?></textarea></td>
				</tr>
				<tr>
					<th><label for="success_redirect_page_id"><?php esc_html_e( 'Success redirect page', 'foodbank-manager' ); ?></label></th>
					<td>
					<?php
					wp_dropdown_pages(
						array(
							'name'             => 'fbm_settings[forms][success_redirect_page_id]',
							'selected'         => $settings['forms']['success_redirect_page_id'],
							'show_option_none' => __( '— Select —', 'foodbank-manager' ),
						)
					);
					?>
					</td>
				</tr>
			</table>
		</div>
		<div id="files" class="tab-section" style="display:none;">
			<table class="form-table">
				<tr>
					<th><label for="max_size_mb"><?php esc_html_e( 'Max file size (MB)', 'foodbank-manager' ); ?></label></th>
					<td><input type="number" name="fbm_settings[files][max_size_mb]" id="max_size_mb" value="<?php echo esc_attr( $settings['files']['max_size_mb'] ); ?>" class="small-text" /></td>
				</tr>
				<tr>
					<th><label for="allowed_mimes"><?php esc_html_e( 'Allowed mime types', 'foodbank-manager' ); ?></label></th>
					<td><input type="text" name="fbm_settings[files][allowed_mimes]" id="allowed_mimes" value="<?php echo esc_attr( implode( ',', $settings['files']['allowed_mimes'] ) ); ?>" class="regular-text" /></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Storage', 'foodbank-manager' ); ?></th>
					<td>
						<select name="fbm_settings[files][storage]">
							<option value="uploads" <?php selected( $settings['files']['storage'], 'uploads' ); ?>>uploads</option>
							<option value="local" <?php selected( $settings['files']['storage'], 'local' ); ?>>local</option>
						</select>
						<p><input type="text" name="fbm_settings[files][local_path]" value="<?php echo esc_attr( $settings['files']['local_path'] ); ?>" class="regular-text" /></p>
					</td>
				</tr>
			</table>
		</div>
		<div id="emails" class="tab-section" style="display:none;">
			<table class="form-table">
				<tr><th><label for="from_name"><?php esc_html_e( 'From name', 'foodbank-manager' ); ?></label></th><td><input type="text" id="from_name" name="fbm_settings[emails][from_name]" value="<?php echo esc_attr( $settings['emails']['from_name'] ); ?>" class="regular-text" /></td></tr>
				<tr><th><label for="from_email"><?php esc_html_e( 'From email', 'foodbank-manager' ); ?></label></th><td><input type="email" id="from_email" name="fbm_settings[emails][from_email]" value="<?php echo esc_attr( $settings['emails']['from_email'] ); ?>" class="regular-text" /></td></tr>
				<tr><th><label for="reply_to"><?php esc_html_e( 'Reply-to', 'foodbank-manager' ); ?></label></th><td><input type="email" id="reply_to" name="fbm_settings[emails][reply_to]" value="<?php echo esc_attr( $settings['emails']['reply_to'] ); ?>" class="regular-text" /></td></tr>
				<tr><th><label for="admin_recipients"><?php esc_html_e( 'Admin recipients', 'foodbank-manager' ); ?></label></th><td><input type="text" id="admin_recipients" name="fbm_settings[emails][admin_recipients]" value="<?php echo esc_attr( $settings['emails']['admin_recipients'] ); ?>" class="regular-text" /></td></tr>
			</table>
		</div>
		<div id="attendance" class="tab-section" style="display:none;">
			<table class="form-table">
				<tr><th><label for="policy_days"><?php esc_html_e( 'Policy days', 'foodbank-manager' ); ?></label></th><td><input type="number" id="policy_days" name="fbm_settings[attendance][policy_days]" value="<?php echo esc_attr( $settings['attendance']['policy_days'] ); ?>" class="small-text" /></td></tr>
				<tr><th><label for="types"><?php esc_html_e( 'Attendance types (comma separated)', 'foodbank-manager' ); ?></label></th><td><input type="text" id="types" name="fbm_settings[attendance][types]" value="<?php echo esc_attr( implode( ',', $settings['attendance']['types'] ) ); ?>" class="regular-text" /></td></tr>
			</table>
		</div>
		<div id="privacy" class="tab-section" style="display:none;">
			<table class="form-table">
				<tr><th><label for="retention_months"><?php esc_html_e( 'Retention months', 'foodbank-manager' ); ?></label></th><td><input type="number" id="retention_months" name="fbm_settings[privacy][retention_months]" value="<?php echo esc_attr( $settings['privacy']['retention_months'] ); ?>" class="small-text" /></td></tr>
				<tr><th><label for="anonymise_files"><?php esc_html_e( 'Files policy', 'foodbank-manager' ); ?></label></th><td>
					<select name="fbm_settings[privacy][anonymise_files]" id="anonymise_files">
						<option value="delete" <?php selected( $settings['privacy']['anonymise_files'], 'delete' ); ?>>delete</option>
						<option value="keep" <?php selected( $settings['privacy']['anonymise_files'], 'keep' ); ?>>keep</option>
						<option value="move" <?php selected( $settings['privacy']['anonymise_files'], 'move' ); ?>>move</option>
					</select></td></tr>
			</table>
		</div>
		<div id="encryption" class="tab-section" style="display:none;">
			<p><?php esc_html_e( 'Encryption status is read-only.', 'foodbank-manager' ); ?></p>
			<table class="form-table">
				<tr><th><?php esc_html_e( 'KEK configured', 'foodbank-manager' ); ?></th><td><?php echo defined( 'FBM_KEK_BASE64' ) && constant( 'FBM_KEK_BASE64' ) ? '✅' : '❌'; ?></td></tr>
				<tr><th><?php esc_html_e( 'Sodium', 'foodbank-manager' ); ?></th><td><?php echo extension_loaded( 'sodium' ) ? 'native' : ( class_exists( 'ParagonIE\\Sodium\\Compat' ) ? 'polyfill' : 'none' ); ?></td></tr>
			</table>
		</div>
		<?php submit_button(); ?>
	</form>
</div>
<script>
(function(){
	const tabs=document.querySelectorAll('.nav-tab');
	tabs.forEach(tab=>tab.addEventListener('click',function(e){e.preventDefault();tabs.forEach(t=>t.classList.remove('nav-tab-active'));tab.classList.add('nav-tab-active');document.querySelectorAll('.tab-section').forEach(sec=>sec.style.display='none');document.querySelector(tab.getAttribute('href')).style.display='block';}));
})();
</script>
